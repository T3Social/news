<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\reminder;

use DateTime;
use Exception;
use humhub\modules\news\helpers\NewsUtils;
use humhub\modules\news\models\News;
use humhub\modules\news\Module;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\news\notifications\Remind;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\IntegrityException;

class ReminderProcessor extends Model
{

    public $handledReminders = [];

    /**
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function run()
    {
        /**
         * We differ the following cases for optimization reasons.
         *
         * If global and container default reminders are given:
         *
         *  - Loop through all not confirmed news
         *  - Handle news level reminder
         *  - Handle remaining default reminder
         *
         * If no global default reminders are given:
         *
         *  - Loop through containers with given default settings
         *  - Handle news level reminder
         *  - Handle container default reminder
         *
         *  => Skips news without reminders
         *
         * If no global default and no container default reminders are given, we simply loop through the news level reminder.
         *
         *  - Loop through and handle all news level reminder
         */
        if (empty(NewsReminder::getDefaults())) {
            foreach (NewsReminder::getContainerWithDefaultReminder() as $contentContainer) {
                $this->runByNotConfirmedNews($contentContainer->getPolymorphicRelation());
            }

            $this->runNewsLevelOnly();
        } else {
            $this->runByNotConfirmedNews();
        }
    }

    /**
     * @param ContentContainerActiveRecord|null $container
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    private function runByNotConfirmedNews(ContentContainerActiveRecord $container = null)
    {
        $notConfirmedNews = News::find();
        $notConfirmedNews = $container
            ? $notConfirmedNews->contentContainer($container)
            : $notConfirmedNews->joinWith(['content', 'content.contentContainer', 'content.createdBy']);

        $notConfirmedNews = $notConfirmedNews->andWhere(['confirm_reading' => '1'])
            ->andWhere(['>=', 'content.created_at', date('Y-m-d G:i:s', time() - 31 * 86400)])
            ->andWhere(['<=', 'content.created_at', date('Y-m-d G:i:s', time() - NewsReminder::getMinReminderHoursInPast() * 3600)])
            ->limit(Module::REMINDER_PROCESS_NEWS_LIMIT)
            ->all();

        foreach ($notConfirmedNews as $news) {
            $news = NewsUtils::getNews($news);

            if (!$news || !($news instanceof News)) {
                continue;
            }

            $skipUsers = $this->handleNewsLevelReminder($news);

            if ($skipUsers === true) {
                continue; // Handled all recipients already
            }

            $this->handleDefaultReminder($news, $skipUsers);
        }
    }

    /**
     * @throws IntegrityException
     * @throws Exception
     * @throws \Throwable
     */
    private function runNewsLevelOnly()
    {
        $newsLevelReminder = NewsReminder::findNewsLevelReminder()->andWhere(['NOT IN', 'news_reminder.id', $this->handledReminders])->all();

        $newsHandled = [];
        foreach ($newsLevelReminder as $reminder) {
            $news = $reminder->getNews();

            if (!$news instanceof News) {
                $reminder->delete();
                continue;
            }

            $newsKey = get_class($news).':'.$news->id;
            if (!isset($newsHandled[$newsKey])) {
                $this->handleNewsLevelReminder($news);
                $newsHandled[$newsKey] = true;
            }
        }
    }

    /**
     * This function handles all news level reminders for a given News.
     *
     * This function will return
     *
     *  - true in case there was a container wide default reminder for this news
     *  - an array of contentcontainer ids of users already handled in case there was no container wide default for this news
     *
     * @param News $news
     * @return array|bool
     * @throws Exception
     */
    private function handleNewsLevelReminder(News $news)
    {
        // We keep track of users which have an news level reminder set for this news, in order to ignore defaults
        $skipUsers = [];

        // We keep track of reminder already sent for a container in order to ignore global defaults
        $sentContainer = [];

        // User level reminder are sorted before container level reminder (see query order_by)
        foreach (NewsReminder::getNewsLevelReminder($news) as $reminder) {
            // User has own reminder settings for this specific event, so ignore this user when handling defaults
            if ($reminder->isUserLevelReminder()) {
                $skipUsers[] = $reminder->contentcontainer_id;
            }

            // Space level reminder were disabled for this specific event, so ignore global defaults
            if ($reminder->isDisabled() && !$reminder->isUserLevelReminder()) {
                $sentContainer[$reminder->contentcontainer_id] = true;
            }

            // Skip reminder which do not match yet
            if (!$reminder->checkMaturity($news)) {
                continue;
            }

            // Check if reminder has already been sent
            if (!$reminder->isActive($news)) {
                $sentContainer[$reminder->contentcontainer_id] = true;
                continue;
            }

            // Make sure no other reminder which is closer to the event has already been sent or is disabled (see query order_by)
            if (isset($sentContainer[$reminder->contentcontainer_id])) {
                $reminder->acknowledge($news);
                continue;
            }

            if ($this->sendNewsLevelReminder($reminder, $news, $skipUsers)) {
                $sentContainer[$reminder->contentcontainer_id] = true;
            }
        }

        // news reminder without contentcotnainer_id are space level news reminder
        return isset($sentContainer[null]) ? true : $skipUsers;
    }

    /**
     * @param NewsReminder $reminder
     * @param News $news
     * @param array $skipUsers
     * @return bool
     * @throws InvalidConfigException
     * @throws IntegrityException
     */
    public function sendNewsLevelReminder(NewsReminder $reminder, News $news = null, $skipUsers = [])
    {
        if (!$news) {
            $news = $reminder->getNews();
        }

        if (!$news) {
            return false;
        }

        if ($reminder->isUserLevelReminder()) {
            $this->sendReminder($reminder, $news, User::find()->where(['user.contentcontainer_id' => $reminder->contentcontainer_id]));
        } else {
            $this->sendReminder($reminder, $news, $this->getRecipientQuery($news, $skipUsers));
        }

        return true;
    }

    /**
     * @param News $news
     * @param array $skipUsers
     * @return array|ActiveQueryUser|ActiveQuery
     */
    protected function getRecipientQuery(News $news, $skipUsers = [])
    {
        $query = $news->getReminderUserQuery();

        if (!empty($skipUsers)) {
            $query->andWhere(['NOT IN', 'user.contentcontainer_id', $skipUsers]);
        }

        return $query;
    }

    /**
     * @param News $news
     * @param $skipUsers
     * @throws InvalidConfigException
     */
    private function handleDefaultReminder(News $news, $skipUsers = [])
    {
        if ($news instanceof News && $news->isNewRecord) { // Make sure our model is saved
            $news->save();
        }

        $sent = false;
        foreach (NewsReminder::getDefaults($news->content->container, true) as $reminder) {
            if (!$reminder->checkMaturity($news)) {
                continue;
            }

            if (!$reminder->isActive($news)) {
                $sent = true;
                continue;
            }

            if (!$sent) {
                $sent = $this->sendReminder($reminder, $news, $this->getRecipientQuery($news, $skipUsers));
            } else {
                // Another reminder closer to the event start was already sent
                $reminder->acknowledge($news);
            }
        }
    }


    /**
     * @param NewsReminder $reminder
     * @param News $news
     * @param ActiveQueryUser|User[] $recipients
     * @return bool
     * @throws InvalidConfigException
     */
    private function sendReminder(NewsReminder $reminder, News $news, $recipients)
    {
        if ($news->getStartDateTime() <= new DateTime()) {
            Remind::instance()->from($news->content->createdBy)->about($news)->sendBulk($recipients);
        } else {
            Yii::warning('News reminder detected with id: '.$reminder->id.' reminder not sent and disabled.');
        }

        $reminder->acknowledge($news);
        return true;
    }
}
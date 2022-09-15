<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models;

use DateTime;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\news\helpers\NewsUtils;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\forms\ContainerSettings;
use humhub\modules\news\models\forms\ModuleSettings;
use humhub\modules\news\Module;
use humhub\modules\news\notifications\NewsCreatedNotification;
use humhub\modules\news\permissions\ManageNews;
use humhub\modules\news\widgets\WallEntry;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table 'news'.
 *
 * The followings are the available columns in table 'news':
 * @property integer $id
 * @property string $title
 * @property string $article
 * @property boolean $confirm_reading
 * @property boolean $send_notification
 */
class News extends ContentActiveRecord implements Searchable
{
    /**
     * @inheritdoc
     */
    public $wallEntryClass = WallEntry::class;

    /**
     * @inheritdoc
     */
    public $managePermission = ManageNews::class;

    /**
     * Cached data from DB
     * @var array
     */
    private $cachedData;

    /**
     * @inheritdoc
     */
    public $silentContentCreation = true;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'article'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['article'], 'safe'],
            [['confirm_reading', 'send_notification'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'title' => Yii::t('NewsModule.base', 'Title'),
            'article' => Yii::t('NewsModule.base', 'Content'),
            'confirm_reading' => Yii::t('NewsModule.base', 'Require read confirmation'),
            'send_notification' => Yii::t('NewsModule.base', 'Trigger Notification'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'send_notification' => Yii::t('NewsModule.base', 'If checked, all members of this space will immediately receive this news via notification and email.'),
        ];
    }



    /**
     * Send notification
     */
    public function sendNotification()
    {
        if (!$this->send_notification) {
            return;
        }

        Yii::createObject(['class' => NewsCreatedNotification::class])
            ->from(Yii::$app->user->getIdentity())
            ->about($this)
            ->sendBulk($this->getReminderUserQuery());
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('NewsModule.base', 'News');
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return Module::ICON;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Deny deactivate the sending of notification if it was once activated
        if (!$this->send_notification && $this->getOldAttribute('send_notification')) {
            $this->send_notification = 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        RichText::postProcess($this->article, $this, 'article');

        if (!empty($this->send_notification) && ($insert || isset($changedAttributes['send_notifications']))) {
            $this->sendNotification();
        }
    }

    /**
     * Get settings of this news' Container
     *
     * @return ContainerSettings
     */
    public function getContainerSettings()
    {
        return new ContainerSettings(['contentContainer' => $this->content->container]);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return Url::toEditNews($this);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return Url::toDeleteNews($this);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthorUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by'])
            ->viaTable('content AS author_content', ['object_id' => 'id'], function ($query) {
                /* @var $query ActiveQuery */
                $query->andWhere(['author_content.object_model' => News::class]);
            });
    }

    /**
     * Get all users who can read this News
     *
     * @return int
     */
    public function getReadingMembersCount()
    {
        if (!($this->content->container instanceof Space)) {
            return 0;
        }

        if (!isset($this->cachedData['readingMembersCount'])) {
            /* @var Space $space */
            $space = $this->content->container;

            $this->cachedData['readingMembersCount'] = (int)$space->getMemberships()
                ->innerJoin('user', 'space_membership.user_id = user.id')
                ->where(['>=', 'user.last_login', date('Y-m-d G:i:s', time() - (new ModuleSettings())->activeUserDays() * 86400)])
                ->count();
        }

        return $this->cachedData['readingMembersCount'];
    }

    /**
     * Get users who confirmed a reading of this News
     *
     * @return int
     */
    public function getConfirmedMembersCount()
    {
        if (!isset($this->cachedData['confirmedMembersCount'])) {
            $this->cachedData['confirmedMembersCount'] = (int)NewsRead::find()
                ->where(['news_id' => $this->id])
                ->innerJoin('user', 'news_read.user_id = user.id')
                ->andWhere(['>=', 'user.last_login', date('Y-m-d G:i:s', time() - (new ModuleSettings())->activeUserDays() * 86400)])
                ->count();
        }

        return $this->cachedData['confirmedMembersCount'];
    }

    /**
     * Get percent of members who confirmed a reading of this news
     *
     * @return int
     */
    public function getConfirmedReadingPercent()
    {
        $readingMembersCount = $this->getReadingMembersCount();
        if (!$readingMembersCount) {
            return 0;
        }

        $percent = ceil($this->getConfirmedMembersCount() / $readingMembersCount * 100);

        return $percent > 100 ? 100 : $percent;
    }

    /**
     * Render a progress bar of confirmed reading for this news
     *
     * @return string
     */
    public function renderConfirmProgress()
    {
        if (!$this->canBeConfirmed() ||
            !$this->content->container->can(ManageNews::class)) {
            return '';
        }

        // Display progress bar only if current User can manage News in the current Space:
        return Yii::$app->getView()->render('@news/widgets/views/confirmReadingProgress', ['news' => $this]);
    }

    /**
     * @return ActiveQueryUser
     */
    public function getReminderUserQuery()
    {
        if ($this->content->container instanceof Space) {
            $userConfirmedQuery = NewsRead::find()
                ->where('news_read.user_id = user.id')
                ->andWhere(['news_read.news_id' => $this->id]);

            return Membership::getSpaceMembersQuery($this->content->container)
                ->andWhere(['NOT EXISTS', $userConfirmedQuery]);
        }

        // Fallback should only happen for global events, which are not supported
        return User::find()->where(['id' => $this->content->createdBy->id]);
    }

    /**
     * @return DateTime|\DateTimeInterface
     * @throws \Exception
     */
    public function getStartDateTime()
    {
        return new DateTime($this->content->created_at, NewsUtils::getSystemTimeZone());
    }

    /**
     * @return bool
     */
    public function canBeConfirmed()
    {
        return !Yii::$app->user->isGuest && !$this->isNewRecord && $this->confirm_reading;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        if (!$this->canBeConfirmed()) {
            return false;
        }

        return (bool) NewsRead::findOne([
            'user_id' => Yii::$app->user->id,
            'news_id' => $this->id,
        ]);
    }

    /**
     * @return bool
     */
    public function confirmReading()
    {
        if (!$this->canBeConfirmed()) {
            return false;
        }

        $newsRead = NewsRead::findOne([
            'user_id' => Yii::$app->user->id,
            'news_id' => $this->id,
        ]);

        if ($newsRead) {
            return (bool) $newsRead->delete();
        }

        $newsRead = new NewsRead();
        $newsRead->user_id = Yii::$app->user->id;
        $newsRead->news_id = $this->id;
        return $newsRead->save();
    }

    /**
     * Get info of how many users already read this News
     *
     * @param string
     * @return string
     */
    public function getReadingInfo($text = '{confirmedNum} of {totalReadersNum} users reached!'): string
    {
        return Yii::t('NewsModule.base', $text, [
            'confirmedNum' => $this->getConfirmedMembersCount(),
            'totalReadersNum' => $this->getReadingMembersCount(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSearchAttributes()
    {
        return [
            'title' => $this->title,
            'article' => $this->article,
        ];
    }
}
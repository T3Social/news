<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\reminder;

use humhub\components\ActiveRecord;
use humhub\modules\news\models\News;

/**
 * Class NewsReminderSent
 *
 * @property int $news_id
 * @property int $reminder_id
 * @property string $created_at
 */
class NewsReminderSent extends ActiveRecord
{
    /**
     * @param NewsReminder $reminder
     * @param News $news
     * @return NewsReminderSent
     */
    public static function create(NewsReminder $reminder, News $news)
    {
        $instance = new static(['reminder_id' => $reminder->id]);
        $instance->news_id = $news->id;
        $instance->save();

        return $instance;
    }

    public static function check(NewsReminder $reminder, News $news = null)
    {
        return !empty(static::findByReminder($reminder, $news)->all());
    }

    /**
     * @param NewsReminder $reminder
     * @param News $news
     * @return \yii\db\ActiveQuery
     */
    public static function findByReminder(NewsReminder $reminder, News $news = null)
    {
        $condition = ['reminder_id' => $reminder->id];
        if ($news) {
            $condition['news_id'] = $news->id;
        }

        return static::find()->where($condition);
    }

}
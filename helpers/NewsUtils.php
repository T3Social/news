<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\helpers;

use DateTimeZone;
use humhub\modules\content\models\Content;
use humhub\modules\news\models\News;
use Yii;

/**
 * Helper for News
 */
class NewsUtils
{

    /**
     * @param $model
     * @return News|null
     */
    public static function getNews($model)
    {
        if (!$model) {
            return null;
        }

        if ($model instanceof Content) {
            $model = $model->getModel();
        }

        if ($model instanceof News) {
            return $model;
        }

        if (method_exists($model, 'getNews')) {
            $news = $model->getNews();
            if ($news instanceof News) {
                return $news;
            }
        }

        return null;
    }

    public static function getSystemTimeZone($asString = false)
    {
        return $asString ? Yii::$app->timeZone : new DateTimeZone(Yii::$app->timeZone);
    }
}

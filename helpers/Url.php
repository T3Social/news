<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\helpers;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\news\models\News;
use yii\helpers\Url as BaseUrl;

class Url extends BaseUrl
{
    const ROUTE_CONFIG = '/news/config';
    const ROUTE_CONFIG_REMINDER = '/news/config/reminder';

    const ROUTE_NEWS_MANAGER = '/news/manager';
    const ROUTE_NEWS_SETTINGS = '/news/settings';

    const ROUTE_NEWS_ADD = '/news/manager/add';
    const ROUTE_NEWS_EDIT = '/news/manager/edit';
    const ROUTE_NEWS_DELETE = '/news/manager/delete';

    const ROUTE_NEWS_ENTRY_CONFIRM_READING = '/news/entry/confirm-reading';

    private static function create($route, $params = [], ContentContainerActiveRecord $container = null)
    {
        if ($container) {
            return $container->createUrl($route, $params);
        } else {
            $params[0] = $route;
            return static::to($params);
        }
    }

    public static function toConfig()
    {
        return static::create(static::ROUTE_CONFIG);
    }

    public static function toConfigReminder()
    {
        return static::create(static::ROUTE_CONFIG_REMINDER);
    }

    public static function toNewsManager(ContentContainerActiveRecord $container = null)
    {
        return static::create(static::ROUTE_NEWS_MANAGER, [], $container);
    }

    public static function toNewsSettings(ContentContainerActiveRecord $container = null)
    {
        return static::create(static::ROUTE_NEWS_SETTINGS, [], $container);
    }

    public static function toAddNews(ContentContainerActiveRecord $container = null)
    {
        return static::create(static::ROUTE_NEWS_ADD, [], $container);
    }

    public static function toEditNews(News $news)
    {
        return static::create(static::ROUTE_NEWS_EDIT, ['id' => $news->id], $news->content->getContainer());
    }

    public static function toDeleteNews(News $news)
    {
        return static::create(static::ROUTE_NEWS_DELETE, ['id' => $news->id], $news->content->getContainer());
    }

    public static function toConfirmReadingNewsEntry(News $news)
    {
        return static::create(static::ROUTE_NEWS_ENTRY_CONFIRM_READING, ['id' => $news->id], $news->content->getContainer());
    }

}
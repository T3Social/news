<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\modules\content\widgets\WallEntryLinks;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\news\Events;
use humhub\modules\space\widgets\Menu;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\stream\models\StreamSuppressQuery;

return [
    'id' => 'news',
    'class' => 'humhub\modules\news\Module',
    'namespace' => 'humhub\modules\news',
    'events' => [
        ['class' => Menu::class, 'event' => Menu::EVENT_INIT, 'callback' => [Events::class, 'onSpaceMenuInit']],
        ['class' => SpaceSidebar::class, 'event' => SpaceSidebar::EVENT_INIT, 'callback' => [Events::class, 'onSpaceSidebarInit']],
        ['class' => DashboardSidebar::class, 'event' => DashboardSidebar::EVENT_INIT, 'callback' => [Events::class, 'onDashboardSidebarInit']],
        ['class' => WallEntryLinks::class, 'event' => WallEntryLinks::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryLinksInit']],
        ['class' => CronController::class, 'event' => CronController::EVENT_BEFORE_ACTION, 'callback' => [Events::class, 'onCronRun']],
        ['class' => StreamQuery::class, 'event' => StreamQuery::EVENT_BEFORE_FILTER, 'callback' => [Events::class, 'onStreamQueryBeforeApplyFilters']],
        ['class' => StreamSuppressQuery::class, 'event' => StreamSuppressQuery::EVENT_BEFORE_FILTER, 'callback' => [Events::class, 'onStreamSuppressQueryBeforeApplyFilters']],
    ],
];

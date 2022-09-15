<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news;

use humhub\modules\content\widgets\WallEntryLinks;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\filters\ReadNewsStreamFilter;
use humhub\modules\news\models\forms\ContainerSettings;
use humhub\modules\news\models\forms\ModuleSettings;
use humhub\modules\news\models\News;
use humhub\modules\news\models\reminder\ReminderProcessor;
use humhub\modules\news\permissions\ManageNews;
use humhub\modules\news\widgets\ConfirmButton;
use humhub\modules\news\widgets\DashboardSidebarWidget;
use humhub\modules\news\widgets\SpaceSidebarWidget;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\HeaderControlsMenu;
use humhub\modules\space\widgets\Sidebar;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\stream\models\StreamSuppressQuery;
use humhub\modules\ui\menu\MenuLink;
use Throwable;
use Yii;
use yii\helpers\Console;

class Events
{
    public static function onSpaceMenuInit($event)
    {
        try {
            /* @var $menu HeaderControlsMenu */
            $menu = $event->sender;

            if (!$menu->space->moduleManager->isEnabled('news') ||
                !$menu->space->can(ManageNews::class)) {
                return;
            }

            $menu->addEntry((new MenuLink)
                ->setLabel(Yii::t('NewsModule.base', 'News'))
                ->setUrl(Url::toNewsManager($menu->space))
                ->setIcon(Module::ICON)
                ->setSortOrder(10000)
                ->setIsActive(Yii::$app->controller->module && Yii::$app->controller->module->id === 'news'));

        } catch (Throwable $e) {
            Yii::error($e, 'news');
        }
    }

    public static function onSpaceSidebarInit($event)
    {
        try {
            /* @var Sidebar $sidebar */
            $sidebar = $event->sender;

            if ($sidebar->space !== null && $sidebar->space->moduleManager->isEnabled('news')) {
                $spaceSettings = new ContainerSettings(['contentContainer' => $sidebar->space]);
                if ($spaceSettings->showSidebarWidget()) {
                    $sidebar->addWidget(SpaceSidebarWidget::class,
                        ['contentContainer' => $sidebar->space],
                        ['sortOrder' => $spaceSettings->sidebarOrder()]
                    );
                }
            }
        } catch (Throwable $e) {
            Yii::error($e, 'news');
        }
    }

    public static function onDashboardSidebarInit($event)
    {
        try {
            $moduleSettings = new ModuleSettings();

            if ($moduleSettings->showSidebarWidget()) {
                $event->sender->addWidget(DashboardSidebarWidget::class, [], ['sortOrder' => $moduleSettings->sidebarOrder()]);
            }
        } catch (Throwable $e) {
            Yii::error($e, 'news');
        }
    }

    public static function onWallEntryLinksInit($event)
    {
    }

    public static function onCronRun($event)
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('news');
        $lastRunTS = $module->settings->get('lastReminderRunTS');

        if (!$lastRunTS || ((time() - $lastRunTS) >= $module->getReminderProcessIntervalS())) {
            try {
                $controller = $event->sender;
                $controller->stdout('Running news reminder process... ');
                (new ReminderProcessor())->run();
                $controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
            } catch (Throwable $e) {
                Yii::error($e, 'news');
                $controller->stdout('error.' . PHP_EOL, Console::FG_RED);
                $controller->stderr("\n" . $e->getTraceAsString() . "\n", Console::BOLD);
            }
            $module->settings->set('lastReminderRunTS', time());
        }
    }

    public static function onStreamQueryBeforeApplyFilters($event)
    {
        if ($event->sender instanceof StreamQuery) {
            $event->sender->addFilterHandler(ReadNewsStreamFilter::class);
        }
    }

    public static function onStreamSuppressQueryBeforeApplyFilters($event)
    {
        if ($event->sender instanceof StreamSuppressQuery) {
            Yii::$app->getModule('stream')->streamSuppressQueryIgnore[] = News::class;
        }
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\widgets;

use humhub\components\Widget;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\forms\ModuleSettings;
use humhub\modules\news\models\News;
use humhub\modules\stream\widgets\StreamViewer;
use Yii;

/**
 * DashboardSidebarWidget shows news inside a dashboard sidebar.
 */
class DashboardSidebarWidget extends Widget
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $settings = new ModuleSettings();
        if (!$settings->showSidebarWidget()) {
            return '';
        }

        $news = News::find()
            ->readable()
            ->orderBy('id DESC')
            ->limit($settings->sidebarLimit())
            ->all();

        if (empty($news)) {
            return '';
        }

        $extraMenus = [];
        if (Yii::$app->user->can(ManageModules::class)) {
            $extraMenus[] = [
                'url' => Url::toConfig(),
                'icon' => 'fa-cog',
                'text' => Yii::t('NewsModule.base', 'Settings'),
            ];
        }

        return $this->render('sidebar', [
            'models' => $news,
            'extraMenus' => $extraMenus,
        ]);
    }
}

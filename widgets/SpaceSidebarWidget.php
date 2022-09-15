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
use humhub\modules\news\models\forms\ContainerSettings;
use humhub\modules\news\models\News;
use humhub\modules\stream\widgets\StreamViewer;
use Yii;

/**
 * SpaceSidebarWidget shows news inside a space sidebar.
 */
class SpaceSidebarWidget extends Widget
{

    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $settings = new ContainerSettings(['contentContainer' => $this->contentContainer]);

        if (!$settings->showSidebarWidget()) {
            return;
        }

        $query = News::find()
            ->contentContainer($this->contentContainer)
            ->readable()
            ->orderBy('id DESC');

        $totalNews = $query->count();

        if (empty($totalNews)) {
            return;
        }

        $extraMenus = [];
        if (Yii::$app->user->can(ManageModules::class)) {
            $extraMenus[] = [
                'url' => Url::toNewsSettings($this->contentContainer),
                'icon' => 'fa-cog',
                'text' => Yii::t('NewsModule.base', 'Settings'),
            ];
        }

        return $this->render('sidebar', [
            'models' => $query->limit($settings->sidebarLimit())->all(),
            'extraMenus' => $extraMenus,
            'container' => $this->contentContainer,
            'total' => $totalNews
        ]);
    }
}

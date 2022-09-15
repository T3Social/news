<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\controllers;

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\news\models\forms\ContainerSettings;
use Yii;

class SettingsController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [['permissions' => ManageModules::class]];
    }

    /**
     * Action for display and update Settings of Content Container(Space)
     */
    public function actionIndex()
    {
        $settings = new ContainerSettings([
            'contentContainer' => $this->contentContainer
        ]);

        if ($settings->load(Yii::$app->request->post()) && $settings->save()) {
            $this->view->saved();
        }

        return $this->render('container', [
            'settings' => $settings,
        ]);
    }
}

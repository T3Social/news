<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\news\controllers;

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\components\Controller;
use humhub\modules\news\models\forms\ModuleSettings;
use humhub\modules\news\models\reminder\forms\ReminderSettings;
use Yii;

class ConfigController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [['permissions' => ManageModules::class]];
    }

    /**
     * Action for common Module Settings
     */
    public function actionIndex()
    {
        $settings = new ModuleSettings();

        if ($settings->load(Yii::$app->request->post()) && $settings->save()) {
            $this->view->saved();
        }

        return $this->render('@news/views/settings/module', [
            'settings' => $settings,
        ]);
    }

    /**
     * Action for global Reminder Settings
     */
    public function actionReminder()
    {
        $settings = new ReminderSettings();

        if ($settings->load(Yii::$app->request->post()) && $settings->save()) {
            $this->view->saved();
        }

        return $this->render('@news/views/settings/moduleReminder', [
            'settings' => $settings,
        ]);
    }
}

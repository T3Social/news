<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $settings ModuleSettings */

use humhub\modules\news\models\forms\ModuleSettings;
use humhub\modules\news\widgets\ConfigMenu;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('NewsModule.settings', '<strong>News</strong>') ?></div>

    <?= ConfigMenu::widget() ?>

    <div class="panel-body">
        <h4><?= Yii::t('NewsModule.settings', 'Settings'); ?></h4>
        <div class="help-block">
            <?= Yii::t('NewsModule.settings', 'General settings for the News module.') ?>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'module-config-form']); ?>

            <?= $form->field($settings, $settings::ACTIVE_USER_DAYS) ?>
            <div class="help-block">
                <?= Yii::t('NewsModule.settings', 'Prevents e.g. inactive users from receiving unwanted messages or new user accounts from subsequently receiving all existing "News" of a space.') ?>
            </div>

            <?= $this->render('common', [
                'form' => $form,
                'settings' => $settings,
            ]); ?>

            <?= Button::save()->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
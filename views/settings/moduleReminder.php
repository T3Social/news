<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $this View */
/* @var $settings ReminderSettings */

use humhub\modules\news\models\reminder\forms\ReminderSettings;
use humhub\modules\news\widgets\ConfigMenu;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('NewsModule.settings', '<strong>News</strong> module settings') ?></div>

    <?= ConfigMenu::widget() ?>

    <div class="panel-body">
        <h4><?= Yii::t('NewsModule.settings', 'Reminder settings'); ?></h4>
        <div class="help-block">
            <?= Yii::t('NewsModule.settings', 'Here you can configure global reminders for news.') ?>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'module-reminder-form']); ?>

            <?= $this->render('reminderSettings', ['settings' => $settings, 'form' => $form])?>

            <?= Button::save()->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
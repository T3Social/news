<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $settings ContainerSettings */

use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\forms\ContainerSettings;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('NewsModule.settings', '<strong>News</strong>') ?></div>

    <div class="panel-body">
        <div class="help-block">
            <?= Yii::t('NewsModule.settings', 'Adjust the settings of the "News" module for this single Space.'); ?>
        </div>
        <?= Button::back(Url::toNewsManager($settings->contentContainer), Yii::t('NewsModule.settings', 'Back to overview'))->xs() ?>
        <br />
        <?php $form = ActiveForm::begin(['id' => 'container-settings-form']); ?>

            <?= $this->render('common', [
                'form' => $form,
                'settings' => $settings,
            ]); ?>

            <?= Button::save()->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $news News */

use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\News;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use humhub\widgets\Link;

?>
<div class="panel panel-default">
    <div class="panel-heading"><strong>News</strong> <?= Yii::t('NewsModule.base', 'manager') ?></div>

    <div class="panel-body">
        <?= Button::back(Url::toNewsManager($news->content->getContainer()), Yii::t('NewsModule.base', 'Back'))->sm(); ?>

        <h4><?= $news->isNewRecord ? Yii::t('NewsModule.base', 'Create new entry') : Yii::t('NewsModule.base', 'Edit entry'); ?></h4>

        <div class="help-block">
            <?= Yii::t('NewsModule.base', 'Write a News article and define its settings.') ?>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'news-form']); ?>

        <?= $form->field($news, 'title') ?>

        <?= $form->field($news, 'article')->widget(RichTextField::class); ?>

        <?= $form->field($news, 'confirm_reading')->checkbox() ?>

        <?= $form->field($news, 'send_notification')->checkbox(['disabled' => (bool)$news->send_notification]) ?>

        <?= Button::save($news->isNewRecord ? Yii::t('NewsModule.base', 'Create') : null)->submit() ?>

        <?php if (!$news->isNewRecord) : ?>
            <?= Link::danger(Yii::t('NewsModule.base', 'Delete'))->post($news->getDeleteUrl())->pjax(false)->confirm() ?>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

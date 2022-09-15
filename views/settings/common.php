<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $form ActiveForm */
/* @var $settings CommonSettings */

use humhub\modules\news\models\forms\CommonSettings;
use yii\bootstrap\ActiveForm;
?>

<strong><?= Yii::t('NewsModule.settings', 'Sidebar') ?></strong><br><br>
<?= $form->field($settings, 'showSidebarWidget')->checkbox() ?>

<?= $form->field($settings, 'sidebarLimit')->textInput(['disabled' => !$settings->showSidebarWidget()])->label(null, ['class' => false]) ?>

<?= $form->field($settings, 'sidebarOrder')->textInput(['disabled' => !$settings->showSidebarWidget()])->label(null, ['class' => false]) ?>

<script>
$('[type=checkbox][name$="[showSidebarWidget]"]').on('click', function () {
    $('[name$="[sidebarLimit]"], [name$="[sidebarOrder]"]').prop('disabled', !$(this).prop('checked'))
})
</script>

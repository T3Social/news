<?php

use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailButton;
use humhub\modules\news\helpers\Url;

/* @var $this yii\web\View */
/* @var $viewable \humhub\modules\news\notifications\NewsCreatedNotification */
/* @var $url string */
/* @var $date string */
/* @var $isNew boolean */
/* @var $originator \humhub\modules\user\models\User */
/* @var $source \humhub\modules\news\models\News */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $space humhub\modules\space\models\Space */
/* @var $record \humhub\modules\notification\models\Notification */
/* @var $html string */
/* @var $text string */

$button = Yii::t('NewsModule.base', 'Open news');
if ($source->confirm_reading) {
    $button = Yii::t('NewsModule.base', 'View News');
    $url = Url::toConfirmReadingNewsEntry($source);
}

?>

<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 16px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-highlight', '#555555') ?>; font-weight:300; text-align:left;">
                <?= Html::encode($source->title); ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td class="content">
                <?= RichTextToEmailHtmlConverter::process($source->article, ['receiver' => $record->user]) ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td>
                <?=
                MailButtonList::widget(['buttons' => [MailButton::widget(['url' => $url, 'text' => $button])]]);
                ?>
            </td>
        </tr>
    </table>

    <style>
        td.content img {
            max-width:560px;
        }
    </style>

<?php
$this->endContent();

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $models News[] */
/* @var $extraMenus array */
/* @var $total int */

/* @var $container ContentContainerActiveRecord|null */

use humhub\libs\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\news\assets\Assets;
use humhub\modules\news\models\News;
use humhub\modules\news\Module;
use humhub\widgets\Link;
use humhub\widgets\PanelMenu;
use humhub\widgets\TimeAgo;

Assets::register($this);

$extraMenusHtml = '';
foreach ($extraMenus as $extraMenu) {
    $extraMenusHtml .= '<li><a href="' . $extraMenu['url'] . '"><i class="fa ' . $extraMenu['icon'] . '"></i> ' . $extraMenu['text'] . '</a></li>';
}
?>

<div class="panel panel-default panel-news" id="panel-news">
    <?= PanelMenu::widget(['id' => 'panel-news', 'extraMenus' => $extraMenusHtml]) ?>
    <div class="panel-heading">
        <?= Module::icon() ?> <?= Yii::t('NewsModule.base', '<strong>Recent</strong> news') ?>
    </div>

    <div class="panel-body" style="padding:0">
        <ul class="media-list">
            <?php foreach ($models as $news) : ?>
                <li>
                    <a href="<?= $news->content->getUrl() ?>">
                        <div class="media">
                            <div class="media-body text-break">
                                <strong><?= Html::encode($news->title) ?></strong><br>
                                <div class="subline">
                                    <?php if (!isset($container) || !($container instanceof ContentContainerActiveRecord)) : ?>
                                        <?= Yii::t('NewsModule.base', '{someTimeAgo} &middot; {spaceContainerName}', [
                                            'someTimeAgo' => TimeAgo::widget(['timestamp' => $news->content->created_at]),
                                            'userName' => Html::encode($news->content->createdBy->displayName),
                                            '{spaceContainerName}' => Html::a(Html::encode($news->content->container->displayName), $news->content->container->getUrl(), ['class' => 'colorLink']),
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Yii::t('NewsModule.base', '{someTimeAgo} &middot; {userName}', [
                                            'userName' => Html::a(Html::encode($news->content->createdBy->displayName), $news->content->createdBy->getUrl(), ['class' => 'colorLink']),
                                            'someTimeAgo' => TimeAgo::widget(['timestamp' => $news->content->created_at])
                                        ])
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (isset($container) && $container instanceof ContentContainerActiveRecord && $total > count($models)) : ?>
            <?= Html::beginTag('div', ['style' => 'padding-bottom: 10px; text-align: center']) ?>
            <?= Link::defaultType(Yii::t('NewsModule.base', 'Show all'))
                ->action('news.filterContentType')
                ->options(['data-content-type' => News::class])
                ->loader(false)
                ->xs() ?>
            <?= Html::endTag('div') ?>
        <?php endif; ?>
    </div>
</div>
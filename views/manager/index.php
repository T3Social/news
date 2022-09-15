<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $contentContainer ContentContainerActiveRecord */

/* @var $dataProvider ActiveDataProvider */

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\News;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\widgets\Link;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;

?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('NewsModule.base', '<strong>News</strong>') ?></div>

    <div class="panel-body">
        <div class="clearfix">
            <?php if (Yii::$app->user->can(ManageModules::class)) : ?>
                <?= Button::defaultType()->icon('fa-cog')->link(Url::toNewsSettings($contentContainer))->xs()->right()->style('margin-left:5px'); ?>
            <?php endif; ?>
            <?= Button::success(Yii::t('NewsModule.base', 'Create News'))->icon('fa-plus')->link(Url::toAddNews($contentContainer))->xs()->right(); ?>

            <h4><?= Yii::t('NewsModule.base', 'Overview') ?></h4>
            <div class="help-block">
                <?= Yii::t('NewsModule.base', 'This overview shows you all News of this Space.'); ?>
            </div>
        </div>

        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => '',
                'columns' => [
                    'title',
                    ['class' => ImageColumn::class, 'userAttribute' => 'authorUser'],
                    [
                        'label' => Yii::t('NewsModule.base', 'Author'),
                        'attribute' => 'author',
                        'class' => DisplayNameColumn::class,
                        'userAttribute' => 'authorUser',
                        'options' => ['width' => '170px'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'options' => ['style' => 'width:170px; min-width:170px;'],
                        'value' => function (News $model) {
                            return $model->content->created_at;
                        }
                    ],
                    [
                        'class' => ActionColumn::class,
                        'options' => ['width' => '80px'],
                        'buttons' => [
                            'update' => function ($url, News $model) {
                                return Link::primary()->icon('fa-pencil')->link($model->getEditUrl())->xs();
                            },
                            'view' => function ($url, News $model) {
                                return Link::primary()->icon('fa-eye')->link($model->content->getUrl())->xs();
                            },
                            'delete' => function () {
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

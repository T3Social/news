<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $news News */

use humhub\modules\news\assets\Assets;
use humhub\modules\news\models\News;
use yii\bootstrap\Progress;

Assets::register($this);
?>

<div class="pull-right">
    <span data-toggle="tooltip" data-placement="top" class="tt"
          title="<?= Yii::t('NewsModule.base', '{confirmedMembersCount} of {allMembersCount} users reached.', [
              '{confirmedMembersCount}' => $news->getConfirmedMembersCount(),
              '{allMembersCount}' => $news->getReadingMembersCount(),
          ]) ?>">
        <i class="fa fa-eye" aria-hidden="true"></i> <?= $news->getConfirmedMembersCount() ?>
    </span>
</div>
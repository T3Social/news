<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $news News */

/* @var $isDetailView bool */

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\news\models\News;
use humhub\modules\news\widgets\ConfirmButton;

?>

<div data-news="<?= $news->id ?>" data-content-key="<?= $news->content->id ?>">
    <div data-ui-markdown<?= $isDetailView ? '' : ' data-ui-show-more' ?>>
        <?= RichText::output($news->article) ?>
    </div>

    <?= ConfirmButton::widget(['news' => $news]) ?>

</div>
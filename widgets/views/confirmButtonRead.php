<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $news News */

use humhub\modules\news\assets\Assets;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\News;
use humhub\widgets\Button;

Assets::register($this);
?>
<div class="confirm">
    <br/>

    <p>
        <?= Button::info(Yii::t('NewsModule.base', 'Mark as read'))
            ->action('news.confirmReading', Url::toConfirmReadingNewsEntry($news))
            ->id('news-mark-read')
            ->title($news->getReadingInfo())
        ?>
    </p>

    <?= $this->render('confirmReadingProgress', ['news' => $news]); ?>
</div>

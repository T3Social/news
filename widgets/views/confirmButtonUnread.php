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
        <?= Button::defaultType('<i class="fa fa-check" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('NewsModule.base', 'Unmark'))
            ->action('news.confirmReading', Url::toConfirmReadingNewsEntry($news))
            ->title($news->getReadingInfo())
        ?>
    </p>

    <?= $this->render('confirmReadingProgress', ['news' => $news]); ?>
</div>


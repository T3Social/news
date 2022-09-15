<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ReminderFormAssets extends AssetBundle
{
    /**
     * @inheritDoc
     */
    public $sourcePath = '@news/resources';

    /**
     * @inheritDoc
     */
    public $js = [
        'js/humhub.news.reminder.Form.js',
    ];

    /**
     * @inheritDoc
     */
    public $jsOptions = ['position' => View::POS_END];

    /**
     * @inheritDoc
     */
    public $publishOptions = [
        'forceCopy' => false
    ];
}

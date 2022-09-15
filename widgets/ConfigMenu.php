<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\widgets;

use humhub\modules\news\helpers\Url;
use humhub\widgets\SettingsTabs;
use Yii;

class ConfigMenu extends SettingsTabs
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->items = [
            [
                'label' => Yii::t('NewsModule.settings', 'General'),
                'url' => Url::toConfig(),
                'active' => $this->isCurrentRoute('news', 'config', 'index'),
                'sortOrder' => 10
            ],
            [
                'label' => Yii::t('NewsModule.settings', 'Reminder'),
                'url' =>  Url::toConfigReminder(),
                'active' => $this->isCurrentRoute('news', 'config', 'reminder'),
                'sortOrder' => 20
            ],
        ];

        parent::init();
    }

}
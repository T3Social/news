<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\forms;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerSettingsManager;
use Yii;

/**
 * This is the form for News Container Settings
 */
class ContainerSettings extends CommonSettings
{

    /**
     * @var ContentContainerActiveRecord $contentContainer
     */
    public $contentContainer;

    /**
     * @var ContentContainerSettingsManager module setting manager instance
     */
    protected $settings;

    /**
     * @inheritdoc
     */
    protected function initSettings()
    {
        $this->settings = $this->module->settings->contentContainer($this->contentContainer);
    }
}

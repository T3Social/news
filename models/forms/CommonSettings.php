<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\forms;

use humhub\components\SettingsManager;
use humhub\modules\news\Module;
use Yii;
use yii\base\Model;

/**
 * This is the form for News Common Settings
 */
abstract class CommonSettings extends Model
{

    const SHOW_SIDEBAR_WIDGET = 'showSidebarWidget';
    const SIDEBAR_LIMIT = 'sidebarLimit';
    const SIDEBAR_ORDER = 'sidebarOrder';

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var SettingsManager module setting manager instance
     */
    protected $settings;

    /**
     * @var bool
     */
    public $showSidebarWidget;

    /**
     * @var int
     */
    public $sidebarLimit;

    /**
     * @var int
     */
    public $sidebarOrder;

    public function init()
    {
        parent::init();

        /* @var $module Module */
        $this->module = Yii::$app->getModule('news');

        $this->showSidebarWidget = $this->showSidebarWidget();
        $this->sidebarLimit = $this->sidebarLimit();
        $this->sidebarOrder = $this->sidebarOrder();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['showSidebarWidget', 'sidebarOrder'], 'integer'],
            ['sidebarLimit', 'integer', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'showSidebarWidget' => Yii::t('NewsModule.settings', 'Enable sidebar widget'),
            'sidebarLimit' => Yii::t('NewsModule.settings', 'Number of entries'),
            'sidebarOrder' => Yii::t('NewsModule.settings', 'Sort Order'),
        ];
    }

    /**
     * Initialize settings
     */
    abstract protected function initSettings();

    /**
     * @return SettingsManager
     */
    protected function getSettings()
    {
        if (!$this->settings) {
            $this->initSettings();
        }
        return $this->settings;
    }

    /**
     * Save settings
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->getSettings()->set(self::SHOW_SIDEBAR_WIDGET, $this->showSidebarWidget);
        $this->getSettings()->set(self::SIDEBAR_LIMIT, $this->sidebarLimit);
        $this->getSettings()->set(self::SIDEBAR_ORDER, $this->sidebarOrder);

        return true;
    }

    /**
     * Check to show widget on sidebar
     *
     * @return bool
     */
    public function showSidebarWidget()
    {
        return (bool) $this->getSettings()->get(self::SHOW_SIDEBAR_WIDGET, true);
    }

    /**
     * Number of news in the sidebar widget
     *
     * @return int
     */
    public function sidebarLimit()
    {
        return (int) $this->getSettings()->get(self::SIDEBAR_LIMIT, 5);
    }

    /**
     * Widget order in the sidebar
     *
     * @return int
     */
    public function sidebarOrder()
    {
        return (int) $this->getSettings()->get(self::SIDEBAR_ORDER, 0);
    }
}

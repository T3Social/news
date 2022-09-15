<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\forms;

use Yii;

/**
 * This is the form for News Module Settings
 */
class ModuleSettings extends CommonSettings
{
    const ACTIVE_USER_DAYS = 'activeUserDays';

    /**
     * @var int
     */
    public $activeUserDays;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->{self::ACTIVE_USER_DAYS} = $this->activeUserDays();
    }

    /**
     * @inheritdoc
     */
    protected function initSettings()
    {
        $this->settings = $this->module->settings;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [self::ACTIVE_USER_DAYS, 'integer', 'min' => 1],
            [self::ACTIVE_USER_DAYS, 'required'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            self::ACTIVE_USER_DAYS => Yii::t('NewsModule.settings', 'Days since which a user must have last logged in to be considered'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!parent::save()) {
            return false;
        }

        $this->getSettings()->set(self::ACTIVE_USER_DAYS, $this->{self::ACTIVE_USER_DAYS});

        return true;
    }

    /**
     * Get days number to consider what space members should be
     * counted as ALL members for progress bar of confirmation reading a news
     *
     * @return int
     */
    public function activeUserDays()
    {
        return (int) $this->getSettings()->get(self::ACTIVE_USER_DAYS, 30);
    }
}

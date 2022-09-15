<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news;

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\News;
use humhub\modules\space\models\Space;
use Yii;

class Module extends ContentContainerModule
{
    const ICON = 'fa-newspaper-o';

    /**
     * @var int max amount of reminder allowed in the reminder settings
     */
    const MAX_REMINDER = 2;

    /**
     * @var int Reminder process run interval in minutes
     */
    const REMINDER_PROCESS_INTERVAL = 10;

    /**
     * @var int Defines the maximum number of news the reminder process can handle at once
     */
    const REMINDER_PROCESS_NEWS_LIMIT = 500;

    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            Space::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        foreach (News::find()->all() as $news) {
            $news->delete();
        }

        // Cleanup all module data, don't remove the parent::disable()!!!
        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        foreach (News::find()->contentContainer($container)->all() as $news) {
            $news->delete();
        }

        // Clean up space related data, don't remove the parent::disable()!!!
        parent::disableContentContainer($container);
    }

    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        return Url::toNewsSettings($container);
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerName(ContentContainerActiveRecord $container)
    {
        return Yii::t('NewsModule.base', 'News');
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        return Yii::t('NewsModule.base', 'News');
    }

    /**
     * Get icon html tag for this Module
     *
     * @return string
     */
    public static function icon()
    {
        return '<i class="fa ' . static::ICON . '"></i>';
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [
                new permissions\ManageNews(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::toConfig();
    }

    /**
     * Get a reminder process run interval in seconds
     *
     * @return int
     */
    public function getReminderProcessIntervalS()
    {
        return self::REMINDER_PROCESS_INTERVAL * 60;
    }
}

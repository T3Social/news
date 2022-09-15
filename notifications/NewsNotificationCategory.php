<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\notifications;

use humhub\modules\notification\components\NotificationCategory;
use Yii;

class NewsNotificationCategory extends NotificationCategory
{
    /**
     * @var string the category id
     */
    public $id = 'news';

    /**
     * Returns a human readable title of this  category
     */
    public function getTitle()
    {
        return Yii::t('NewsModule.notifications_NewsNotificationCategory', 'News');
    }

    /**
     * Returns a group description
     */
    public function getDescription()
    {
        return Yii::t('NewsModule.notifications_NewsNotificationCategory', 'Receive News related Notifications.');
    }
}
<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\notifications;

use humhub\libs\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Space;
use Yii;

/**
 * Class NewsCreatedNotification
 */
class NewsCreatedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'news';

    /**
     * @inheritdoc
     */
    public $viewName = 'news';

    /**
     * @inheritdoc
     */
    public $suppressSendToOriginator = false;

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new NewsNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        if ($this->source->content->container instanceof Space) {
            return Yii::t('NewsModule.notifications', '{displayName} has created the News "{contentTitle}" in Space {spaceName}.', [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                'contentTitle' => $this->getContentInfo($this->source, false),
                'spaceName' => Html::encode($this->source->content->container->displayName)
            ]);
        }

        return Yii::t('NewsModule.notifications', '{displayName} has created the News "{contentTitle}".', [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'contentTitle' => $this->getContentInfo($this->source, false)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return $this->source->title;
    }
}
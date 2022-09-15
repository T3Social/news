<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\notifications;

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\news\models\News;
use humhub\modules\notification\components\BaseNotification;
use Yii;

class Remind extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'news';

    /**
     * @var bool
     */
    public $suppressSendToOriginator = false;

    /**
     * @inheritdoc
     */
    public $viewName = 'news.php';

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
        if ($this->source instanceof News) {
            return Yii::t('NewsModule.reminder', 'You have a not confirmed News: {title}', [
                'title' => RichText::preview($this->source->title, 25)
            ]);
        }

        return Yii::t('NewsModule.reminder', 'You have a not confirmed News');
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        /** @var News $news */
        $news = $this->source;

        return Yii::t('NewsModule.reminder', 'Reminder - Action required: {title}', [
            'title' => $this->source->title
        ]);

    }
}
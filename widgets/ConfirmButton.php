<?php

namespace humhub\modules\news\widgets;

use humhub\modules\content\widgets\WallEntryLinks;
use humhub\modules\news\models\News;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\Widget;

/**
 * This widget is used to show a button to confirm a reading of the news inside the wall entry controls.
 */
class ConfirmButton extends Widget
{

    /**
     * @var News
     */
    public $news;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!$this->canShow()) {
            return '';
        }

        if (!$this->news->canBeConfirmed()) {
            return '';
        }

        $buttonView = $this->news->isConfirmed() ? 'confirmButtonUnread' : 'confirmButtonRead';

        return $this->render($buttonView, [
            'news' => $this->news
        ]);
    }


    private function canShow()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if (!($this->news->content->container instanceof Space) ||
            !$this->news->content->container->moduleManager->isEnabled('news') ||
            !$this->news->canBeConfirmed()) {
            return false;
        }

        return true;
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\news\models\News;
use humhub\modules\news\widgets\ConfirmButton;
use yii\web\HttpException;

class EntryController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['confirm-reading']],
        ];
    }

    /**
     * Confirm reading/unreading a News entry
     *
     * @param int $id
     */
    public function actionConfirmReading($id)
    {
        $news = $this->findNews($id);

        if (!$news->canBeConfirmed()) {
            throw new HttpException(403);
        }

        $news->confirmReading();

        if ($this->request->isAjax) {
            return ConfirmButton::widget(['news' => $news]);
        }

        return $this->redirect($news->content->getUrl());
    }


    /**
     * Find a News by id
     *
     * @param int $id
     * @return News
     */
    private function findNews(int $id)
    {
        if (empty($id)) {
            throw new HttpException(404);
        }

        $news = News::findOne(['id' => $id]);

        if (!$news) {
            throw new HttpException(404);
        }

        if (!$news->content->canView()) {
            throw new HttpException(403);
        }

        return $news;
    }
}

<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\news\helpers\Url;
use humhub\modules\news\models\News;
use humhub\modules\news\permissions\ManageNews;
use Yii;
use yii\data\ActiveDataProvider;

class ManagerController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_PERMISSION => [ManageNews::class]],
        ];
    }

    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => News::find()
                ->joinWith('authorUser')
                ->contentContainer($this->contentContainer)
                ->readable(),
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'attributes' => [
                    'title',
                    'author' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['content.created_at' => SORT_ASC],
                        'desc' => ['content.created_at' => SORT_DESC],
                    ],
                ],
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        return $this->render('index', [
            'contentContainer' => $this->contentContainer,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Add news
     *
     * @return string
     */
    public function actionAdd()
    {
        return $this->actionEdit();
    }

    /**
     * Edit news
     *
     * @param int|null $id
     * @return string
     */
    public function actionEdit($id = null)
    {
        $news = News::findOne(['id' => $id]);

        if (!$news) {
            $news = new News($this->contentContainer);
        }

        if ($news->load(Yii::$app->request->post()) &&
            $news->validate() &&
            $news->save()) {
            $this->view->saved();
            return $this->redirect(Url::toNewsManager($this->contentContainer));
        }

        return $this->render('edit', [
            'news' => $news,
        ]);
    }

    /**
     * Delete news
     *
     * @param int|null $id
     * @return string
     */
    public function actionDelete($id = null)
    {
        $news = News::findOne(['id' => $id]);

        if ($news && $news->delete()) {
            $this->view->success(Yii::t('NewsModule.base', 'Deleted'));
        }

        return $this->redirect(Url::toNewsManager($this->contentContainer));
    }

}


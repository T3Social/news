<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamModuleEntryWidget;
use humhub\modules\news\models\News;

/**
 * WallEntry is used to display a news inside the stream.
 */
class WallEntry extends WallStreamModuleEntryWidget
{

    /**
     * @var News the content type model to render.
     */
    public $model;

    /**
     * @inheritDoc
     */
    public $editRoute = '/news/manager/edit';

    /**
     * @inheritDoc
     */
    public $editMode = WallStreamEntryWidget::EDIT_MODE_NEW_WINDOW;
    
    /**
     * @inheritDoc
     */
    public function renderContent()
    {
        return $this->render('entry', [
            'news' => $this->model,
            'isDetailView' => $this->renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_DETAIL),
        ]);
    }

    /**
     * @return string a non encoded plain text title (no html allowed) used in the header of the widget
     */
    protected function getTitle()
    {
        return $this->model->title;
    }
}
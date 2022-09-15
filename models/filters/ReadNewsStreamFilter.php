<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\filters;

use humhub\modules\content\models\Content;
use humhub\modules\news\models\News;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use Yii;
use yii\db\Expression;

/**
 * This stream filter manages the stream order with not confirmed news by current User.
 * This filter can not be deactivated by request parameter.
 */
class ReadNewsStreamFilter extends StreamQueryFilter
{
    /**
     * @inheritdoc
     */
    public function apply()
    {
        if ($this->streamQuery->isInitialQuery() || $this->isLoadingSuppressedTopNotConfirmedNews()) {
            $this->orderTopNotConfirmedNews();
        } else if(!$this->streamQuery->isSingleContentQuery()) {
            $this->excludeTopNotConfirmedNews();
        }
    }

    /**
     * Join all required tables to pin not confirmed news on top
     */
    protected function joinNewsTables()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->query->leftJoin('news', 'content.object_model = :newsClass AND content.object_id = news.id');
        $this->query->leftJoin('news_read', 'news.id = news_read.news_id AND news_read.user_id = :userId');

        $this->query->addParams($this->getNewsQueryParams());
    }

    /**
     * Get params for news queries
     *
     * @return array
     */
    protected function getNewsQueryParams()
    {
        return [
            ':newsClass' => News::class,
            ':userId' => Yii::$app->user->id,
            ':userCreatedAt' => Yii::$app->user->getIdentity()->created_at,
        ];
    }

    /**
     * Order not confirmed news on top of stream
     */
    protected function orderTopNotConfirmedNews()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->joinNewsTables();

        $originalOrderBy = $this->query->orderBy;
        $this->query->orderBy(['IF(news.confirm_reading = 1 AND news_read.news_id IS NULL AND content.created_at >= :userCreatedAt, 1, 0)' => SORT_DESC]);
        $this->query->addOrderBy($originalOrderBy);
    }

    /**
     * Exclude not confirmed news from stream because they are already pinned on top on first page
     */
    protected function excludeTopNotConfirmedNews()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->joinNewsTables();

        $this->query->andWhere('content.object_model != :newsClass OR news.confirm_reading = 0 OR news_read.news_id IS NOT NULL OR content.created_at < :userCreatedAt');
    }

    /**
     * Check if it is a loading of suppressed/hidden top not confirmed news by click on "Show x more [NEWS]"
     *
     * @return bool
     */
    protected function isLoadingSuppressedTopNotConfirmedNews()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $streamQueryParams = Yii::$app->request->get('StreamQuery');

        if (empty($streamQueryParams['suppressionsOnly']) || empty($streamQueryParams['from'])) {
            return false;
        }

        return Content::find()
            ->innerJoin('news', 'content.object_model = :newsClass AND content.object_id = news.id')
            ->leftJoin('news_read', 'news.id = news_read.news_id AND news_read.user_id = :userId')
            ->where(['content.id' => $streamQueryParams['from']])
            ->andWhere(['IS', 'news_read.news_id', new Expression('NULL')])
            ->andWhere(['news.confirm_reading' => 1])
            ->andWhere('content.created_at >= :userCreatedAt')
            ->addParams($this->getNewsQueryParams())
            ->limit(1)
            ->exists();
    }
}

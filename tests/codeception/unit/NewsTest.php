<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace news;

use humhub\modules\news\models\News;
use tests\codeception\_support\HumHubDbTestCase;

class NewsTest extends HumHubDbTestCase
{
    public function testCreateNews()
    {
        $this->becomeUser('Admin');
        $news = $this->createTestNews();

        $this->assertTrue(!$news->isNewRecord);
        $this->assertTrue($news->canBeConfirmed());
    }

    public function testMarkAsRead()
    {
        $this->becomeUser('Admin');
        $news = $this->createTestNews();

        $this->assertFalse($news->isConfirmed());
        $news->confirmReading();
        $this->assertTrue($news->isConfirmed());
    }

    /**
     * @return News
     */
    protected function createTestNews()
    {
        $news = new News();
        $news->title = 'First news title';
        $news->article = 'First news article';
        $news->confirm_reading = 1;
        $news->send_notification = 1;
        $news->save();

        return $news;
    }
}
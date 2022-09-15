<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\tests\codeception\fixtures;

use humhub\modules\news\models\News;
use yii\test\ActiveFixture;

class NewsFixture extends ActiveFixture
{
    public $modelClass = News::class;
    public $dataFile = '@news/tests/codeception/fixtures/data/news.php';
    public $depends = [
        'humhub\modules\news\tests\codeception\fixtures\NewsReadFixture',
        'humhub\modules\news\tests\codeception\fixtures\NewsReminderFixture',
        'humhub\modules\news\tests\codeception\fixtures\NewsReminderSentFixture',
    ];
}

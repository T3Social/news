<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\tests\codeception\fixtures;

use humhub\modules\news\models\reminder\NewsReminder;
use yii\test\ActiveFixture;

class NewsReminderFixture extends ActiveFixture
{
    public $modelClass = NewsReminder::class;
    public $dataFile = '@news/tests/codeception/fixtures/data/news_reminder.php';
}

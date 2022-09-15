<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\tests\codeception\fixtures;

use humhub\modules\news\models\NewsRead;
use yii\test\ActiveFixture;

class NewsReadFixture extends ActiveFixture
{
    public $modelClass = NewsRead::class;
    public $dataFile = '@news/tests/codeception/fixtures/data/news_read.php';
}

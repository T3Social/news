<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models;

use humhub\components\ActiveRecord;

/**
 * This is the model class for table 'news_read'.
 *
 * The followings are the available columns in table 'news_read':
 * @property integer $user_id
 * @property integer $news_id
 * @property string $created_at
 */
class NewsRead extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'news_read';
    }

    /**
     * @inhritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'user_id'], 'integer'],
            [['news_id', 'user_id'], 'required'],
        ];
    }
}
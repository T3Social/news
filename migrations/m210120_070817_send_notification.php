<?php

use humhub\components\Migration;

/**
 * Class m210120_070817_send_notification
 */
class m210120_070817_send_notification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('news', 'send_notification', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropColumn('news', 'send_notification');
    }
}

<?php

use humhub\components\Migration;

/**
 * Class m201221_105530_confirm_reading
 */
class m201221_105530_confirm_reading extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('news_read', [
            'user_id' => $this->integer(),
            'news_id' => $this->integer(),
            'created_at' => $this->dateTime()->null(),
        ]);
        $this->safeAddPrimaryKey('pk_news_read', 'news_read', 'user_id,news_id');
        $this->safeAddForeignKey('fk_news_read_user_id', 'news_read', 'user_id', 'user', 'id', 'CASCADE');
        $this->safeAddForeignKey('fk_news_read_news_id', 'news_read', 'news_id', 'news', 'id', 'CASCADE');

        $this->safeAddColumn('news', 'confirm_reading', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropTable('news_read');

        $this->safeDropColumn('news', 'confirm_reading');
    }
}

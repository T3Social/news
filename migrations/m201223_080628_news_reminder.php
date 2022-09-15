<?php

use humhub\components\Migration;

/**
 * Class m201223_080628_news_reminder
 */
class m201223_080628_news_reminder extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('news_reminder', [
            'id' => $this->primaryKey(),
            'contentcontainer_id' => $this->integer()->null(),
            'news_id' => $this->integer()->null(),
            'unit' => $this->tinyInteger(1)->null(),
            'value' => $this->tinyInteger(2)->null(),
            'active' => $this->boolean()->defaultValue(1),
            'disabled' => $this->boolean()->defaultValue(0),
        ]);
        $this->safeAddForeignKey('fk_news_reminder_container_id', 'news_reminder', 'contentcontainer_id', 'contentcontainer', 'id', 'CASCADE');
        $this->safeAddForeignKey('fk_news_reminder_content_id', 'news_reminder', 'news_id', 'news', 'id', 'CASCADE');

        $this->safeCreateTable('news_reminder_sent', [
            'reminder_id' => $this->integer(),
            'news_id' => $this->integer(),
            'created_at' => $this->dateTime()->null(),
        ]);
        $this->safeAddPrimaryKey('pk_news_reminder_sent', 'news_reminder_sent', 'reminder_id,news_id');
        $this->safeAddForeignKey('fk_news_reminder_sent_id', 'news_reminder_sent', 'reminder_id', 'news_reminder', 'id', 'CASCADE');
        $this->safeAddForeignKey('fk_news_reminder_sent_news_id', 'news_reminder_sent', 'news_id', 'news', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->safeDropTable('news_reminder_sent');
        $this->safeDropTable('news_reminder');
    }
}

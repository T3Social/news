<?php
use humhub\components\Migration;

class uninstall extends Migration
{

    public function up()
    {
        $this->safeDropTable('news');
        $this->safeDropTable('news_read');
        $this->safeDropTable('news_reminder');
        $this->safeDropTable('news_reminder_sent');
    }

    public function down()
    {
        echo "uninstall does not support migration down.\n";
        return false;
    }
}
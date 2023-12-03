<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%news}}`.
 */
class m231203_181650_create_news_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%news}}', [
            'id' => $this->primaryKey(),
            'last_date' => $this->dateTime()->notNull(),
            'title' => $this->string(255),
            'link' => $this->string()->unique(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%news}}');
    }
}

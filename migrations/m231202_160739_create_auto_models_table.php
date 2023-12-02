<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%auto_models}}`.
 */
class m231202_160739_create_auto_models_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%auto_models}}', [
            'id' => $this->primaryKey(),
            'model_id' => $this->integer()->notNull(),
            'name' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auto_models}}');
    }
}

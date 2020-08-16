<?php

use yii\db\Migration;

/**
 * Class m200814_185021_add_image
 */
class m200814_185021_add_image extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%image}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'extension' => $this->string()->notNull(),
        ]);

        $this->createIndex('idx_image_user_id', '{{%image}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%image}}');
    }
}

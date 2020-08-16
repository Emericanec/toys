<?php

use yii\db\Migration;

/**
 * Class m200815_085544_add_image_to_goods
 */
class m200815_085544_add_image_to_goods extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%image_to_goods}}', [
            'image_id' => $this->integer()->notNull(),
            'good_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('image_to_goods_pk', '{{%image_to_goods}}', ['image_id', 'good_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%image_to_goods}}');
    }
}

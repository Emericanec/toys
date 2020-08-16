<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class ImageToGoods
 * @package app\models
 *
 * @property integer $image_id
 * @property integer $good_id
 */
class ImageToGoods extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%image_to_goods}}';
    }
}

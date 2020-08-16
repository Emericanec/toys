<?php

declare(strict_types=1);

namespace app\models;

use app\rbac\models\Role;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class Good
 * @package app\models
 *
 * @property integer $id
 * @property string $title
 * @property integer $user_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property Image[] $images
 */
class Good extends ActiveRecord
{
    public const STATUS_NEW = 1;
    public const STATUS_APPROVED = 2;

    public static function tableName(): string
    {
        return '{{%goods}}';
    }

    public function fields(): array
    {
        $fields = [
            'id',
            'title',
            'created_at',
            'updated_at',
            'images',
        ];

        if (Yii::$app->getUser()->can(Role::ROLE_ADMIN)) {
            $fields[] = 'status';
        }

        return $fields;
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getImages(): ActiveQuery
    {
        return $this->hasMany(Image::class, ['id' => 'image_id'])
            ->viaTable(ImageToGoods::tableName(), ['good_id' => 'id']);
    }
}

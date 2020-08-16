<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Class Image
 * @package app\models
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $extension
 */
class Image extends ActiveRecord
{
    public const MAX_HEIGHT = 1024;
    public const MAX_WIDTH = 1024;

    public static function tableName(): string
    {
        return '{{%image}}';
    }

    public function fields(): array
    {
        return [
            'id',
            'name' => function (): string {
                return $this->getFileName();
            },
            'url' => function (): string {
                return $this->getUrl();
            },
        ];
    }

    public function getFileName(): string
    {
        return $this->name . '.' . $this->extension;
    }

    public function getUrl(): string
    {
        return Url::toRoute(['image/get', 'id' => $this->id], true);
    }

    public function getPath(): string
    {
        return __DIR__ . '/../uploads/' . $this->getFileName();
    }
}

<?php

declare(strict_types=1);

namespace app\requests;

use app\models\Image;
use Yii;

class AddGoodRequest extends AbstractRequest
{
    private string $title;

    private array $images = [];

    public function __construct()
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $this->title = (string)($body['title'] ?? '');

        if (isset($body['images']) && is_array($body['images'])) {
            $this->images = $body['images'];
        }
    }

    public function validate(): bool
    {
        if (empty($this->title)) {
            $this->error = 'Title is required';

            return false;
        }

        if (!empty($this->images)) {
            $imageCount = Image::find()->where(['id' => $this->images])->count();
            if ($imageCount !== count($this->images)) {
                $this->error = 'Image with this ids does not exist';

                return false;
            }
        }

        return true;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getImages(): array
    {
        return $this->images;
    }
}

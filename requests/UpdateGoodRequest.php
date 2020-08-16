<?php

declare(strict_types=1);

namespace app\requests;

use app\models\Good;
use app\models\Image;
use Yii;

class UpdateGoodRequest extends AbstractRequest
{
    private string $title;

    private int $id;

    private ?int $status;

    private ?Good $good;

    private array $images = [];

    public function __construct(int $id)
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $this->id = $id;
        $this->title = (string)($body['title'] ?? '');
        $this->status = isset($body['status']) ? (int)$body['status'] : null;

        if (isset($body['images']) && is_array($body['images'])) {
            $this->images = $body['images'];
        }
    }

    public function validate(): bool
    {
        $this->good = Good::findOne($this->id);
        if (null === $this->good) {
            $this->error = 'Goods with this id not found';

            return false;
        }

        if (empty($this->title)) {
            $this->error = 'Title can not be empty';

            return false;
        }

        if (null !== $this->status && !in_array($this->status, [Good::STATUS_NEW, Good::STATUS_APPROVED], true)) {
            $this->error = 'Status is invalid';

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getGood(): Good
    {
        return $this->good;
    }

    public function getImages(): array
    {
        return $this->images;
    }
}

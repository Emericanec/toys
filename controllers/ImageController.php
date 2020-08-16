<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Image;
use Exception;
use Ramsey\Uuid\Uuid;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ImageController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        $behaviors['authenticator']['except'] = ['get'];

        return $behaviors;
    }

    public function actionUpload(): array
    {
        Yii::$app->response->statusCode = 400;

        if (!Yii::$app->request->isPost) {
            return [
                'errors' => ['method should be a POST']
            ];
        }

        if (!isset($_FILES['file'])) {
            return [
                'errors' => ['File is required']
            ];
        }

        $imgMaker = \Gregwar\Image\Image::open($_FILES['file']['tmp_name']);

        $uuid = Uuid::uuid4();

        $image = new Image();
        $image->name = $uuid->toString();
        $image->user_id = Yii::$app->user->getId();
        $image->extension = $imgMaker->guessType();

        try {
            if ($imgMaker->width() > Image::MAX_WIDTH || $imgMaker->height() > Image::MAX_HEIGHT) {
                $imgMaker->cropResize(Image::MAX_WIDTH, Image::MAX_HEIGHT);
            }
            $imgMaker->save($image->getPath());
        } catch (Exception $exception) {
            return [
                'errors' => ['Error while saving file']
            ];
        }

        if (!$image->save()) {
            return [
                'errors' => [$image->getErrors()]
            ];
        }

        Yii::$app->response->statusCode = 200;

        return [
            'success' => true,
            'id' => $image->id
        ];
    }

    /**
     * @param int $id
     * @throws ServerErrorHttpException
     */
    public function actionGet(int $id)
    {
        /** @var Image|null $image */
        $image = Image::findOne($id);

        if (null === $image) {
            Yii::$app->response->statusCode = 400;
            return Yii::$app->response->send();
        }

        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', 'image/' . $image->extension);
        $response->format = Response::FORMAT_RAW;
        if (!is_resource($response->stream = fopen($image->getPath(), 'rb'))) {
            throw new ServerErrorHttpException('file access failed: permission deny');
        }

        return $response->send();
    }
}

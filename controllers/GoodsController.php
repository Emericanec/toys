<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Good;
use app\models\ImageToGoods;
use app\rbac\models\Role;
use app\requests\AddGoodRequest;
use app\requests\UpdateGoodRequest;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;

class GoodsController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        $behaviors['authenticator']['except'] = ['list'];

        return $behaviors;
    }

    public function actionList(): array
    {
        $query = Good::find();

        $status = Yii::$app->getUser()->can(Role::ROLE_ADMIN)
            ? Yii::$app->request->getQueryParam('status', null)
            : Good::STATUS_APPROVED;

        if (null !== $status) {
            $query = $query->where(['status' => $status]);
        }

        return [
            'goods' => $query->all(),
        ];
    }

    public function actionAdd(): array
    {
        $request = new AddGoodRequest();
        if (!$request->validate()) {
            Yii::$app->response->statusCode = 400;

            return ['errors' => [$request->getError()]];
        }

        $good = new Good();
        $good->title = $request->getTitle();
        $good->user_id = Yii::$app->getUser()->getId();
        $good->status = Good::STATUS_NEW;
        $good->created_at = $good->updated_at = time();

        if (!$good->save()) {
            Yii::$app->response->statusCode = 400;

            return ['errors' => [$good->getErrors()]];
        }

        foreach ($request->getImages() as $imageId) {
            $model = new ImageToGoods();
            $model->image_id = (int)$imageId;
            $model->good_id = $good->id;
            if (!$model->save()) {
                return ['errors' => ['Error while saving image']];
            }
        }

        return [
            'good' => $good
        ];
    }

    public function actionUpdate(int $id): array
    {
        $request = new UpdateGoodRequest($id);
        if (!$request->validate()) {
            Yii::$app->response->statusCode = 400;

            return ['errors' => [$request->getError()]];
        }

        $good = $request->getGood();
        $good->title = $request->getTitle();
        if (null !== $request->getStatus()) {
            $good->status = $request->getStatus();
        }

        if (!$good->save()) {
            Yii::$app->response->statusCode = 400;

            return ['errors' => [$good->getErrors()]];
        }

        foreach ($request->getImages() as $imageId) {
            $model = new ImageToGoods();
            $model->image_id = (int)$imageId;
            $model->good_id = $good->id;
            if (!$model->save()) {
                return ['errors' => ['Error while saving image']];
            }
        }

        return [
            'good' => $good
        ];
    }
}

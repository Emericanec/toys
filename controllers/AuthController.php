<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\models\UserIdentity;
use app\requests\LoginRequest;
use app\requests\SignupRequest;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\Cors;
use yii\rest\Controller;

class AuthController extends Controller
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

        $behaviors['authenticator']['except'] = ['login', 'signup'];

        return $behaviors;
    }

    public function actionLogin(): array
    {
        $request = new LoginRequest();
        if (!$request->validate()) {
            Yii::$app->response->statusCode = 400;

            return ['msg' => $request->getError()];
        }

        $expire = time() + Yii::$app->params['JwtExpire'];
        $token = $this->generateToken($request->getUser(), $expire);

        return [
            'msg' => 'logged in successful',
            'token' => (string)$token,
            'expires_in' => $expire
        ];
    }

    public function actionRefresh(): array
    {
        /** @var UserIdentity|null $user */
        $user = Yii::$app->user->getIdentity();

        if (null === $user) {
            Yii::$app->response->statusCode = 400;

            return ['msg' => 'token is not valid!'];
        }

        $expire = time() + Yii::$app->params['JwtExpire'];
        $token = $this->generateToken($user, $expire);

        return [
            'msg' => 'ok',
            'token' => (string)$token,
            'expires_in' => $expire
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionSignup(): array
    {
        $request = new SignupRequest();
        if (!$request->validate()) {
            Yii::$app->response->statusCode = 400;

            return ['msg' => $request->getError()];
        }

        $user = new User();
        $user->username = $request->getUsername();
        $user->email = $request->getEmail();
        $user->setPassword($request->getPassword());
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = $user->updated_at = time();

        if (!$user->save()) {
            Yii::$app->response->statusCode = 400;

            return ['errors' => $user->getErrors()];
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole(Yii::$app->params['DefaultSignupRole']);
        $auth->assign($role, $user->id);

        Yii::$app->response->statusCode = 201;
        $expire = time() + Yii::$app->params['JwtExpire'];
        $token = $this->generateToken($user, $expire);

        return [
            'msg' => 'ok',
            'token' => (string)$token,
            'expires_in' => $expire
        ];
    }

    public function actionMe(): array
    {
        /** @var UserIdentity|null $user */
        $user = Yii::$app->user->getIdentity();

        if (null === $user) {
            Yii::$app->response->statusCode = 404;

            return ['msg' => 'Not found'];
        }

        return [
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role' => $user->getRoleName(),
        ];
    }

    private function generateToken(User $user, int $expire): Token
    {
        $signer = new Sha256();

        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;

        return $jwt->getBuilder()
            ->setIssuer(Yii::$app->params['JwtIssuer'])// Configures the issuer (iss claim)
            ->setAudience(Yii::$app->params['JwtAudience'])// Configures the audience (aud claim)
            ->setId(Yii::$app->params['TokenID'], true)// Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
            ->setExpiration($expire)// Configures the expiration time of the token (exp claim)
            ->set('uid', $user->id)// Configures a new claim, called "uid"
            ->sign($signer, $jwt->key)// creates a signature using [[Jwt::$key]]
            ->getToken(); // Retrieves the generated token
    }
}

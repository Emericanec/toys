<?php

declare(strict_types=1);

namespace app\requests;

use app\models\User;
use Yii;
use yii\base\InvalidConfigException;

class LoginRequest extends AbstractRequest
{
    private string $email;

    private string $password;

    private ?User $user;

    /**
     * LoginRequest constructor.
     * @throws InvalidConfigException
     */
    public function __construct()
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $this->email = (string)($body['email'] ?? '');
        $this->password = (string)($body['password'] ?? '');
    }

    public function validate(): bool
    {
        if (empty($this->email)) {
            $this->error = 'Email is required';

            return false;
        }

        if (empty($this->password)) {
            $this->error = 'Password is required';

            return false;
        }

        $this->user = User::findByEmail($this->email);
        if (null === $this->user || !$this->user->validatePassword($this->password)) {
            $this->error = 'Email or Password is invalid';

            return false;
        }

        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

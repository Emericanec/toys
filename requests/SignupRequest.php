<?php

declare(strict_types=1);

namespace app\requests;

use Yii;
use yii\base\InvalidConfigException;

class SignupRequest extends AbstractRequest
{
    private string $email;

    private string $password;

    private string $username;

    /**
     * SignupRequest constructor.
     * @throws InvalidConfigException
     */
    public function __construct()
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $this->username = (string)($body['username'] ?? '');
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

        if (empty($this->username)) {
            $this->error = 'Username is required';

            return false;
        }

        return true;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}

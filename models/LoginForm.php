<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Developer;

class LoginForm extends Model
{
    public $company_email;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['company_email', 'password'], 'required'],
            ['company_email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect email or password.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user && $user->status === User::STATUS_INACTIVE) {
                $this->addError('username', 'Your account is not verified. Please check your email for the verification link.');
                return false;
            }
            return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByEmail($this->company_email);
        }

        return $this->_user;
    }
}
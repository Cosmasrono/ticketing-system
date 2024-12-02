<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Client;

class LoginForm extends Model
{
    public $company_email;
    public $password;
    public $rememberMe = true;
    public $isFirstLogin = false;
    public $new_password;
    public $confirm_password;

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

    public function attributeLabels()
    {
        return [
            'company_email' => 'Company Email',
            'password' => 'Password',
            'rememberMe' => 'Remember Me',
        ];
    }

    public function validateClientEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $client = Client::findOne(['company_email' => $this->company_email]);
            if (!$client) {
                $this->addError($attribute, 'This email is not registered as a client.');
            }
        }
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            if (!$user) {
                $this->addError($attribute, 'Incorrect email or password.');
                return;
            }

            if ($user->status !== 10) {
                $this->addError($attribute, 'This account has been deactivated.');
                return;
            }

            if (!$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect email or password.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user && $user->status == 10) {
                return Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
            } else {
                $this->addError('company_email', 'Account is inactive or not found.');
            }
        }
        return false;
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByEmail($this->company_email);
        }
        return $this->_user;
    }
}

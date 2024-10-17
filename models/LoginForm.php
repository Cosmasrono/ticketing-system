<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Client; // Add this line to import the Client model

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

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect email or password.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if (Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0)) {
                // Check if the user is an admin after successful login
                if ($user->isAdmin()) {
                    Yii::$app->session->set('isAdmin', true);
                }
                return true;
            }
        }
        return false;
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne(['company_email' => $this->company_email]);
        }

        return $this->_user;
    }
}

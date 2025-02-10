<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

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

    public function attributeLabels()
    {
        return [
            'company_email' => 'Email',
            'password' => 'Password',
            'rememberMe' => 'Remember Me',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            // Add debugging
            Yii::debug('Found user: ' . ($user ? 'Yes' : 'No'));
            if ($user) {
                Yii::debug('User status: ' . $user->status);
            }
            
            if (!$user) {
                $this->addError($attribute, 'Incorrect email or password.');
                return;
            }
            
            // Check status with debugging
            if ($user->status != User::STATUS_ACTIVE) {
                Yii::debug('User not active. Status: ' . $user->status);
                $this->addError($attribute, 'Your account is not active.');
                return;
            }

            if (!$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect email or password.');
            }
        }
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne([
                'company_email' => $this->company_email,
                'status' => [1, 10]  // Accept both status values
            ]);
        }
        return $this->_user;
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }
}

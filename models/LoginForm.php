<?php

namespace app\models;

use Yii;
use yii\base\Model;

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
            return Yii::$app->user->login($this->getUser());
        }
        return false;
    }
    
    

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByCompanyEmail($this->company_email);
        }

        return $this->_user;
    }
}
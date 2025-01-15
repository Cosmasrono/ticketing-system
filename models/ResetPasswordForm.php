<?php

namespace app\models;

use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['password', 'confirm_password'], 'required'],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'New Password',
            'confirm_password' => 'Confirm Password',
        ];
    }
}
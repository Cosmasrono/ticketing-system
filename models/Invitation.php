<?php

namespace app\models;

use Yii;
use yii\base\Model;

class Invitation extends Model
{
    public $company_name;
    public $company_email;
    public $role;
    public $module;

    public function rules()
    {
        return [
            [['company_name', 'company_email', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
            ['role', 'in', 'range' => ['developer', 'admin', 'user']],
            ['module', 'safe'],
        ];
    }

    public function generatePassword()
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
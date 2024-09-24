<?php

namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $name;
    public $company_email;
    public $company_name;
    public $password;
    public $role;

    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'password', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
            ['role', 'in', 'range' => [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_DEVELOPER]],
        ];
    }

    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->name = $this->name;
            $user->company_email = $this->company_email;
            $user->company_name = $this->company_name;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->role = $this->role;
            
            return $user->save() ? $user : null;
        }

        return null;
    }
}

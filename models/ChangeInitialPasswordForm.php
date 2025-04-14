<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ChangeInitialPasswordForm extends Model
{
    public $email;
    public $token;
    public $temporary_password;
    public $new_password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['email', 'token', 'temporary_password', 'new_password', 'confirm_password'], 'required'],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.'],
            ['new_password', 'string', 'min' => 8],
            ['temporary_password', 'validateTemporaryPassword'],
            // Password strength validation
            ['new_password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'],
        ];
    }

    public function validateTemporaryPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['company_email' => $this->email]);
            if (!$user || !$user->validatePassword($this->temporary_password)) {
                $this->addError($attribute, 'Incorrect temporary password.');
            }
        }
    }

    public function changePassword()
    {
        if ($this->validate()) {
            $user = User::findOne(['company_email' => $this->email]);
            if ($user) {
                $user->setPassword($this->new_password);
                return $user->save(false);
            }
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'temporary_password' => 'Temporary Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ];
    }
}
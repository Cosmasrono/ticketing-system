<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

class SetPasswordForm extends Model
{
    public $new_password;
    public $confirm_password;

    private $_user;

    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        // Find user by token
        $this->_user = User::findOne([
            'password_reset_token' => $token,
            'status' => 10
        ]);

        if (!$this->_user) {
            Yii::error('No user found with token: ' . $token);
            throw new InvalidArgumentException('Wrong or expired password reset token.');
        }

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['new_password', 'confirm_password'], 'required'],
            [['new_password', 'confirm_password'], 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.'],
        ];
    }

    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        if (!$user) {
            Yii::error('User not found when trying to reset password');
            return false;
        }

        $user->setPassword($this->new_password);
        $user->removePasswordResetToken();
        $user->first_login = 0;

        return $user->save(false);
    }

    public function attributeLabels()
    {
        return [
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
        ];
    }
}
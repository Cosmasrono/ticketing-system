<?php

namespace app\models;

use yii\base\Model;
use app\models\User;

class ResetPasswordForm extends Model
{
    public $password;
    public $confirm_password;

    private $_user;

    public function __construct($token, $config = [])
    {
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new \yii\web\BadRequestHttpException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['password', 'confirm_password'], 'required'],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
        ];
    }

    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
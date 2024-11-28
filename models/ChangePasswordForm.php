<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class ChangePasswordForm extends Model
{
    public $old_password;
    public $new_password;
    public $confirm_password;
    private $_user;

    public function __construct($email, $config = [])
    {
        $this->_user = User::findOne(['company_email' => $email]);
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['old_password', 'new_password', 'confirm_password'], 'required'],
            ['old_password', 'validateOldPassword'],
            ['new_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 
             'message' => 'Passwords do not match.'],
        ];
    }

    public function validateOldPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user || !Yii::$app->security->validatePassword($this->old_password, $this->_user->password_hash)) {
                $this->addError($attribute, 'Incorrect temporary password.');
            }
        }
    }

    public function changePassword()
    {
        if ($this->validate()) {
            $this->_user->password_hash = Yii::$app->security->generatePasswordHash($this->new_password);
            $this->_user->first_login = 0; // Mark as password changed
            $this->_user->updated_at = time();
            return $this->_user->save();
        }
        return false;
    }

    public function attributeLabels()
    {
        return [
            'old_password' => 'Temporary Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
        ];
    }
} 
<?php
namespace app\models;

use Yii;
use yii\base\Model;

class FirstLoginForm extends Model
{
    public $token;
    public $current_password;
    public $new_password;
    public $confirm_password;

    private $_user;

    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            ['current_password', 'validateCurrentPassword'],
            ['new_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'current_password' => 'Temporary Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            // Add debug logging
            Yii::debug("Validating temporary password for user: " . ($user ? $user->company_email : 'User not found'));
            Yii::debug("Submitted password: " . $this->current_password);
            
            if (!$user || !$user->validatePassword($this->current_password)) {
                Yii::debug("Password validation failed");
                $this->addError($attribute, 'Incorrect temporary password.');
                return false;
            }
            
            Yii::debug("Password validation successful");
            return true;
        }
    }

    public function changePassword()
    {
        if (!$this->validate()) {
            Yii::error("Validation failed: " . print_r($this->errors, true));
            return false;
        }

        $user = $this->getUser();
        if (!$user) {
            Yii::error("User not found for token: " . $this->token);
            return false;
        }

        try {
            // Set new password
            $user->setPassword($this->new_password);
            $user->first_login = 0;
            $user->status = 10;
            $user->password_reset_token = null;

            if (!$user->save(false)) {
                Yii::error("Failed to save user: " . print_r($user->errors, true));
                return false;
            }

            Yii::debug("Password changed and status updated successfully for user: " . $user->company_email);
            return true;

        } catch (\Exception $e) {
            Yii::error("Exception during password change: " . $e->getMessage());
            return false;
        }
    }

    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findOne(['password_reset_token' => $this->token]);
            Yii::debug("Found user with token: " . ($this->_user ? 'Yes' : 'No'));
        }
        return $this->_user;
    }
} 
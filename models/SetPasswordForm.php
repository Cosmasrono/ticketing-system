<?php

namespace app\models;

use Yii;
use yii\base\Model;

class SetPasswordForm extends Model
{
    public $company_email;
    public $password;
    public $confirmPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_email', 'password', 'confirmPassword'], 'required'],
            ['company_email', 'email'],
            ['password', 'string', 'min' => 8],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
            // Password strength validation
            ['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'],
        ];
    }

    /**
     * Changes password.
     *
     * @return bool if password was changed.
     */
    public function changePassword()
    {
        if ($this->validate()) {
            $user = User::findOne(['company_email' => $this->company_email]);
            if ($user) {
                $user->setPassword($this->password);
                $user->password_reset_token = null; // Clear the reset token
                $user->first_login = 0; // Mark as password changed
                return $user->save(false);
            }
        }
        return false;
    }
}
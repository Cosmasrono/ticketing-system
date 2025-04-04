<?php
// models/ResetPasswordForm.php

namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $password;
    public $confirm_password;
    public $current_password;
    
    private $_user;
    
    /**
     * Creates a form model given a token.
     *
     * @param string $token password reset token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        Yii::debug("Looking for user with token in ResetPasswordForm: " . $token);

        // Find user with exact token match
        $this->_user = User::findOne([
            'password_reset_token' => $token,
            'status' => User::STATUS_UNVERIFIED
        ]);

        if (!$this->_user) {
            Yii::error("No user found with token in ResetPasswordForm: $token");
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        Yii::debug("Found user: " . $this->_user->company_email);
        
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'confirm_password', 'current_password'], 'required'],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password'],
            ['current_password', 'validateTemporaryPassword'],
        ];
    }
    
    /**
     * Validates the temporary password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateTemporaryPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!Yii::$app->security->validatePassword($this->current_password, $this->_user->password_hash)) {
                $this->addError($attribute, 'Incorrect temporary password.');
            }
        }
    }
    
    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->status = User::STATUS_ACTIVE;
        $user->is_verified = 1;
        $user->email_verified = 1;

        return $user->save(false);
    }
}
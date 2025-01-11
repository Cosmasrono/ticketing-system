<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

class ResetPasswordForm extends Model
{
    public $new_password;
    public $confirm_password;
    
    private $_user;
    private $_token;

    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        
        $this->_token = $token;
        $user = User::findByPasswordResetToken($token);
        
        if (!$user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        
        $this->_user = $user;
        
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['new_password', 'confirm_password'], 'required'],
            ['new_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password'],
        ];
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        if (is_object($user)) {
            $user->setPassword($this->new_password);
            $user->removePasswordResetToken();

            return $user->save(false);
        }

        return false;
    }
}
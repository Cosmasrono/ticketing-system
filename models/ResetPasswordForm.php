<?php
// models/ResetPasswordForm.php
namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $password;
    public $confirmPassword;
    // Removed current_password as it shouldn't be required during reset
    
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
            [['password', 'confirmPassword'], 'required'],
            ['password', 'string', 'min' => 6],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
            // Removed validation for current_password
        ];
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
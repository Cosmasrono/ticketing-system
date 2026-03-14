<?php
/**
 * Reset Password Form Model
 * 
 * Handles password reset for users with valid reset tokens.
 * Allows users with any status to set their initial password.
 */

namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    /**
     * @var string New password
     */
    public $password;

    /**
     * @var string Password confirmation
     */
    public $confirmPassword;

    /**
     * @var User|null User instance
     */
    private $_user;

    /**
     * Constructor - validates and loads user by reset token
     *
     * @param string $token Password reset token
     * @param array $config Configuration array
     * @throws InvalidArgumentException If token is invalid
     */
    public function __construct($token, $config = [])
    {
        // Validate token exists and is a string
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        // Find user by reset token only
        // Status check removed to allow newly created users (status=20) to set password
        $this->_user = $this->findUserByToken($token);

        if (!$this->_user) {
            Yii::error('No user found with token: ' . $token);
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        parent::__construct($config);
    }

    /**
     * Find user by password reset token
     *
     * @param string $token The reset token
     * @return User|null The user model or null if not found
     */
    private function findUserByToken($token)
    {
        return User::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Validation rules
     *
     * @return array Validation rules
     */
    public function rules()
    {
        return [
            // Both password fields are required
            [['password', 'confirmPassword'], 'required', 'message' => '{attribute} cannot be blank.'],

            // Password minimum length
            ['password', 'string', 'min' => 6, 'tooShort' => 'Password must be at least 6 characters.'],

            // Password confirmation match
            ['confirmPassword', 'compare', 
                'compareAttribute' => 'password',
                'operator' => '==',
                'message' => 'Passwords do not match.',
            ],
        ];
    }

    /**
     * Attribute labels
     *
     * @return array Attribute labels for display
     */
    public function attributeLabels()
    {
        return [
            'password' => 'New Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }

    /**
     * Reset user password
     *
     * @return bool True if password was reset successfully, false otherwise
     */
    public function resetPassword()
    {
        // Validate form data
        if (!$this->validate()) {
            return false;
        }

        // Get user instance
        $user = $this->_user;

        // Update user data
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->status = User::STATUS_ACTIVE;
        $user->is_verified = true;
        $user->email_verified = true;

        // Save without running validation again (we already validated)
        return $user->save(false);
    }

    /**
     * Get the user instance
     *
     * @return User|null The user model
     */
    public function getUser()
    {
        return $this->_user;
    }
}
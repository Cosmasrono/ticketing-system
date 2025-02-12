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

    private $_user;

    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            Yii::error('Password reset token cannot be blank.');
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        // Debug the incoming token
        Yii::debug("Attempting to validate token: " . $token);

        // Find user first without timestamp validation
        $this->_user = User::findOne([
            'password_reset_token' => $token,
            'status' => [User::STATUS_ACTIVE, User::STATUS_UNVERIFIED, User::STATUS_INACTIVE]
        ]);

        if (!$this->_user) {
            Yii::error("No user found with token: " . $token);
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        // Now validate timestamp if token contains it
        if (strpos($token, '_') !== false) {
            $parts = explode('_', $token);
            if (count($parts) === 2) {
                $timestamp = (int)$parts[1];
                $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600; // 1 hour default

                Yii::debug("Token validation details:", 'password-reset');
                Yii::debug([
                    'token' => $token,
                    'timestamp' => $timestamp,
                    'current_time' => time(),
                    'expire_time' => $timestamp + $expire,
                    'is_expired' => ($timestamp + $expire < time()),
                    'user_id' => $this->_user->id,
                    'user_status' => $this->_user->status
                ]);

                if ($timestamp + $expire < time()) {
                    Yii::error("Token expired. Timestamp: {$timestamp}, Current: " . time());
                    throw new InvalidArgumentException('Password reset token expired.');
                }
            }
        }

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['password', 'confirmPassword'], 'required'],
            [['password', 'confirmPassword'], 'string', 'min' => 6],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'New Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }

    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;
        
        // Debug user status before update
        Yii::debug("Resetting password for user:", 'password-reset');
        Yii::debug([
            'user_id' => $user->id,
            'old_status' => $user->status,
            'new_status' => User::STATUS_ACTIVE
        ]);

        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->status = User::STATUS_ACTIVE;
        $user->is_verified = 1;
        $user->first_login = 0;
        $user->updated_at = time();
        
        return $user->save(false);
    }


// Add these actions to controllers/SiteController.php

public function actionRequestPasswordReset()
{
    $model = new PasswordResetRequestForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your company email for further instructions.');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
    }

    return $this->render('requestPasswordResetToken', [
        'model' => $model,
    ]);
}

public function actionResetPassword($token)
{
    try {
        $model = new ResetPasswordForm($token);
    } catch (InvalidArgumentException $e) {
        Yii::$app->session->setFlash('error', $e->getMessage());
        return $this->goHome();
    }

    if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
        Yii::$app->session->setFlash('success', 'New password was saved successfully.');
        return $this->goHome();
    }

    return $this->render('resetPassword', [
        'model' => $model,
    ]);
}
}
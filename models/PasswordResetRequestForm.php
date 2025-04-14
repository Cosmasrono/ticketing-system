<?php

namespace app\models;

use Yii;
use yii\base\Model;

class PasswordResetRequestForm extends Model
{
    public $company_email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['company_email', 'trim'],
            ['company_email', 'required'],
            ['company_email', 'email'],
            ['company_email', 'exist',
                'targetClass' => '\app\models\User',
                'targetAttribute' => 'company_email',
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
                'company_email' => 'Company Email',
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        // First, try without status check to see if email exists at all
        $userCheck = User::find()
            ->where(['company_email' => $this->company_email])
            ->one();
        
        if (!$userCheck) {
            Yii::error("Email not found in database: " . $this->company_email);
            return false;
        }

        // If email exists, check with status
        $user = User::findOne([
            'company_email' => $this->company_email,
            'status' => User::STATUS_ACTIVE  // Should be 10
        ]);

        if (!$user) {
            Yii::error("User found but not active. Email: {$this->company_email}, Status: {$userCheck->status}");
            return false;
        }

        $user->generatePasswordResetToken();
        
        if (!$user->save(false)) {
            Yii::error("Failed to save reset token. Errors: " . json_encode($user->errors));
            return false;
        }

        try {
            $sent = Yii::$app->mailer->compose(
                [
                    'html' => 'passwordResetToken-html',
                    'text' => 'passwordResetToken-text'
                ],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->company_email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();

            if (!$sent) {
                Yii::error("Failed to send email to: " . $this->company_email);
            }

            return $sent;
        } catch (\Exception $e) {
            Yii::error("Email error: " . $e->getMessage());
            return false;
        }
    }
}
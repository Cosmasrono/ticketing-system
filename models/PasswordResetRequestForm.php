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
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with this company email address.'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
                'company_email' => 'company_email ',
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        Yii::info("Attempting to send password reset email to: " . $this->company_email);

        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'company_email' => $this->company_email,
        ]);

        if (!$user) {
            Yii::error("No active user found with email: " . $this->company_email);
            return false;
        }

        Yii::info("Found user with ID: " . $user->id);

        // Generate new token
        $user->generatePasswordResetToken();
        Yii::info("Generated reset token: " . $user->password_reset_token);

        // Save the user with the new token
        if (!$user->save()) {
            Yii::error("Failed to save user with new token. Errors: " . json_encode($user->errors));
            return false;
        }

        Yii::info("Successfully saved user with new token");

        // Create reset URL
        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
        Yii::info("Created reset link: " . $resetLink);

        // Send email
        try {
            $result = Yii::$app->mailer
                ->compose(
                    ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                    ['user' => $user, 'resetLink' => $resetLink]
                )
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                ->setTo($this->company_email)
                ->setSubject('Password reset for ' . Yii::$app->name)
                ->send();

            if (!$result) {
                Yii::error("Failed to send email");
                return false;
            }

            Yii::info("Successfully sent password reset email");
            return true;

        } catch (\Exception $e) {
            Yii::error("Exception while sending email: " . $e->getMessage());
            return false;
        }
    }
}
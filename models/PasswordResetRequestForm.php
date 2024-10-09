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
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'company_email' => $this->company_email,
        ]);

        if (!$user) {
            Yii::error("User not found for email: {$this->company_email}");
            return false;
        }

        if (!$user->password_reset_token || !User::isPasswordResetTokenValid($user->password_reset_token)) {
            if (!$user->generatePasswordResetToken()) {
                Yii::error("Failed to generate password reset token for user: {$user->id}");
                return false;
            }
        }

        return $user->sendPasswordResetEmail();
    }
}
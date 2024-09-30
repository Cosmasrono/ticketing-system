<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

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
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);

        // Instead of sending an email, we'll return the reset link
        return $resetLink;
    }
}
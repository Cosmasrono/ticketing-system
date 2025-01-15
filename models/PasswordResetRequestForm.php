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
        $user = User::findOne([
            'company_email' => $this->company_email,
            'status' => 1
        ]);

        if (!$user) {
            return false;
        }

        $user->generatePasswordResetToken();
        
        if (!$user->save(false)) {
            return false;
        }

        return Yii::$app->mailer->compose(
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
    }
}
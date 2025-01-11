<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class ForgetPasswordForm extends Model
{
    public $company_email;

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

    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'company_email' => $this->company_email,
        ]);

        if ($user) {
            $user->generatePasswordResetToken();
            if ($user->save()) {
                return Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($this->company_email)
                    ->setSubject('Password reset for ' . Yii::$app->name)
                    ->send();
            }
        }

        return false;
    }
}
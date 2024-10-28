<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Invitation extends ActiveRecord
{
    public static function tableName()
    {
        return 'invitation';
    }

    public function rules()
    {
        return [
            [['company_email', 'role'], 'required'],
            ['company_email', 'email'],
            ['role', 'in', 'range' => ['developer', 'admin', 'user']],
            ['module', 'required', 'when' => function($model) {
                return $model->role === 'user';
            }, 'whenClient' => "function (attribute, value) {
                return $('#invitation-role').val() === 'user';
            }"],
            ['module', 'in', 'range' => ['HR', 'IT', 'All']], // Add more modules as needed
            ['token', 'string'],
            ['module', 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function attributeLabels()
    {
        return [
            'company_email' => 'Company Email',
            'module' => 'Module',
            'token' => 'Invitation Token',
        ];
    }

    public function generateToken()
    {
        $this->token = Yii::$app->security->generateRandomString(32);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if (empty($this->token)) {
                    $this->generateToken();
                }
                if ($this->role === 'admin' || $this->role === 'developer') {
                    $this->module = 'All';
                }
            }
            return true;
        }
        return false;
    }

    public function sendInvitationEmail()
    {
        $senderEmail = Yii::$app->params['senderEmail'] ?? 'noreply@example.com';
        $body = "You've been invited to join as a {$this->role}.";
        if ($this->role === 'user') {
            $body .= " You will have access to the {$this->module} module.";
        }
        $body .= " Click here to sign up: " . Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'token' => $this->token]);

        return Yii::$app->mailer->compose()
            ->setFrom($senderEmail)
            ->setTo($this->company_email)
            ->setSubject('Invitation to join')
            ->setTextBody($body)
            ->send();
    }

    public static function findByToken($token)
    {
        return static::findOne(['token' => $token]);
    }
}

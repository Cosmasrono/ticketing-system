<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;

class Invitation extends ActiveRecord
{
    public static function tableName()
    {
        return 'invitation';
    }

    public function rules()
    {
        return [
            ['company_name', 'string'],
            [['company_email', 'role'], 'required'],
            ['company_email', 'email'],
            ['role', 'in', 'range' => ['developer', 'admin', 'user']],
            ['module', 'string'],
            ['module', 'required', 'when' => function($model) {
                return $model->role === 'user';
            }, 'whenClient' => "function (attribute, value) {
                return $('#invitation-role').val() === 'user';
            }"],
            ['token', 'string'],
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
            'company_name' => 'Company Name',
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
                Yii::debug('Role: ' . $this->role);
                Yii::debug('Module: ' . $this->module);
                
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
        $signupUrl = Yii::$app->urlManager->createAbsoluteUrl([
            'site/signup',
            'token' => $this->token
        ]);

        // Create HTML email content
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Welcome to " . Yii::$app->name . "</h2>
                <p>Hello " . Html::encode($this->company_name) . ",</p>
                <p>You've been invited to join as a <strong>" . Html::encode($this->role) . "</strong>.</p>
                " . ($this->role === 'user' ? "<p>You will have access to the <strong>" . Html::encode($this->module) . "</strong> module.</p>" : "") . "
                <p style='margin: 25px 0;'>
                    <a href='" . $signupUrl . "' 
                       style='background-color: #4CAF50; color: white; padding: 12px 25px; 
                              text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Complete Your Registration
                    </a>
                </p>
                <p style='color: #666;'>
                    If the button doesn't work, copy and paste this link into your browser:<br>
                    <span style='color: #0066cc;'>" . $signupUrl . "</span>
                </p>
            </div>
        ";

        // Create plain text version
        $textBody = "Welcome to " . Yii::$app->name . "\n\n" .
                    "Hello " . $this->company_name . ",\n\n" .
                    "You've been invited to join as a " . $this->role . ".\n" .
                    ($this->role === 'user' ? "You will have access to the " . $this->module . " module.\n\n" : "") .
                    "Click here to complete your registration: " . $signupUrl;

        try {
            $sent = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->name])
                ->setTo($this->company_email)
                ->setSubject('Invitation to join ' . Yii::$app->name)
                ->setHtmlBody($htmlBody)
                ->setTextBody($textBody)
                ->send();

            // Add debugging
            Yii::debug('Email sending attempt to: ' . $this->company_email);
            Yii::debug('Signup URL generated: ' . $signupUrl);
            
            return $sent;
        } catch (\Exception $e) {
            Yii::error('Failed to send email: ' . $e->getMessage());
            return false;
        }
    }

    public static function findByToken($token)
    {
        return static::findOne(['token' => $token]);
    }
}
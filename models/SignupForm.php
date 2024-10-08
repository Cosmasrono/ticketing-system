<?php

namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $name;
    public $company_email;
    public $company_name;
    public $password;
    public $role;

    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'password', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
        ];
    }

    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->generateVerificationToken(); // Make sure this method exists and works correctly
            
            Yii::info("Generated verification token for new user: " . $user->verification_token);
            
            if ($user->save()) {
                Yii::info("New user saved successfully: ID = {$user->id}, Email = {$user->email}");
                return $user;
            }
            
            Yii::error("Failed to save new user. Errors: " . print_r($user->getErrors(), true));
        }
        
        return null;
    }
    
    protected function sendVerificationEmail($user)
    {
        $verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
    
        $subject = 'Account verification for ' . Yii::$app->name;
        $htmlBody = $this->renderEmailTemplate('emailVerify-html', ['user' => $user, 'verifyLink' => $verifyLink]);
        $textBody = $this->renderEmailTemplate('emailVerify-text', ['user' => $user, 'verifyLink' => $verifyLink]);
    
        $sent = Yii::$app->brevoMailer->send(
            $user->company_email,
            $subject,
            $htmlBody,
            $textBody
        );
    
        if (!$sent) {
            Yii::error('Failed to send verification email to ' . $user->company_email, 'signup');
        }
    
        return $sent;
    }
    

    private function renderEmailTemplate($view, $params)
    {
        return Yii::$app->view->render("@app/mail/{$view}", $params);
    }
}

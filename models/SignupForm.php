<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Admin;
use app\models\Developer;
use app\models\Client;

class SignupForm extends Model
{
    public $name;
    public $company_email;
    public $company_name;
    public $password;
    public $role;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'password', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'validateClientEmail'],
            ['password', 'string', 'min' => 6],
            ['role', 'in', 'range' => [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_DEVELOPER]],
        ];
    }

    public function validateClientEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!Client::emailExists($this->company_email)) {
                $this->addError($attribute, 'Only registered customers are allowed to sign up.');
            }
        }
    }

    /**
     * Signs up the user.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->name = $this->name;
            $user->company_email = $this->company_email;
            $user->company_name = $this->company_name;
            $user->setPassword($this->password);
            $user->role = $this->role;
            $user->generateAuthKey();
            
            if ($user->save()) {
                return $user;
            }
        }
        
        return null;
    }

    private function determineUserType()
    {
        // You need to implement logic here to determine the user type
        // This could be based on the email domain, a selection made during signup, etc.
        // For now, let's assume all new signups are clients
        return 'client';
    }

    private function createUserTypeRecord($userId, $userType)
    {
        switch ($userType) {
            case 'admin':
                $admin = new Admin();
                $admin->user_id = $userId;
                $admin->save();
                break;
            case 'developer':
                $developer = new Developer();
                $developer->user_id = $userId;
                $developer->save();
                break;
            case 'client':
                $client = new Client();
                $client->user_id = $userId;
                $client->save();
                break;
        }
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

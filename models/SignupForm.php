<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Admin;
use app\models\Developer;
use app\models\Client;
use app\models\Invitation;
use yii\db\Expression;

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
            [['name', 'company_email', 'company_name', 'password'], 'required'],
            ['name', 'string', 'min' => 2, 'max' => 255],
            ['company_email', 'email'],
            ['company_email', 'validateInvitation'],
            ['company_email', 'validateUniqueUser'],
            ['company_name', 'string', 'max' => 255],
            ['password', 'string', 'min' => 6],
            ['role', 'in', 'range' => ['developer', 'admin', 'user']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Full Name',
            'company_email' => 'Company Email',
            'company_name' => 'Company Name',
            'password' => 'Password',
            'role' => 'Role',
        ];
    }

    /**
     * Signs user up.
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
            $user->password = $this->password;
            $user->role = $this->role;
            
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

    public function validateInvitation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $invitationExists = Yii::$app->db->createCommand('SELECT EXISTS(SELECT 1 FROM invitation WHERE company_email = :email)', [':email' => $this->company_email])->queryScalar();
            if (!$invitationExists) {
                $this->addError($attribute, 'No valid invitation found for this email address.');
            }
        }
    }

    public function validateUniqueUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $userExists = Yii::$app->db->createCommand('SELECT EXISTS(SELECT 1 FROM user WHERE company_email = :email)', [':email' => $this->company_email])->queryScalar();
            if ($userExists) {
                $this->addError($attribute, 'This email address has already been taken.');
            }
        }
    }
}

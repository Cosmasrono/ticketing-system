<?php

namespace app\models;
use yii\db\Expression;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Admin;

class User extends ActiveRecord implements IdentityInterface
{
 
    public $password;
    public $module;
    public $issue;
    public $description;


    const SCENARIO_SIGNUP = 'signup';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SIGNUP] = ['name', 'company_email', 'company_name', 'password', 'role'];
        return $scenarios;
    }

    // Role constants
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_CLIENT = 'client';

    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_DELETED = 9;
    const STATUS_UNVERIFIED = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'password'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => self::class, 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
            // Add any other rules you need, but make sure they don't restrict signup
        ];
    }

    /**
     * {@inheritdoc}
     */
 

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_email' => 'Company Email',
            'company_name' => 'Company Name',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'role' => 'Role', 
            'verification_token',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     */
 



     public function beforeSave($insert)
     {
         if (!parent::beforeSave($insert)) {
             return false;
         }
 
         // Log the current state of the model before saving
         Yii::info("Attempting to save User model. Current state: " . json_encode($this->attributes));
 
         return true;
     }
 
     public function afterSave($insert, $changedAttributes)
     {
         parent::afterSave($insert, $changedAttributes);
 
         // Log the changes made to the model
         Yii::info("User model saved. Changed attributes: " . json_encode($changedAttributes));
 
         if ($insert) {
             // Check if the company email exists in the client table
             $isClient = Client::find()->where(['company_email' => $this->company_email])->exists();
 
             // Determine the role based on whether the email exists in the client table
             $this->role = $isClient ? 'admin' : 'user';
 
             // Save the role
             $this->save(false);
         }
     }
     
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
  
     public function getTickets()
     {
         return $this->hasMany(Ticket::class, ['user_id' => 'id']);
     }

    public static function findByCompanyEmail($companyEmail)
    {
        return static::findOne(['company_email' => $companyEmail]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */


     public function generateAuthKey()
     {
         $this->auth_key = Yii::$app->security->generateRandomString();
     }


     
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Set the password for the user
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    // public function generateAuthKey()
    // {
    //     $this->auth_key = Yii::$app->security->generateRandomString();
    // }

    // public function isDeveloper()
    // {
    //     // Adjust this condition based on how you identify developers in your system
    //     return $this->role === 'developer';
    // }

    public static function findDeveloper($id)
    {
        return static::findOne(['id' => $id, 'role' => 'developer']);
    }

    public function getDeveloper()
    {
        return $this->hasOne(Developer::class, ['id' => 'id']);
    }

    public function isDeveloper()
    {
        return $this->role === self::ROLE_DEVELOPER;
    }

    // Add this method if you don't have a username column
    public function getUsername()
    {
        return $this->company_email; // or whatever field you use to identify users
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['company_email']);
    }

    public static function findByEmail($email)
    {
        return static::findOne(['company_email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public function getAssignedTickets()
    {
        return $this->hasMany(Ticket::class, ['assigned_to' => 'id']);
    }

    public function isAdmin()
    {
        return Admin::isAdminEmail($this->company_email);
    }

    public function getCompanyEmail()
    {
        return $this->company_email;
    }

    /**
     * Checks if the user is an admin.
     * @return bool whether the user is an admin
     */
    public function getIsAdmin()
    {
        // Replace this with your actual logic to determine if a user is an admin
        // For example, if you have a 'role' column in your user table:
        return $this->role === 'admin';

        // Or if you have a separate 'is_admin' column:
        // return (bool) $this->is_admin;

        // Or if you're using RBAC:
        // return \Yii::$app->authManager->checkAccess($this->id, 'admin');
    }
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            Yii::error("Invalid password reset token: $token");
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            Yii::error("Empty password reset token");
            return false;
        }
        
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $isValid = $timestamp + $expire >= time();
        
        if (!$isValid) {
            Yii::error("Expired password reset token: $token");
        }
        
        return $isValid;
    }
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
        if (!$this->save(false)) {
            Yii::error("Failed to save user with new password reset token: " . json_encode($this->errors));
            return false;
        }
        Yii::info("Generated password reset token for user {$this->id}: {$this->password_reset_token}");
        return true;
    }
    
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
        return $this->save(false);
    }

    public function sendEmail()
    {
        if (!User::isPasswordResetTokenValid($this->password_reset_token)) {
            $this->generatePasswordResetToken();
            if (!$this->save()) {
                Yii::error("Failed to save user with new reset token: " . json_encode($this->errors));
                return false;
            }
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $this->password_reset_token]);

        Yii::info("Sending password reset email to company email: {$this->company_email}");
        
        return Yii::$app->brevoMailer->sendPasswordResetEmail($this->company_email, $this->username, $resetLink);
    }

    //email verification

    public function generateVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
        return $this->verification_token;
    }
    
    public static function findByVerificationToken($token, $companyEmail)
    {
        if (empty($token) || empty($companyEmail)) {
            return null;
        }

        $user = static::findOne([
            'verification_token' => $token,
            'company_email' => $companyEmail,
            'status' => self::STATUS_UNVERIFIED, // Check for unverified status
        ]);

        return $user;
    }
    

    
    
    
    
public function verify($token, $companyEmail)
{
    Yii::info("Verifying user: ID = {$this->id}, Email = {$this->email}, Company Email = {$companyEmail}, Current Status = {$this->status}");

    $user = self::findByVerificationToken($token, $companyEmail);
    
    if ($user === null) {
        Yii::error("Invalid or expired token for user ID {$this->id} with company email {$companyEmail}");
        return false;
    }

    // Proceed with verification
    $this->status = self::STATUS_ACTIVE;
    $this->verification_token = null;
    $this->verification_token_created_at = null;
    $this->verified_at = new Expression('NOW()');

    if ($this->save()) {
        Yii::info("User verified successfully: ID = {$this->id}");
        return true;
    }

    Yii::error("Failed to verify user: ID = {$this->id}. Errors: " . json_encode($this->getErrors()));
    return false;
}

    public static function isValidTokenFormat($token)
{
    // Adjust this regex pattern based on your token format
    return preg_match('/^[A-Za-z0-9_-]{32,64}$/', $token) === 1;
}

    

        
    
    public function removeVerificationToken()
    {
        Yii::info("Removing verification token for user ID: {$this->id}");
        $this->verification_token = null;
        // If you have a verification_token_created_at field, reset it here as well
        // $this->verification_token_created_at = null;
    }

    public static function isValidToken($token)
{
    if (empty($token)) {
        return false;
    }

    $parts = explode('_', $token);
    $timestamp = (int) end($parts);

    // Check if the token has expired (e.g., after 24 hours)
    $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600;
    return $timestamp + $expire >= time();
}
    
    
    public function sendPasswordResetEmail()
    {
        if (!$this->password_reset_token) {
            Yii::error("Attempted to send password reset email without a token for user: {$this->id}");
            return false;
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $this->password_reset_token]);

        Yii::info("Sending password reset email to user: {$this->id} with token: {$this->password_reset_token}");

        $supportEmail = Yii::$app->params['supportEmail'] ?? 'support@example.com';
        $senderEmail = Yii::$app->params['senderEmail'] ?? $supportEmail;
        $senderName = Yii::$app->params['senderName'] ?? Yii::$app->name . ' robot';

        return Yii::$app->mailer->compose(
            ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
            ['user' => $this, 'resetLink' => $resetLink]
        )
            ->setFrom([$senderEmail => $senderName])
            ->setTo($this->company_email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }

    private function getPasswordResetEmailContent($resetLink)
    {
        return "
            <html>
            <body>
                <h2>Password Reset for " . Yii::$app->name . "</h2>
                <p>Hello {$this->username},</p>
                <p>Follow the link below to reset your password:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>If you didn't request this, you can ignore this email.</p>
            </body>
            </html>
        ";
    }

    public function resetPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->password_reset_token = null;
        $this->password_reset_token_created_at = null;
        return $this->save(false);
    }

    // ... other methods ...

    public function addComment($ticketId, $comment)
    {
        $ticket = Ticket::findOne($ticketId);
        if ($ticket && $ticket->assigned_to == $this->id) {
            $ticket->comments .= "\n" . date('Y-m-d H:i:s') . " - " . $this->username . ": " . $comment;
            return $ticket->save();
        }
        return false;
    }



    // public function isAdmin()
    // {
    //     return $this->getRole() === 'admin';
    // }

//     public function isDeveloper()
//     {
//         return $this->getRole() === 'developer';
//     }
 

    public function validateClientEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!Client::emailExists($this->$attribute)) {
                $this->addError($attribute, 'Only registered customers are allowed to sign up.');
            }
        }
    }

    public function isClient()
    {
        return Client::find()->where(['company_email' => $this->company_email])->exists();
    }

    public function getRoleName()
    {
        return $this->role;
    }

     
  }







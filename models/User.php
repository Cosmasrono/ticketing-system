<?php

namespace app\models;
use yii\db\Expression;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Admin;
use app\models\Client;
use app\models\Company;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord implements IdentityInterface
{
    public $is_verified;
    public $first_login;
    // created at
    public $created_at;
    // updated at
    public $updated_at;
    // token created at
    public $token_created_at_unix;

    public $password; // This will hold the plain text password temporarily
    public $module;
    public $issue;
    public $description;
    public $temporary_password;
    public $new_password;
    public $confirm_password;
    public $password_repeat;
    public $start_date;
    public $end_date;
    public $modules;
    // public $email_verified_at;
    public $company_email;
    public $isExpired; // Add this line to define the property

    // Add these properties to your User model
    public $email_verified = false;
    public $verification_token;

    // Add this property
    public $company_type;

    // Add this property
    public $subscription_level;

    const SCENARIO_SIGNUP = 'signup';

    // Define status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 9;
    const STATUS_UNVERIFIED = 20;

    public $password_reset_token;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SIGNUP] = ['company_name', 'company_email', 'password', 'role'];
        return $scenarios;
    }

    // Role constants// Replace role constants
const ROLE_USER = 2;
const ROLE_ADMIN = 1; 
const ROLE_DEVELOPER = 3;
const ROLE_SUPER_ADMIN = 4;

// Add role check method
public function isUser()
{
    return (int)$this->role === self::ROLE_USER;
}

// Check for admin/super_admin
// public function isAdminOrSuper() 
// {
//     return in_array((int)$this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
// }

    // Status constants
    

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at_unix',
                'updatedAtAttribute' => 'updated_at_unix',
                'value' => time(),
            ],
        ];
    }
    
    // Then your beforeSave would only need to handle the other logic

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'password_hash', 'auth_key', 'role', 'status', 'company_id'], 'required'],
            [['company_email'], 'email', 'skipOnEmpty' => true],
            [['company_email'], 'unique', 'skipOnEmpty' => true],
            [['company_email'], 'default', 'value' => null], // Set default value to null
            [['role', 'status', 'company_id', 'created_at_unix', 'updated_at_unix'], 'integer'],
            [['name', 'company_name', 'company_email', 'password_hash', 'auth_key'], 'string', 'max' => 255],
            [['verification_token'], 'string', 'max' => 255], // Allow null
            [['email_verified'], 'boolean'],
            [['password_reset_token', 'token_created_at'], 'safe'],
            [['email_verified'], 'default', 'value' => false], // Set default value to false
            [['is_verified', 'first_login'], 'integer'], // Changed from boolean to integer
            [['modules'], 'safe'],
            [['company_type'], 'string'], // Add this rule
            [['company_type'], 'default', 'value' => null], // Sets default value to null
            [['subscription_level'], 'string'], // Add this rule
            [['subscription_level'], 'default', 'value' => null], // Sets default value to null
            // subscription_id
            // [['created_at', 'updated_at'], 'safe'],
            [['company_id'], 'integer'],
            [['created_at_unix', 'updated_at_unix'], 'safe'],
            ['password_reset_token', 'string'],
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
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'role' => 'Role',
            'status' => 'Status',
            'company_id' => 'Company ID',
            'created_at_unix'=>'created_at_unix',
            'updated_at_unix'=>'updated_at_unix',
            'is_verified' => 'Is Verified',
            'first_login' => 'First Login',
            'verification_token' => 'Verification Token',
            'modules' => 'Modules',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyName()
    {
        return $this->requestedBy->company_name; // Assuming 'requestedBy' is linked to User with 'company_name'
    }
    

     public function isSuperAdmin()
     {
         return $this->role === 'super_admin';
     }

     public function beforeSave($insert)
     {
         if (!parent::beforeSave($insert)) {
             return false;
         }
     
         if ($this->isNewRecord) {
             $this->auth_key = Yii::$app->security->generateRandomString();
             $this->created_at_unix = time(); // Set current Unix timestamp
             $this->updated_at_unix = time(); // Set current Unix timestamp
             $this->is_verified = false;
             $this->first_login = true;
             $this->email_verified = false;
             
             if (!empty($this->company_email)) {
                 $this->verification_token = Yii::$app->security->generateRandomString(32);
             } else {
                 $this->verification_token = null;
             }
         } else {
             // For updates, set updated_at_unix to the current Unix timestamp
             $this->updated_at_unix = time();
         }
         
         return true;
     }

     
    // Add this method to handle datetime conversion when retrieving data
    public function afterFind()
    {
        parent::afterFind();
        
        // Convert Unix timestamp to datetime if needed
        if ($this->created_at_unix) {
            $this->created_at = date('Y-m-d H:i:s', $this->created_at_unix);
        }
        if ($this->updated_at_unix) {
            $this->updated_at = date('Y-m-d H:i:s', $this->updated_at_unix);
        }
    }
 
    //  public function afterSave($insert, $changedAttributes)
    //  {
    //      parent::afterSave($insert, $changedAttributes);
 
    //      // Log the changes made to the model
    //      Yii::info("User model saved. Changed attributes: " . json_encode($changedAttributes));
 
    //      if ($insert) {
    //          // Check if the company email exists in the client table
    //          $isClient = Client::find()->where(['company_email' => $this->company_email])->exists();
 
    //          // Determine the role based on whether the email exists in the client table
    //          $this->role = $isClient ? 'admin' : 'user';
 
    //          // Save the role
    //          $this->save(false);
    //      }
    //  }
     
    public static function findIdentity($id)
    {
        $user = static::findOne($id);
        if ($user) {
            // Ensure the user has a role
            $auth = Yii::$app->authManager;
            $roles = $auth->getRolesByUser($id);
            if (empty($roles)) {
                // If no role is assigned, assign the 'user' role by default
                $userRole = $auth->getRole('user');
                if ($userRole) {
                    $auth->assign($userRole, $id);
                }
            }
        }
        return $user;
    }

    public function assignRole()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        if (empty($roles)) {
            $role = $auth->getRole('user');
            if ($role === null) {
                Yii::error("The 'user' role does not exist in the RBAC system.");
                return;
            }
            $auth->assign($role, $this->id);
        }
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
  
    public function getTickets(): \yii\db\ActiveQuery
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
        return $this->id;
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
        Yii::debug("Attempting to validate password");
        Yii::debug("Stored hash: " . $this->password_hash);
        
        try {
            $result = Yii::$app->security->validatePassword($password, $this->password_hash);
            Yii::debug("Password validation result: " . ($result ? 'true' : 'false'));
            return $result;
        } catch (\Exception $e) {
            Yii::error("Password validation error: " . $e->getMessage());
            return false;
        }
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
        return [
            'id',
            'company_id',
            'name',
            'company_name',
            'company_email',
            'password_hash',
            'auth_key',
            'verification_token',
            'email_verified',
            'role',
            'status',
            'company_id',
            'created_at_unix',
            'updated_at_unix',
            'token_created_at_unix',
            'modules',
            'is_verified',
            'first_login',
            'subscription_level'
        ];
    }

    public static function findByEmail($email)
    {
        // Add debug logging
        Yii::debug("Looking for user with email: " . $email);
        $user = static::findOne(['company_email' => $email]);
        Yii::debug("User found: " . ($user ? 'yes' : 'no'));
        return $user;
    }

    public function getAssignedTickets()
    {
        return $this->hasMany(Ticket::class, ['assigned_to' => 'id']);
    }

    // public function isAdmin()
    // {
    //     return $this->role === self::ROLE_SUPER_ADMIN;
    // }

    public function getCompanyEmail()
    {
        return $this->company_email;
    }

    /**
     * Checks if the user is an admin.
     * @return bool whether the user is an admin
     */
    // public function getIsAdmin()
    // {
    //     return $this->role === 'admin';
    // }
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            Yii::debug("Token is empty");
            return false;
        }
    
        // Add debug logging
        Yii::debug("Checking token validity: " . $token);
        
        $user = static::findOne([
            'password_reset_token' => $token,
        ]);
    
        if (!$user) {
            Yii::debug("No user found with token: " . $token);
            return false;
        }
    
        Yii::debug("User found with status: " . $user->status);
        Yii::debug("Token created at: " . $user->token_created_at_unix);
        
        // If you have token expiration logic:
        $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600; // 1 hour default
        $isValid = $user->token_created_at_unix + $expire >= time();
        
        Yii::debug("Token is " . ($isValid ? "valid" : "expired"));
        
        return $isValid;
    }
    
    // public static function findByPasswordResetToken($token)
    // {
    //     if (!static::isPasswordResetTokenValid($token)) {
    //         Yii::error('Token validation failed: ' . $token);
    //         return null;
    //     }
    
    //     $user = static::findOne([
    //         'password_reset_token' => $token,
    //         'status' => [self::STATUS_ACTIVE, self::STATUS_UNVERIFIED],
    //     ]);
    
    //     if (!$user) {
    //         Yii::debug("User not found or has invalid status");
    //     } else {
    //         Yii::debug("User found with status: " . $user->status);
    //     }
    
    //     return $user;
    // }
    
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
        return $this->save(false);
    }


    
    public function generatePasswordResetToken()
    {
        $randomStr = Yii::$app->security->generateRandomString(32);
        $timestamp = time();
        $this->password_reset_token = $randomStr . '_' . $timestamp;
        Yii::debug("Generated new reset token: " . $this->password_reset_token);
        return $this->password_reset_token;
    }

    // public function removePasswordResetToken()
    // {
    //     $this->password_reset_token = null;
    // }

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
        if (empty($this->company_email)) {
            $this->verification_token = null;
            return null;
        }
        
        $this->verification_token = Yii::$app->security->generateRandomString(32);
        return $this->verification_token;
    }
    
    public static function findByVerificationToken($token)
    {
        if (empty($token)) {
            Yii::error('Empty verification token provided');
            return null;
        }

        Yii::debug("Looking for user with verification token: $token");
        
        $user = static::find()
            ->where(['verification_token' => $token])
            ->andWhere(['IS NOT', 'company_email', null])
            ->andWhere(['status' => self::STATUS_INACTIVE])
            ->one();

        if (!$user) {
            Yii::error("No user found with verification token: $token");
        } else {
            Yii::debug("Found user with ID: {$user->id} and email: {$user->company_email}");
        }

        return $user;
    }
    

    
    
    
    
public function verify()
{
    if (empty($this->company_email)) {
        Yii::error("Cannot verify user without email address");
        return false;
    }

    if ($this->email_verified) {
        Yii::debug("User {$this->id} is already verified");
        return true;
    }

    $this->email_verified = true;
    $this->status = self::STATUS_ACTIVE;

    if ($this->save(false)) {
        Yii::debug("User {$this->id} verified successfully");
        return true;
    }

    Yii::error("Failed to verify user {$this->id}: " . json_encode($this->errors));
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
        $this->password_reset_token_created_at_unix = null;
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
        return $this->role === self::ROLE_ADMIN ? 'Admin' : 'User';
    }

    public function getRole()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return !empty($roles) ? reset($roles)->name : null;
    }

    public function can($action)
    {
        switch ($this->getRole()) {
            case 'admin':
                return true; // Admins can do everything
            case 'developer':
                return in_array($action, ['viewTicket', 'updateTicket', 'closeTicket']);
            case 'user':
                return in_array($action, ['createTicket', 'viewOwnTicket']);
            default:
                return false;
        }
    }

    public function getModule()
    {
        return $this->invitation ? $this->invitation->module : '';
    }

    public function getName()
    {
        // Adjust this based on how you store the user's name
        return $this->username; // or $this->first_name . ' ' . $this->last_name;
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['company_name' => 'company_name']); // Adjust according to your foreign key
    }

    // public function getCompanyName()
    // {
    //     return $this->company_name ?: 'Unknown Company';
    // }

    /**
     * Check if user is super admin
     */
    // public function isSuperAdmin()
    // {
    //     return $this->role === 'superadmin';
    // }

    /**
     * Check if user is admin or super admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is administrator (alias for isAdmin)
     */
    public function isAdministrator()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access tickets
     */
    public function canAccessTickets()
    {
        return !$this->isAdmin();
    }

    public static function isAllowedEmail($email)
    {
        return static::find()->where(['company_email' => $email])->exists() || $email === 'ccosmas001@gmail.com';
    }

    public static function getDevelopers()
    {
        $developers = self::find()
            ->select(['id', 'name']) // Make sure to select the fields you need
            ->where(['role' => 'developer'])
            ->all();
        
        // Convert to id => name array format for dropdown
        return \yii\helpers\ArrayHelper::map($developers, 'id', 'name');
    }

    // /**
    //  * Check if user is a developer
    //  */
    // public function getIsDeveloper()
    // {
    //     return $this->role === self::ROLE_DEVELOPER;
    // }

    // /**
    //  * Check if user is an admin
    //  */
    // public function getIsAdmin()
    // {
    //     return $this->role === self::ROLE_ADMIN;
    // }

    public function isSpecialAdmin()
    {
        return $this->company_email === 'ccosmas001@gmail.com';
    }

    public function canAccessAdmin()
    {
        return $this->isSpecialAdmin() || $this->role === 'superadmin';
    }

    /**
     * Gets the list of modules this user has access to
     * @return array|string Array of module names or 'All' for full access
     */
    public function getAccessModules()
    {
        return $this->module === null || $this->module === '' 
            ? [] 
            : (
                $this->module === 'All' 
                ? 'All' 
                : explode(',', $this->module)
            );
    }

    /**
     * Checks if user has access to a specific module
     * @param string $module The module name to check
     * @return boolean Whether user has access to the module
     */
    public function hasModuleAccess($module)
    {
        if ($this->module === null || $this->module === '') {
            return false; // No modules assigned
        }
        
        return $this->module === 'All' || in_array($module, $this->getAccessModules());
    }

    // Example usage in your views or controllers:
    // if ($user->hasModuleAccess('HR')) {
    //     // Show HR module content
    // }

    public function beforeLogin($event)
    {
        if ($this->status === 'inactive') {
            $event->isValid = false;
            Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact support.');
        }
        return parent::beforeLogin($event);
    }

    public function setCompanyName($value)
    {
        echo "Setting company name to: " . $value . "<br>";
        $this->company_name = $value;
    }

    public function isAdminOrSuper()
    {
        return in_array((int)$this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function getIsAdmin()
    {
        return $this->role === 'admin';
    }

    public function getIsSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    /**
     * Get list of roles for dropdown
     */
    public static function getRoleList()
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_USER => 'User',
        ];
    }

    public function validatePasswordResetToken($token)
    {
        if (empty($token) || $token !== $this->password_reset_token) {
            return false;
        }

        $timestamp = (int) substr($this->password_reset_token, strrpos($this->password_reset_token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        
        return $timestamp + $expire >= time();
    }

    // Add this relation method
    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    // Add this method to see all available attributes
    public function safeAttributes()
    {
        return [
            'company_id',
            'name',
            'company_name',
            'company_email',  // Explicitly include company_email
            'password_hash',
            'auth_key',
            'role',
            'status',
            'verification_token',
            'created_at_unix',
            'updated_at_unix',
            'token_created_at_unix',
            'modules',
            'is_verified',
            'first_login',
            'subscription_level'
        ];
    }

    // Add a method to check if email verification is needed
    public function needsEmailVerification()
    {
        return !empty($this->company_email) && !$this->email_verified;
    }

     

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => [self::STATUS_UNVERIFIED, self::STATUS_ACTIVE]
        ]);
    }



    public static function isPasswordResetTokenExpired($token)
    {
        if (empty($token)) {
            Yii::error('Empty token provided for expiration check');
            return true;
        }

        $parts = explode('_', $token);
        if (count($parts) !== 2) {
            Yii::error('Token format invalid: ' . $token);
            return true;
        }

        $timestamp = (int) $parts[1];
        if (!$timestamp) {
            Yii::error('Invalid timestamp in token: ' . $token);
            return true;
        }

        $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 86400;
        $isExpired = $timestamp + $expire < time();
        
        Yii::debug("Token check - Timestamp: $timestamp, Expire: $expire, Current: " . time() . ", IsExpired: " . ($isExpired ? 'yes' : 'no'));
        
        return $isExpired;
    }

}
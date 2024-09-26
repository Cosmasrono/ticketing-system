<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Admin;

class User extends ActiveRecord implements IdentityInterface
{

    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_DEVELOPER = 'developer'; // Add this line

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
            [['company_email'], 'required'],
            [['company_email'], 'email'],
            [['company_email'], 'unique'],
            [['password_hash', 'auth_key'], 'string'],
            ['auth_key', 'string', 'max' => 32],
            ['role', 'safe'],
        ['role', 'in', 'range' => [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_DEVELOPER]],

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
        ];
    }

    /**
     * {@inheritdoc}
     */
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

    public static function findByCompanyEmail($email)
    {
        return static::findOne(['company_email' => $email]);
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
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

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
        return static::findOne(['company_email' => $email]);
    }

    public function getAssignedTickets()
    {
        return $this->hasMany(Ticket::class, ['assigned_to' => 'id']);
    }

    public function isAdmin()
    {
        // Adjust this condition based on how you determine if a user is an admin
        return $this->role === 'admin';
    }

    public function getCompanyEmail()
    {
        return $this->company_email;
    }

}
<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Company extends ActiveRecord
{
    // Define role constants as strings to match database values
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_SUPER_ADMIN = 'super_admin';

    public $role;
    public $name;

    public static function tableName()
    {
        return '{{%company}}';
    }

    public function rules()
    {
        return [
            [['name', 'company_name', 'company_email', 'company_type', 'subscription_level'], 'required'],
            [['name', 'company_name', 'company_email'], 'string', 'max' => 255],
            [['company_email'], 'email'],
            [['start_date', 'end_date'], 'safe'],
            [['modules'], 'safe'],
            [['status'], 'integer'],
            [['company_type', 'subscription_level'], 'string'],
            ['company_name', 'unique', 'targetClass' => self::class, 'message' => 'This Company Name has already been taken.'],
            ['company_email', 'unique', 'targetClass' => self::class, 'message' => 'This Company Email has already been taken.'],
            ['role', 'string'],
        ];
    }

    public function validateDateRange($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $start = strtotime($this->start_date);
            $end = strtotime($this->end_date);
            
            if ($end <= $start) {
                $this->addError($attribute, 'End date must be after start date');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'company_name' => 'Name',
            'company_email' => 'Email',
            'company_type' => 'Company Type',
            'subscription_level' => 'Subscription Level',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'modules' => 'Modules',
            'status' => 'Status',
            'role' => 'User Type',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Copy name to company_name if name is set
        if (isset($this->name)) {
            $this->company_name = $this->name;
        }

        // Ensure dates are in the correct format
        if ($this->start_date) {
            $this->start_date = date('Y-m-d', strtotime($this->start_date));
        }
        if ($this->end_date) {
            $this->end_date = date('Y-m-d', strtotime($this->end_date));
        }

        // Convert modules array to string before saving
        if (is_array($this->modules)) {
            $this->modules = implode(',', $this->modules);
        }

        return true;
    }


    public function getCompanyName()
{
    return $this->requestedBy->company->name; // Assuming 'company' is a relation in User model
}

    public function afterFind()
    {
        parent::afterFind();
        // Convert modules string back to array after fetching
        if (is_string($this->modules) && !empty($this->modules)) {
            $this->modules = explode(',', $this->modules);
        } elseif ($this->modules === null) {
            $this->modules = [];
        }
    }

    public function getName()
    {
        return $this->company_name;
    }

    public function setName($value)
    {
        $this->company_name = $value;
    }

    public function attributes()
    {
        // Remove virtual attributes from the list of attributes that will be saved
        $attributes = parent::attributes();
        return array_diff($attributes, ['name', 'role']);
    }

    // Define the relation to the roles if they are stored in a separate table
    public function getRoles()
    {
        return $this->hasMany(Role::class, ['company_id' => 'id']);
    }

    // Get the role value and map it to a label
    public function getRoleLabel()
    {
        $role = strtolower($this->role); // Convert to lowercase for consistent comparison
        
        switch($role) {
            case self::ROLE_ADMIN:
                return 'Admin';
            case self::ROLE_USER:
                return 'User';
            case self::ROLE_DEVELOPER:
                return 'Developer';
            case self::ROLE_SUPER_ADMIN:
                return 'Super Admin';
            default:
                return 'Unknown Role (' . $this->role . ')';
        }
    }

    // Get roles array (for backward compatibility)
    public function getRolesArray()
    {
        return [$this->role];
    }

    // Add relation to User model
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_name' => 'company_name']);
    }

    // Helper method to check if company has active users
    public function hasActiveUser()
    {
        return $this->getUsers()
            ->andWhere(['status' => 10]) // STATUS_ACTIVE = 10
            ->exists();
    }
}

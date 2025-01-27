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
        return 'company';
    }

    public function rules()
    {
        return [
            [['name', 'company_name', 'company_email'], 'required'],
            [['name', 'company_name', 'company_email'], 'string', 'max' => 255],
            [['company_email'], 'email'],
            [['start_date', 'end_date'], 'safe'],
            [['modules'], 'safe'],
            [['status'], 'integer'],
            [['role'], 'in', 'range' => ['user', 'admin', 'developer', 'super_admin']],
            [['company_type', 'subscription_level'], 'string'],
            ['company_email', 'unique', 'targetClass' => self::class, 'message' => 'This Company Email has already been taken.'],
            [['name'], 'string', 'max' => 255],
            [['start_date', 'end_date'], 'required'],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['end_date'], 'safe'],  // Make sure end_date is allowed to be set
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
            'company_name' => 'Company Name',
            'company_email' => 'Email',
            'company_type' => 'Company Type',
            'subscription_level' => 'Subscription Level',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'modules' => 'Modules',
            'status' => 'Status',
            'role' => 'User Type',
            'name' => 'Name',
        ];
    }

    public function beforeSave($insert)
    {
        Yii::debug('beforeSave - company_name before: ' . $this->company_name);
        
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Ensure dates are in the correct format
        if ($this->start_date) {
            $this->start_date = date('Y-m-d', strtotime($this->start_date));
        }
        if ($this->end_date) {
            $date = new \DateTime($this->end_date);
            $this->end_date = $date->format('Y-m-d');
        }

        // Convert modules array to string before saving
        if (is_array($this->modules)) {
            $this->modules = implode(',', $this->modules);
        }

        Yii::debug('beforeSave - company_name after: ' . $this->company_name);
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::debug('afterSave - company_name: ' . $this->company_name);
        parent::afterSave($insert, $changedAttributes);
    }

    // Add getter and setter for end_date to ensure proper formatting
    public function getEndDate()
    {
        return $this->end_date;
    }

    public function setEndDate($value)
    {
        if ($value) {
            $date = new \DateTime($value);
            $this->end_date = $date->format('Y-m-d');
        } else {
            $this->end_date = null;
        }
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

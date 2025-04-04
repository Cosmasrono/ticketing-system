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
            [['start_date', 'end_date'], 'safe'],
            [['status'], 'integer'],
            [['modules'], 'safe'],
            [['name', 'company_name', 'company_email'], 'string', 'max' => 255],
            [['role'], 'string', 'max' => 50],
            [['company_type', 'subscription_level'], 'string'],
            ['company_email', 'email'],
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
            'id' => 'ID',
            'name' => 'Name',
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'role' => 'Role',
            'status' => 'Status',
            'modules' => 'Modules',
            'company_type' => 'Company Type',
            'subscription_level' => 'Subscription Level',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'value' => new \yii\db\Expression('GETDATE()'),  // Use GETDATE() for SQL Server
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = date('Y-m-d H:i:s');
                $this->updated_at = date('Y-m-d H:i:s');
                $this->start_date = date('Y-m-d');
                $this->end_date = date('Y-m-d', strtotime('+1 year'));
            } else {
                $this->updated_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
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
        // Log the role value for debugging
        Yii::info('Role value: ' . json_encode($this->role)); // Log the role value

        // Return the role in lowercase, defaulting to an empty string if null
        return strtolower($this->role ?? ''); 
    }

    // Get roles array (for backward compatibility)
    public function getRolesArray()
    {
        return [$this->role];
    }

    // Add relation to User model
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }

    // Helper method to check if company has active users
    public function hasActiveUser()
    {
        return $this->getUsers()
            ->andWhere(['status' => 10]) // STATUS_ACTIVE = 10
            ->exists();
    }
}

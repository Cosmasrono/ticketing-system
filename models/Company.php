<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Company extends ActiveRecord
{
    // Define role constants as strings to match database values
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_SUPER_ADMIN = 'super_admin';

    public $role;
    public $id; // Assuming you have an id column
    public $name; // Assuming you have a name column
    public $company_name; // Assuming you have a company_name column
    public $company_email; // Assuming you have a company_email column
    public $company_type; // Assuming you have a company_type column
    public $subscription_level; // Assuming you have a subscription_level column
    public $modules; // Assuming you have a modules column
    public $created_at; // Assuming you have a created_at column
    public $updated_at; // Assuming you have an updated_at column
    public $status; // Assuming you have a status column
    public $start_date; // New property
    public $end_date; // New property
    public $renewed_at; // New property

    public static function tableName()
    {
        return 'company';
    }

    public function rules()
    {
        return [
            [['company_name', 'company_email'], 'required'],
            [['company_name', 'company_email'], 'string', 'max' => 255],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string'],
            [['company_email'], 'email'],
            [['company_name'], 'unique'],
            [['company_email'], 'unique'],
            
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
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Convert dates to database format
            if ($this->start_date) {
                $this->start_date = Yii::$app->formatter->asDate($this->start_date, 'php:Y-m-d');
            }
            if ($this->end_date) {
                $this->end_date = Yii::$app->formatter->asDate($this->end_date, 'php:Y-m-d');
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

    /**
     * Gets query for [[ContractRenewals]]
     */
    public function getContractRenewals()
    {
        return $this->hasMany(ContractRenewal::class, ['company_id' => 'id']);
    }

    /**
     * Gets the latest contract renewal
     */
    public function getLatestRenewal()
    {
        return $this->getContractRenewals()
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
    }

    /**
     * Check if company has active contract
     */
    public function getIsActive()
    {
        if (empty($this->end_date)) {
            return false;
        }
        return strtotime($this->end_date) >= strtotime(date('Y-m-d'));
    }

    /**
     * Get formatted status
     */
    public function getStatusText()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Get days remaining in contract
     */
    public function getDaysRemaining()
    {
        if (empty($this->end_date)) {
            return 0;
        }
        $end = strtotime($this->end_date);
        $now = strtotime(date('Y-m-d'));
        $diff = $end - $now;
        return max(0, round($diff / (60 * 60 * 24)));
    }
}

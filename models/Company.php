<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Company extends ActiveRecord
{
    public $role;

    public static function tableName()
    {
        return '{{%company}}';
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
            'name' => 'Contact Person Name',
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
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
}

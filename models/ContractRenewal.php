<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ContractRenewal extends ActiveRecord
{
    public static function tableName()
    {
        return 'contract_renewal';
    }

    public function rules()
    {
        return [
            [['company_id', 'requested_by', 'extension_period', 'current_end_date'], 'required'],
            [['company_id', 'requested_by', 'extension_period'], 'integer'],
            ['notes', 'string'],
            ['renewal_status', 'in', 'range' => ['pending', 'approved', 'rejected']],
            [['current_end_date', 'new_end_date', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Set current_end_date from company if not set
        if ($insert && empty($this->current_end_date)) {
            $company = Company::findOne($this->company_id);
            if ($company) {
                $this->current_end_date = $company->end_date;
            }
        }

        // Calculate new_end_date
        if ($insert && $this->extension_period) {
            $currentEndDate = strtotime($this->current_end_date);
            $newEndDate = strtotime("+{$this->extension_period} months", $currentEndDate);
            $this->new_end_date = date('Y-m-d', $newEndDate);
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company',
            'requested_by' => 'Requested By',
            'extension_period' => 'Extension Period',
            'notes' => 'Notes',
            'renewal_status' => 'Status',
            'current_end_date' => 'Current End Date',
            'new_end_date' => 'New End Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    // Relations
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getRequestedBy()
    {
        return $this->hasOne(User::class, ['id' => 'requested_by']);
    }

    // Helper method to get extension period options
    public static function getExtensionPeriodOptions()
    {
        return [
            3 => '3 Months',
            6 => '6 Months',
            12 => '1 Year',
            24 => '2 Years',
        ];
    }

    // Helper method to get status labels with colors
    public static function getStatusLabels()
    {
        return [
            'pending' => ['label' => 'Pending', 'class' => 'badge bg-warning'],
            'approved' => ['label' => 'Approved', 'class' => 'badge bg-success'],
            'rejected' => ['label' => 'Rejected', 'class' => 'badge bg-danger'],
        ];
    }

    // Get formatted status label with HTML
    public function getStatusLabel()
    {
        $statuses = self::getStatusLabels();
        if (isset($statuses[$this->renewal_status])) {
            return "<span class='{$statuses[$this->renewal_status]['class']}'>{$statuses[$this->renewal_status]['label']}</span>";
        }
        return $this->renewal_status;
    }
} 
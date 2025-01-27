<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Company;
use app\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class ContractRenewal extends ActiveRecord
{
    public static function tableName()
    {
        return 'contract_renewal'; // Changed to contract_renewal table
    }

    public function rules()
    {
        return [
            [['company_id', 'requested_by', 'extension_period', 'current_end_date'], 'required'],
            [['company_id', 'requested_by', 'extension_period'], 'integer'],
            ['notes', 'string'],
            ['renewal_status', 'in', 'range' => ['pending', 'approved', 'rejected']],
            [['current_end_date', 'new_end_date', 'created_at', 'updated_at'], 'safe'],
            ['renewal_status', 'default', 'value' => 'pending'],
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
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Ensure dates are in the correct format
        if ($this->current_end_date) {
            $date = new \DateTime($this->current_end_date);
            $this->current_end_date = $date->format('Y-m-d');
        }

        if ($this->new_end_date) {
            $date = new \DateTime($this->new_end_date);
            $this->new_end_date = $date->format('Y-m-d');
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

        // If renewal_status is being changed to 'approved'
        if (!$insert && $this->isAttributeChanged('renewal_status') && $this->renewal_status === 'approved') {
            // Update company end_date
            $company = Company::findOne($this->company_id);
            if ($company) {
                $company->end_date = $this->new_end_date;
                if (!$company->save()) {
                    Yii::$app->session->setFlash('error', 'Failed to update company end date.');
                    return false;
                }
                
                // Log the contract renewal
                Yii::info("Contract renewed for company {$company->company_name}. New end date: {$this->new_end_date}", 'contract');
                
                // Send notification email
                $this->sendRenewalNotification($company);
            }
        }

        return true;
    }

    /**
     * Send notification email about contract renewal
     */
    public function sendRenewalNotification($company)
    {
        try {
            // Find company admin
            $companyAdmin = User::find()
                ->where(['company_name' => $company->company_name])
                ->andWhere(['role' => '2']) // Assuming 2 is company admin role
                ->one();

            if ($companyAdmin && $companyAdmin->company_email) {
                return Yii::$app->mailer->compose('contractRenewal', [
                    'company' => $company,
                    'renewal' => $this,
                    'admin' => $companyAdmin
                ])
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                ->setTo($companyAdmin->company_email)
                ->setSubject('Contract Renewal ' . ucfirst($this->renewal_status))
                ->send();
            }
            return false;
        } catch (\Exception $e) {
            Yii::error("Failed to send renewal notification: {$e->getMessage()}", 'contract');
            return false;
        }
    }

    /**
     * Update renewal status and company end date
     */
    public function updateStatus($status)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->renewal_status = $status;
            if ($this->save()) {
                $transaction->commit();
                return true;
            }
            $transaction->rollBack();
            return false;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Failed to update renewal status: {$e->getMessage()}", 'contract');
            return false;
        }
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
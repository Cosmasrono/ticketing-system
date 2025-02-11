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
            [['company_id', 'current_end_date', 'extension_period', 'renewal_duration', 'requested_by'], 'required'],
            [['company_id', 'renewal_duration', 'requested_by'], 'integer'],
            [['current_end_date', 'extension_period'], 'safe'],
            [['notes'], 'string'],
            [['renewal_status'], 'string'],
            [['renewal_status'], 'default', 'value' => 'pending'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],

        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company',
            'renewal_duration' => 'Duration (Months)',
            'requested_by' => 'Requested By',
            'extension_period' => 'Extension Period',
            'notes' => 'Notes',
            'renewal_status' => 'Status',
            'current_end_date' => 'Current End Date',
            'new_end_date' => 'New End Date',
            'renewed_at' => 'Renewed At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets the related Company record
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets the related User record who requested the renewal
     */
    public function getRequestedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'requested_by']);
    }

    /**
     * Gets the requester's name
     */
    public function getRequesterName()
    {
        return $this->requestedByUser ? $this->requestedByUser->name : 'Unknown';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->renewal_status = 'pending';
            }
            if ($this->isNewRecord) {
                $this->created_at = date('Y-m-d H:i:s');
                $this->requested_by = Yii::$app->user->id;
            }
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
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
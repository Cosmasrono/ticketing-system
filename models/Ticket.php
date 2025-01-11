<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use \DateTime;  // Add this import
use \DateTimeZone;  // Add this import too
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use app\models\TicketEscalation; // Ensure this import is present

class Ticket extends ActiveRecord
{

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_CLOSED = 'closed';
    const STATUS_ESCALATED = 'escalated';
    const STATUS_REOPEN = 'reopen';
    const STATUS_DELETED = 'deleted';
    const STATUS_REASSIGNED = 'reassigned';

    const SLA_STATUS_WITHIN = 'within';
    const SLA_STATUS_AT_RISK = 'at_risk';
    const SLA_STATUS_BREACHED = 'breached';

    const SEVERITY_CRITICAL = 1;
    const SEVERITY_HIGH = 2;
    const SEVERITY_MEDIUM = 3;
    const SEVERITY_LOW = 4;

    /**
     * @var UploadedFile
     */
     public $selectedModule;
    public $screenshot;
    public $uploadedFile;
    public $imageFile;
    public $screenshot_url;
    public $issue;
    public $severity;
    public $module;
    public $voice_note_url;
 
 
    public static function tableName()
    {
        return '{{%ticket}}'; // Your ticket table name
    }




    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => function() { 
                    return date('Y-m-d H:i:s');  // This will store exact current datetime
                }
            ],
        ];
    }
    public function rules()
    {
        return [
            // Remove duplicate rules
            [['module', 'issue', 'description'], 'required'],
            [['description', 'screenshot'], 'string'],
            [['created_at'], 'safe'],
            [['created_by'], 'integer'],
            [['module', 'issue', 'status'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['approved_at'], 'safe'],
            [['escalation_comment'], 'string'],
            [['assigned_to'], 'integer'],
            [['module'], 'safe'],
            
            // Escalation rule
            [['escalated_to'], 'required', 'when' => function($model) {
                return $model->status === 'reassigned';
            }, 'whenClient' => "function (attribute, value) {
                return $('#ticket-status').val() === 'reassigned';
            }"],
            
            [['status'], 'string', 'max' => 20],
            [['company_name', 'company_email', 'module', 'issue'], 'string', 'max' => 255],
            

        
        ['screenshotUrl', 'string'],
        ['screenshot', 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 5*1024*1024],
        ['screenshotUrl', 'url'],
        ['screenshotUrl', 'validateCloudinaryUrl'],
        ['severity_level', 'required'],
        ['severity_level', 'integer'],
        ['severity_level', 'in', 'range' => [1, 2, 3, 4]],
        ['sla_status', 'string'],
        [['first_response_at', 'resolution_deadline', 'last_update_at', 'next_update_due'], 'safe'],
        [['issue'], 'string'],
        [['issue'], 'required'],
        [['severity'], 'integer'],
        [['severity'], 'required'],
        [['module', 'issue'], 'required'],
        [['module', 'issue'], 'string'],
        [['renewal_status'], 'string', 'max' => 255],
        [['renewal_date'], 'datetime'],
        [['approved_by'], 'integer'],
        [['approved_at'], 'safe'],

        [['renewal_date'], 'date', 'format' => 'php:Y-m-d H:i:s'],

        [['voice_note_url'], 'string'],
        [['user_id', 'module', 'issue', 'description'], 'required'],

    ];
    }
    
    // Add this method to your model
    public function validateScreenshot($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            // More permissive regex for base64 validation
            if (!preg_match('/^data:image\/[a-zA-Z]+;base64,/', $this->$attribute)) {
                $this->addError($attribute, 'Invalid screenshot format');
                return;
            }
    
            // Size validation
            $base64 = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $this->$attribute);
            $base64 = str_replace(' ', '+', $base64);
            $imageSize = strlen(base64_decode($base64));
            
            if ($imageSize > 5 * 1024 * 1024) { // 5MB limit
                $this->addError($attribute, 'Screenshot is too large (max 5MB)');
            }
        }
    }
    

    public function attributeLabels()
    {
        return [
            'selectedModule' => 'Select Module',
            'issue' => 'Issue',
            'description' => 'Description',
            
            
            
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'escalation_comment' => 'Escalation Comment',
            'assigned_to' => 'Assigned To',
            'screenshot' => 'Screenshot',
            'severity_level' => 'Severity',
            'first_response_at' => 'First Response',
            'resolution_deadline' => 'Due By',
            'last_update_at' => 'Last Updated',
            'sla_status' => 'SLA Status',
            'next_update_due' => 'Next Update Due',
            'severity' => 'Severity',
            'module' => 'Module',
            'renewal_status'=>'renewal_status',
            'approved_by' => 'Approved By',
            'approved_at' => 'Approved At',
        ];
    }

     
    // // Custom screenshot validation method
    // public function validateScreenshot($attribute, $params)
    // {
    //     if (!empty($this->$attribute)) {
    //         // Validate base64 format
    //         if (!preg_match('/^data:image\/(png|jpe?g|gif);base64,/', $this->$attribute)) {
    //             $this->addError($attribute, 'Invalid screenshot format');
    //         }

    //         // Optional: Additional size validation
    //         $base64 = str_replace('data:image/png;base64,', '', $this->$attribute);
    //         $base64 = str_replace(' ', '+', $base64);
    //         $imageSize = strlen(base64_decode($base64));
            
    //         if ($imageSize > 5 * 1024 * 1024) { // 5MB limit
    //             $this->addError($attribute, 'Screenshot is too large');
    //         }
    //     }
    // }

    // Optional: Method to get image from base64
    public function getScreenshotImage()
    {
        return $this->screenshot ? $this->screenshot : null;
    }

    
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Log the screenshot data
        Yii::debug('Screenshot in beforeSave: ' . (empty($this->screenshot) ? 'empty' : 'has data'));
        if (!empty($this->screenshot)) {
            Yii::debug('Screenshot length: ' . strlen($this->screenshot));
            Yii::debug('Screenshot preview: ' . substr($this->screenshot, 0, 100));
        }

        if ($insert) {
            $this->calculateSlaDeadlines();
        }

        $this->updateSlaStatus();
        $this->last_update_at = new Expression('NOW()');

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Debug saved data
        Yii::debug('Ticket saved. Screenshot length in DB: ' . 
            (empty($this->screenshot) ? '0' : strlen($this->screenshot)));
        
        // Keep your existing logging
        Yii::info("afterSave called for ticket {$this->id}. Changed attributes: " . json_encode($changedAttributes));

        // Your custom logic here
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    public function getAssignedDeveloper()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }





    public function getDeveloper()
    {
        return $this->hasOne(User::class, ['id' => 'developer_id']);
    }

    public function getAssignedTo()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }


    // public function approve()
    // {
    //     $this->status = 'approved';
    //     return $this->save();
    // }

    public function cancel()
    {
        $this->status = 'cancelled';
        return $this->save();
    }
    // public function getCompany()
    // {
    //     return $this->hasOne(Company::class, ['id' => 'company_id']);
    // }

    // public static function findTicketsByCompanyEmail($email)
    // {
    //     return self::find()->where(['company_email' => $email])->all();
    // }

    public function approve()
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_at = new \yii\db\Expression('NOW()');
        $this->approved_by = Yii::$app->user->id;

        return $this->save(false);
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['assign'] = ['assigned_to', 'status'];
        return $scenarios;
    }

   
    

    public function getAssignedUser()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }

    public function getClosedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'closed_by']);
    }

    public function getRaisedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'raised_by']); // Relation to the user who raised the ticket
    }

    public function getRemainingTimeInSeconds()
    {
        // Implement your logic here
        // For example:
        $createdAt = strtotime($this->created_at);
        $now = time();
        $difference = $now - $createdAt;
        $remainingTime = 3600 - $difference; // Assuming 1 hour limit
        return max(0, $remainingTime);
    }

    public function getElapsedTime()
    {
        if ($this->timer_start === null) {
            return '0:00:00';
        }
        $now = new \DateTime();
        $start = new \DateTime($this->timer_start);
        $interval = $now->diff($start);
        return $interval->format('%H:%I:%S');
    }

    public function getTimeTaken()
    {
        if ($this->status === 'closed' && $this->created_at && $this->closed_at) {
            $createdAt = new \DateTime($this->created_at);
            $closedAt = new \DateTime($this->closed_at);
            $interval = $createdAt->diff($closedAt);
            
            $days = $interval->d;
            $hours = $interval->h;
            $minutes = $interval->i;
            
            $timeTaken = [];
            if ($days > 0) $timeTaken[] = $days . ' day' . ($days > 1 ? 's' : '');
            if ($hours > 0) $timeTaken[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) $timeTaken[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            
            return implode(', ', $timeTaken);
        } else {
            return 'Reviewing';
        }
    }

    public function getModule()
    {
        return $this->hasOne(Module::class, ['id' => 'module_id']);
    }

    public static function getStatusCounts()
    {
        $counts = static::find()
            ->select(['status', 'COUNT(*) as count'])
            ->groupBy('status')
            ->indexBy('status')
            ->column();

        $statuses = ['approved', 'assigned', 'pending', 'cancelled'];
        foreach ($statuses as $status) {
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
        }

        return $counts;
    }

    // Add other relations and methods as needed

    public function getAssignButtonText()
    {
        return $this->status === self::STATUS_ESCALATED ? 'Reassign' : 'Assign';
    }

    public function getCompanyName()
    {
        return $this->user->company_name; // Assuming there's a relation to User model
    }

    public function getCompanyEmail()
    {
        return $this->user ? $this->user->company_email : null;
    }

    public $company_name; // Add this if it's not a database field but needed in the model

    public function fields()
    {
        return array_merge(parent::fields(), [
            'company_name' => function ($model) {
                return $model->companyName;
            },
        ]);
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_ESCALATED => 'Escalated',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REOPEN => 'Reopen',
            self::STATUS_REASSIGNED => 'Reassigned',
        ];
    }

    public function attributes()
    {
        $baseAttributes = parent::attributes();
        return array_merge($baseAttributes, ['updated_at', 'assigned_to', 'screenshot', 'screenshotUrl']);
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
            return true;
        }
        return false;
    }

    private function ensureInteger($value, $allowNull = false)
    {
        if ($allowNull && ($value === null || $value === '')) {
            return null;
        }
        return $value === null || $value === '' ? time() : (int)$value;
    }

    /**
     * Escalate the ticket
     * @return boolean whether the ticket was escalated successfully
     */
    public function escalate()
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_CLOSED, self::STATUS_ESCALATED])) {
            return false;
        }

        $this->status = self::STATUS_ESCALATED;
        $this->escalated_at = new Expression('NOW()');
        
        return $this->save();
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getCreatedAtKenyan()
    {
        if ($this->created_at) {
            $date = new DateTime($this->created_at, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Africa/Nairobi'));
            return $date->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function getStatusLabel()
    {
        $labels = [
            'open' => 'Open',
            'closed' => 'Closed',
            'pending' => 'Pending',
            // Add other statuses as needed
        ];
        
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function calculateTimeTaken()
    {
        if ($this->assigned_at && $this->closed_at) {
            $assigned = new \DateTime($this->assigned_at);
            $closed = new \DateTime($this->closed_at);
            $interval = $assigned->diff($closed);
            
            // Format the time taken
            $timeTaken = '';
            if ($interval->d > 0) {
                $timeTaken .= $interval->d . ' days ';
            }
            if ($interval->h > 0) {
                $timeTaken .= $interval->h . ' hours ';
            }
            if ($interval->i > 0) {
                $timeTaken .= $interval->i . ' minutes';
            }
            
            return trim($timeTaken);
        }
        return null;
    }

    // Helper method to get resolution time
    public function getResolutionTime()
    {
        if ($this->closed_at && $this->created_at) {
            $created = new \DateTime($this->created_at);
            $closed = new \DateTime($this->closed_at);
            return $closed->diff($created)->format('%d days, %h hours, %i minutes');
        }
        return null;
    }

    // Helper method to get response time
    public function getResponseTime()
    {
        if ($this->assigned_at && $this->created_at) {
            $created = new \DateTime($this->created_at);
            $assigned = new \DateTime($this->assigned_at);
            return $assigned->diff($created)->format('%d days, %h hours, %i minutes');
        }
        return null;
    }





   

// public function validateBase64Image($attribute, $params)
// {
//     if (!empty($this->$attribute)) {
//         // Check if it's a valid base64 encoded image
//         if (!preg_match('/^data:image\/(png|jpe?g|gif);base64,/', $this->$attribute)) {
//             $this->addError($attribute, 'Invalid image format');
//         }
//     }
// }

// public function beforeSave($insert)
// {
//     if (parent::beforeSave($insert)) {
//         if ($this->uploadedFile instanceof UploadedFile) {
//             $fileName = 'ticket_' . time() . '_' . uniqid() . '.' . $this->uploadedFile->extension;
//             $uploadPath = Yii::getAlias('@webroot/uploads/screenshots/');
            
//             if (!is_dir($uploadPath)) {
//                 FileHelper::createDirectory($uploadPath, 0777, true);
//             }
            
//             if ($this->uploadedFile->saveAs($uploadPath . $fileName)) {
//                 $this->screenshot = $fileName;
//             }
//         }
//         return true;
//     }
//     return false;
// }
    public function getLocalCreatedAt()
    {
        if ($this->created_at) {
            $timestamp = strtotime($this->created_at);
            return Yii::$app->formatter->asDatetime($timestamp, 'php:Y-m-d H:i:s');
        }
        return null;
    }

    // Add getter methods for formatted dates
    public function getFormattedCreatedAt()
    {
        return Yii::$app->formatter->asDatetime($this->created_at, 'php:Y-m-d H:i:s');
    }

    public function getFormattedAssignedAt()
    {
        return $this->assigned_at ? 
            Yii::$app->formatter->asDatetime($this->assigned_at, 'php:Y-m-d H:i:s') : 
            'Not Assigned';
    }

    public function getFormattedClosedAt()
    {
        return $this->closed_at ? 
            Yii::$app->formatter->asDatetime($this->closed_at, 'php:Y-m-d H:i:s') : 
            'Not Closed';
    }

 

    public static function getModulesList()
    {
        return [
            'Power BI' => 'Power BI',
            'Members Portal' => 'Members Portal',
            'Mobile App' => 'Mobile App',
            'CRM' => 'CRM',
        ];
    }

    public function getCompanyNameFromEmail($email)
    {
        if (empty($email)) {
            return null;
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return null;
        }
        $domain = $parts[1];
        return ucwords(str_replace(['.com', '.org', '.net'], '', $domain));
    }

    public function getEscalationHistory()
    {
        return $this->hasOne(TicketEscalation::class, ['ticket_id' => 'id']);
    }

    public function getEscalatedTo()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_to']);
    }

    public function getEscalatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_by']);
    }

    // Add this method to handle the file upload
    public function upload()
    {
        if (!$this->validate()) {
            return false;
        }
    
        if ($this->imageFile) {
            try {
                $fileName = 'ticket_' . time() . '_' . uniqid() . '.' . $this->imageFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/tickets/');
                
                if (!is_dir($uploadPath)) {
                    FileHelper::createDirectory($uploadPath, 0777, true);
                }
                
                if ($this->imageFile->saveAs($uploadPath . $fileName)) {
                    $this->screenshot = $fileName;
                    return true;
                }
            } catch (\Exception $e) {
                Yii::error('Upload failed: ' . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }
    // Update beforeSave to ensure screenshot is handled properly
    // public function beforeSave($insert)
    // {
    //     if (parent::beforeSave($insert)) {
    //         // If screenshot is empty string, set to null
    //         if ($this->screenshot === '') {
    //             $this->screenshot = null;
    //         }
            
    //         // Debug the screenshot value
    //         Yii::debug('Screenshot before save: ' . (is_null($this->screenshot) ? 'null' : 'has data'));
            
    //         return true;
    //     }
    //     return false;
    // }

    public function validateCloudinaryUrl($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $cloudName = Yii::$app->params['cloudinary']['cloud_name'];
            if (strpos($this->$attribute, $cloudName) === false) {
                $this->addError($attribute, 'Invalid screenshot URL format');
            }
        }
    }

    public function getScreenshotUrl()
    {
        return $this->screenshot_url; // Assuming this is stored in the database
    }

    protected function calculateSlaDeadlines()
    {
        // Define SLA times in minutes for each severity level
        $slaConfig = [
            self::SEVERITY_CRITICAL => ['resolution' => 1440, 'update' => 240],  // 24 hours, 4 hours
            self::SEVERITY_HIGH => ['resolution' => 2880, 'update' => 480],      // 48 hours, 8 hours
            self::SEVERITY_MEDIUM => ['resolution' => 10080, 'update' => 1440],  // 7 days, 24 hours
            self::SEVERITY_LOW => ['resolution' => 20160, 'update' => 2880],     // 14 days, 48 hours
        ];

        $config = $slaConfig[$this->severity_level] ?? $slaConfig[self::SEVERITY_LOW];
        
        $this->resolution_deadline = new Expression("DATE_ADD(NOW(), INTERVAL {$config['resolution']} MINUTE)");
        $this->next_update_due = new Expression("DATE_ADD(NOW(), INTERVAL {$config['update']} MINUTE)");
    }

    protected function updateSlaStatus()
    {
        if ($this->status === 'closed') {
            return;
        }

        $now = time();
        $deadline = strtotime($this->resolution_deadline);
        
        // If within 25% of resolution time, mark as at risk
        if ($now >= ($deadline - ($deadline * 0.25))) {
            $this->sla_status = self::SLA_STATUS_AT_RISK;
        }
        
        // If past deadline, mark as breached
        if ($now > $deadline) {
            $this->sla_status = self::SLA_STATUS_BREACHED;
        }
    }

    public function getSeverityLabel()
    {
        return [
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_LOW => 'Low',
        ][$this->severity_level] ?? 'Unknown';
    }

    public function getSeverityList()
    {
        return [
            self::SEVERITY_CRITICAL => 'Critical - System Down (24h)',
            self::SEVERITY_HIGH => 'High - Major Function Affected (48h)',
            self::SEVERITY_MEDIUM => 'Medium - Minor Function Affected (7d)',
            self::SEVERITY_LOW => 'Low - Cosmetic Issue (14d)',
        ];
    }

    public function getSlaStatusLabel()
    {
        return [
            self::SLA_STATUS_WITHIN => 'Within SLA',
            self::SLA_STATUS_AT_RISK => 'At Risk',
            self::SLA_STATUS_BREACHED => 'SLA Breached',
        ][$this->sla_status] ?? 'Unknown';
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getApprovedBy()
    {
        return $this->hasOne(User::class, ['id' => 'approved_by']);
    }
}


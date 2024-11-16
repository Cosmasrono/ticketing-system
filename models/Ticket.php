<?php

namespace app\models;

use Yii; // Make sure this line is included
 
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use \DateTime;  // Add this import
use \DateTimeZone;  // Add this import too

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

    public static function tableName()
    {
        return 'ticket'; // make sure this matches your actual table name
    }




    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => function() { return time(); },
            ],
        ];
    }

    public function rules()
    {
        return [
            [['module', 'issue', 'description'], 'required'],
            [['module', 'issue', 'description', 'screenshot'], 'string'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_CANCELLED,
                self::STATUS_ASSIGNED,
                self::STATUS_ESCALATED,
                self::STATUS_CLOSED,
                self::STATUS_REOPEN,
                self::STATUS_REASSIGNED
            ]],
            ['screenshot_base64', 'safe'],
            [['assigned_to'], 'integer'],
            [['status'], 'string'],
            [['created_at', 'assigned_at', 'closed_at'], 'safe'],
            ['reopen_reason', 'safe'],
            ['reopen_reason', 'string', 'max' => 65535],
            ['closed_by', 'integer'],
            ['closed_at', 'safe'],
            
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module' => 'Module',
            'issue' => 'Issue',
            'description' => 'Description',
            'status' => 'Status',
            'screenshot' => 'Screenshot',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'assigned_to' => 'Reassign To',
        ];
    }
 
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
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
        if ($this->status !== 'Pending') {
            $this->addError('status', "Cannot approve ticket: Ticket must be in Pending status to approve. Current status: {$this->status}");
            return false;
        }
    
        $this->status = 'Approved';
        
        if (!$this->save()) {
            Yii::error("Failed to save approved ticket {$this->id}. Errors: " . json_encode($this->errors));
            return false;
        }
    
        return true;
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['assign'] = ['assigned_to', 'status'];
        return $scenarios;
    }

   
    

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::info("afterSave called for ticket {$this->id}. Changed attributes: " . json_encode($changedAttributes));

        // Your custom logic here
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
        return array_merge(parent::attributes(), ['updated_at', 'assigned_to', 'screenshot_base64']);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->created_at = $this->ensureInteger($this->created_at);
        $this->closed_at = $this->ensureInteger($this->closed_at, true);

        return true;
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // For new records
            if ($this->isNewRecord) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            
            // When status changes to assigned
            if ($this->isAttributeChanged('assigned_to') && !empty($this->assigned_to)) {
                $this->assigned_at = date('Y-m-d H:i:s');
            }
            
            // When status changes to closed
            if ($this->isAttributeChanged('status') && $this->status === 'closed') {
                $this->closed_at = date('Y-m-d H:i:s');
            }
            
            return true;
        }
        return false;
    }

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
}

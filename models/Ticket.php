<?php

namespace app\models;

use Yii; // Make sure this line is included
 
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

class Ticket extends ActiveRecord
{

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ESCALATE = 'escalated';
    const STATUS_CLOSED = 'closed';
    const STATUS_DELETED = 'deleted';
    const STATUS_ESCALATED = 'escalated';  // Add this line if you need 'escalated' status
    const STATUS_REOPENED = 'reopened';
    const STATUS_REOPEN = 'reopen';  // Add this constant

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
            [['module', 'issue', 'description', 'created_by', 'status'], 'required'],
            [['description'], 'string'],
            [['created_by', 'created_at'], 'integer'],
            [['module', 'issue', 'status'], 'string', 'max' => 255],
            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_CANCELLED,
                self::STATUS_ESCALATE,
                self::STATUS_CLOSED,
                self::STATUS_REOPEN
                 // Add reopen to valid statuses
            ]],
            [['created_at', 'closed_at'], 'integer', 'skipOnEmpty' => true],
            [['created_at', 'closed_at'], 'default', 'value' => null],
            ['assigned_to', 'integer'],
            ['assigned_to', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['assigned_to' => 'id']],
            ['screenshot', 'string'],
            [['escalated_at'], 'safe'],
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
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'company_email' => 'Company Email',
            'escalated_at' => 'Escalated At',
        ];
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
        $scenarios['assign'] = ['assigned_to', 'assigned_at'];
        return $scenarios;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->created_at === null || $this->created_at === '') {
            $this->created_at = time();
        } else {
            $this->created_at = (int)$this->created_at;
        }

        if ($this->closed_at === '') {
            $this->closed_at = null;
        } elseif ($this->closed_at !== null) {
            $this->closed_at = (int)$this->closed_at;
        }

        return true;
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
        return $this->status === self::STATUS_ESCALATE ? 'Reassign' : 'Assign';
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
            self::STATUS_ESCALATE => 'Escalate',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_DELETED => 'Deleted',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ESCALATED => 'Escalated',  // Add this line if you added the constant
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['updated_at', 'assigned_to']);
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
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_CLOSED, self::STATUS_ESCALATE])) {
            return false;
        }

        $this->status = self::STATUS_ESCALATE;
        $this->escalated_at = new Expression('NOW()');
        
        return $this->save();
    }
}

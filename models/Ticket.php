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
    const STATUS_CLOSED = 'closed';

    public static function tableName()
    {
        return '{{%ticket}}';
    }




    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['assigned_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['assigned_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['assigned_to'], 'integer'],
            [['assigned_at'], 'safe'], // Change this to 'safe' instead of 'datetime'
            [['status', 'company_email'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['assigned_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at', 'assigned_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['timer_start'], 'safe'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_CANCELLED, self::STATUS_CLOSED]],    
            [['action'], 'string', 'max' => 255],
            ['action', 'default', 'value' => 'reopen'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'status' => 'Status',
            'closed_by' => 'Closed By',
            'assigned_to' => 'Assigned To',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'assigned_at' => 'Assigned At',
            
         

        ];
    }
 
    public function getUser()
    {
        return $this->hasOne(User::class, ['email' => 'company_email']);
    }


    public function getAssignedDeveloper()
    {
        return $this->hasOne(Developer::class, ['id' => 'assigned_to']);
    }





    public function getDeveloper()
    {
        return $this->hasOne(Developer::class, ['id' => 'assigned_to']);
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

        Yii::info("Attempting to save ticket ID: {$this->id}, Status: {$this->status}, Action: {$this->action}", 'ticket');

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

}

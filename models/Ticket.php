<?php

namespace app\models;

use Yii; // Make sure this line is included
 
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

class Ticket extends ActiveRecord
{

   



    public static function tableName()
    {
        return '{{%ticket}}';
    }




    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false, // This disables the updated_at attribute
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['assigned_to', 'closed_by'], 'integer'],
            [['status', 'company_email'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['assigned_at'], 'datetime'],
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
        return $this->hasOne(Developer::class, ['id' => 'developer_id']);
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


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->status = 'pending'; // Set default status to 'pending'
                $this->company_email = Yii::$app->user->identity->company_email; // Set company_email to the current user's company email
            }
            if ($this->assigned_to && !$this->assigned_at) {
                $this->assigned_at = date('Y-m-d H:i:s'); // Set assigned_at to the current timestamp
            }
            return true;
        }
        return false;
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
        if ($this->assigned_at) {
            $assignedTime = new \DateTime($this->assigned_at);
            $currentTime = new \DateTime();
            $interval = $assignedTime->diff($currentTime);
            
            $hoursPassed = $interval->h + ($interval->days * 24);
            $minutesPassed = $interval->i;
            $secondsPassed = $interval->s;
            
            $totalSecondsPassed = ($hoursPassed * 3600) + ($minutesPassed * 60) + $secondsPassed;
            $totalSecondsRemaining = (48 * 3600) - $totalSecondsPassed;
            
            return $totalSecondsRemaining > 0 ? $totalSecondsRemaining : 0;
        }
        return null;
    }

}

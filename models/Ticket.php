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
        return 'ticket';
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
            ['title', 'string', 'max' => 255],
            ['description', 'string'],
            ['status', 'string'],
            [['assigned_to'], 'integer'],
            ['status', 'in', 'range' => ['pending', 'approved','cancelled']],
            ['company_email', 'email'],
        ];
    }


 




    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'status' => 'Status',
            'company_email' => 'Company Email',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
 
    public function getUser()
    {
        return $this->hasOne(User::class, ['email' => 'company_email']);
    }


    public function getAssignedDeveloper()
    {
        return $this->hasOne(User::className(), ['id' => 'assigned_to']);
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
    if (!parent::beforeSave($insert)) {
        return false;
    }

    Yii::info("beforeSave called for ticket {$this->id}. Current attributes: " . json_encode($this->attributes));

    // Your custom logic here

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

}
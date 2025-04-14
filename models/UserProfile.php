<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserProfile extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_profile';
    }

    public function rules()
    {
        return [
            [['user_id', 'entry_date'], 'required'],
            [['user_id'], 'integer'],
            [['entry_date', 'leave_date'], 'safe'],
            [['notes', 'position'], 'string'],
            [['attendance_status'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'entry_date' => 'Entry Date',
            'leave_date' => 'Leave Date',
            'position' => 'Position',
            'notes' => 'Notes',
            'attendance_status' => 'Attendance Status',
        ];
    }

    // Relation to User model
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
} 
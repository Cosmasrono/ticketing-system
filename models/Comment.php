<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Comment extends ActiveRecord
{
    public static function tableName()
    {
        return 'comments';
    }

    public function rules()
    {
        return [
            [['ticket_id', 'comments'], 'required'],
            ['ticket_id', 'integer'],
            ['comments', 'string'],
            ['created_by', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ticket_id' => 'Ticket ID',
            'comments' => 'Comments',
            'created_by' => 'Created By',
        ];
    }

    // ... other model methods ...
}

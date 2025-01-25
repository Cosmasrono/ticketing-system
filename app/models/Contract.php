<?php

namespace app\models;

use yii\db\ActiveRecord;

class Contract extends ActiveRecord
{
    public static function tableName()
    {
        return 'contracts'; // Ensure this matches your table name
    }

    // Define your attributes and any necessary validation rules
    public function rules()
    {
        return [
            [['client_id', 'type', 'start_date', 'end_date', 'status', 'value'], 'required'],
            [['start_date', 'end_date'], 'safe'],
            [['value'], 'number'],
            [['status'], 'string', 'max' => 255],
        ];
    }

    // Add any other methods as needed
} 
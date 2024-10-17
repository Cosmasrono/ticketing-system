<?php

namespace app\models;

use yii\db\ActiveRecord;

class Company extends ActiveRecord
{
    public static function tableName()
    {
        return 'company'; // Make sure this matches your actual table name
    }

    // Define other attributes and relations as needed
}

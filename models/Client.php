<?php

namespace app\models;

use yii\db\ActiveRecord;

class Client extends ActiveRecord
{
    // Define the table name (optional if it follows Yii's naming conventions)
    public static function tableName()
    {
        return 'client'; // The name of your table
    }

    // Define rules and validation if needed (optional)
}

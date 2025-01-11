<?php

namespace app\models;

use yii\db\ActiveRecord;

class Roles extends ActiveRecord
{
    public static function tableName()
    {
        return 'roles';
    }

    // ... other code ...
}


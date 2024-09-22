<?php


namespace app\models;
use yii\db\ActiveRecord;

class Developer extends ActiveRecord
{
    public static function tableName()
    {
        return 'developer';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id']);
    }

    // Other methods...
}
<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Admin extends ActiveRecord
{
    public static function tableName()
    {
        return 'admin'; // or whatever your admin table is called
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['company_email'], 'required'],
            [['company_email'], 'email'],
            [['company_email'], 'unique'],
        ];
    }

    public static function isAdminEmail($email)
    {
        return self::find()->where(['company_email' => $email])->exists();
    }
}
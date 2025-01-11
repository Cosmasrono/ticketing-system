<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Client extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_email', 'company_name'], 'required'],
            [['company_email'], 'email'],
            [['company_email'], 'unique'],
            [['company_name'], 'string', 'max' => 255],
            [['status'], 'integer'],
        ];
    }

    /**
     * Check if email exists in clients table
     */
    public static function emailExists($email)
    {
        return static::find()->where(['company_email' => $email])->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_email' => 'Company Email',
            'company_name' => 'Company Name',
            'status' => 'Status',
        ];
    }
}
<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Yii;

class Client extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false, // Set this to false to disable updated_at
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['company_email'], 'required'],
            [['company_email'], 'email'],
            [['created_at'], 'safe'],
            [['company_email'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_email' => 'Company Email',
            'created_at' => 'Created At',
            // ... other attribute labels ...
        ];
    }
 
  
    
        public function beforeValidate()
        {
            if (!parent::beforeValidate()) {
                Yii::error('Parent beforeValidate returned false for client');
                return false;
            }
            Yii::info('Before validating client: ' . json_encode($this->attributes));
            return true;
        }
    
        public function afterValidate()
        {
            parent::afterValidate();
            if ($this->hasErrors()) {
                Yii::error('Validation errors for client: ' . json_encode($this->errors));
            } else {
                Yii::info('Client validated successfully');
            }
        }
    
        public function beforeSave($insert)
        {
            if (!parent::beforeSave($insert)) {
                Yii::error('Parent beforeSave returned false for client');
                return false;
            }
            Yii::info('Before saving client: ' . json_encode($this->attributes));
            return true;
        }
    
        public function afterSave($insert, $changedAttributes)
        {
            parent::afterSave($insert, $changedAttributes);
            Yii::info('Client saved successfully. Insert: ' . ($insert ? 'Yes' : 'No'));
        }
    
        

    public static function emailExists($email)
    {
        return self::find()->where(['company_email' => $email])->exists();
    }

    public static function adminEmailExists($email)
    {
        return Admin::find()->where(['company_email' => $email])->exists();
    }

    public static function developerEmailExists($email)
    {
        return Developer::find()->where(['company_email' => $email])->exists();
    }

}

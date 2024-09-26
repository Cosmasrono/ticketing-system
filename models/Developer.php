<?php


namespace app\models;
use Yii;
use yii\db\ActiveRecord;

class Developer extends ActiveRecord
{
    public static function tableName()
    {
        return 'developer'; // or whatever your table name is
    }

    public function rules()
    {
        return [
            [['name', 'company_email'], 'required'],
            [['company_email'], 'email'],
            [['name', 'company_email'], 'string', 'max' => 255],
            [['company_email'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_email' => 'Company Email',
        ];
    }

    public static function findByCompanyEmail($email)
    {
        Yii::info("Searching for developer with email: $email", __METHOD__);
        $developer = self::findOne(['company_email' => $email]);
        if ($developer === null) {
            Yii::error("No developer found with email: $email", __METHOD__);
        } else {
            Yii::info("Developer found: " . json_encode($developer->attributes), __METHOD__);
        }
        return $developer;
    }

    public function getAssignedTickets()
    {
        return $this->hasMany(Ticket::class, ['assigned_to' => 'id']);
    }

    // Remove these methods as they're causing confusion between 'email' and 'company_email'
    /*
    public function afterFind()
    {
        parent::afterFind();
        $this->company_email = $this->email;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->company_email !== null) {
                $this->email = $this->company_email;
            }
            return true;
        }
        return false;
    }
    */
}
<?php


namespace app\models;
use Yii;
use yii\db\ActiveRecord;

class Developer extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%developer}}';
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

    /**
     * Finds a developer by company email
     *
     * @param string $email
     * @return Developer|null
     */
    public static function findByCompanyEmail($email)
    {
        return static::findOne(['company_email' => $email]);
    }

    public function getAssignedTickets()
    {
        return $this->hasMany(Ticket::class, ['assigned_to' => 'id']);
    }

    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['assigned_to' => 'id']);
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

    public static function emailExists($email)
    {
        return self::find()->where(['company_email' => $email])->exists();
    }
}

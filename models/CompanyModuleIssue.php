<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "company_module_issue".
 *
 * @property int $id
 * @property string $company_email
 * @property string $module
 * @property string $issue
 *
 * @property User $companyEmail
 */
class CompanyModuleIssue extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%company_module_issue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_email', 'module', 'issue'], 'required'],
            [['company_email', 'module', 'issue'], 'string', 'max' => 255],
            [['company_email', 'module', 'issue'], 'unique', 'targetAttribute' => ['company_email', 'module', 'issue']],
            [['company_email'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['company_email' => 'company_email']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_email' => 'Company Email',
            'module' => 'Module',
            'issue' => 'Issue',
        ];
    }

    /**
     * Gets query for [[CompanyEmail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyEmail()
    {
        return $this->hasOne(User::class, ['company_email' => 'company_email']);
    }
}

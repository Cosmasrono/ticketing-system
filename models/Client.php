<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Client extends ActiveRecord
{
    public $modules; // For form handling

    protected $_availableModules = [
        'Finance' => 'Finance',
        'HR' => 'HR',
        'Payroll' => 'Payroll',
        'BOSA' => 'BOSA',
        'FOSA' => 'FOSA',
        'EDMS' => 'EDMS',
        'Member Portal' => 'Member Portal',
        'Mobile App' => 'Mobile App',
        'Procurement' => 'Procurement',
        'CRM' => 'CRM',
        'Credit' => 'Credit',
        'Marketing' => 'Marketing',
        'call center' => 'call center'
    ];

    /**
     * Get available modules
     */
    public function getAvailableModules()
    {
        return $this->_availableModules;
    }

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
            [['company_name', 'company_email'], 'required'],
            [['company_name', 'company_email', 'module'], 'string', 'max' => 255],
            [['company_email'], 'email'],
            [['modules'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
            'module' => 'Modules',
        ];
    }

    public function getClients()
    {
        return $this->hasMany(Clients::class, ['company_id' => 'id']);
    }
}

class Clients extends ActiveRecord
{
    public static function tableName()
    {
        return 'clients'; // The name of your clients table
    }

    public function rules()
    {
        return [
            [['company_id', 'client_name', 'client_email'], 'required'],
            [['company_id'], 'integer'],
            [['client_name', 'client_email'], 'string', 'max' => 255],
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Client::class, ['id' => 'company_id']);
    }
}

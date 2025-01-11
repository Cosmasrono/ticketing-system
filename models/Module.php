<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Module extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%module}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module_name', 'module_code', 'category'], 'required'],
            [['module_name', 'module_code', 'category'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['status'], 'integer'],
            // Ensure module_code is unique
            ['module_code', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_name' => 'Module Name',
            'module_code' => 'Module Code',
            'category' => 'Category',
            'description' => 'Description',
            'status' => 'Status',
        ];
    }
} 
<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ModuleList extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%module_list}}';
    }

    public function rules()
    {
        return [
            [['module_name', 'module_code', 'price', 'category'], 'required'],
            [['description'], 'string'],
            [['price'], 'number'],
            [['module_name'], 'string', 'max' => 255],
            [['module_code', 'category'], 'string', 'max' => 50],
            [['module_code'], 'unique'],
            ['status', 'default', 'value' => 10],
        ];
    }

    public static function getModulesByCategory()
    {
        return static::find()
            ->select(['id', 'module_name', 'module_code', 'description', 'price', 'category'])
            ->where(['status' => 10])
            ->orderBy(['category' => SORT_ASC, 'module_name' => SORT_ASC])
            ->asArray()
            ->all();
    }
} 
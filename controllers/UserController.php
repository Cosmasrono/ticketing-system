<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\ArrayHelper;

class UserController extends Controller
{
    public function actionGetDevelopers()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $developers = User::find()
            ->select(['id', 'username'])
            ->where(['role' => 'developer'])
            ->asArray()
            ->all();
        
        return ArrayHelper::map($developers, 'id', 'username');
    }
}

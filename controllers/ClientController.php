<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Client;

class ClientController extends Controller
{
    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new Client();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => 'Client added successfully',
            ];
        } else {
            return [
                'success' => false,
                'message' => $model->getErrors(),
            ];
        }
    }
}

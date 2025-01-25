<?php

namespace app\controllers;

use Yii;
use app\models\ContractRenewal;
use yii\web\Controller;
use yii\web\Response;

class ContractRenewalController extends Controller
{
    public function actionApprove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $model = ContractRenewal::findOne($id);
        
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Contract renewal not found'
            ];
        }

        $model->renewal_status = 'approved';
        $model->updated_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Contract renewal approved successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to approve contract renewal'
        ];
    }

    public function actionReject()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $model = ContractRenewal::findOne($id);
        
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Contract renewal not found'
            ];
        }

        $model->renewal_status = 'rejected';
        $model->updated_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Contract renewal rejected successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to reject contract renewal'
        ];
    }
} 
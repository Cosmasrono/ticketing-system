<?php

namespace app\controllers;

use Yii;
use app\models\ContractRenewal;
use app\models\Company;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

class ContractRenewalController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $model = new ContractRenewal();
            $postData = Yii::$app->request->post();
            
            // Log the received data
            Yii::debug('Received POST data: ' . print_r($postData, true));
            
            if ($model->load($postData)) {
                // Set additional fields
                $model->requested_by = Yii::$app->user->id;
                
                // Validate the model
                if (!$model->validate()) {
                    Yii::error('Validation errors: ' . print_r($model->getErrors(), true));
                    return [
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $model->getErrors() // Send back detailed errors
                    ];
                }

                // Try to save
                if ($model->save()) {
                    // Update company dates if needed
                    $company = Company::findOne($model->company_id);
                    if ($company) {
                        $company->end_date = $model->new_end_date;
                        if (!$company->save()) {
                            Yii::error('Company update failed: ' . print_r($company->getErrors(), true));
                        }
                    }
                    
                    return [
                        'success' => true,
                        'message' => 'Contract renewal has been submitted successfully.',
                        'renewalId' => $model->id
                    ];
                } else {
                    Yii::error('Save failed: ' . print_r($model->getErrors(), true));
                    return [
                        'success' => false,
                        'message' => 'Failed to save renewal',
                        'errors' => $model->getErrors()
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Invalid form data',
                'errors' => $model->getErrors(),
                'receivedData' => $postData
            ];
            
        } catch (\Exception $e) {
            Yii::error('Exception in contract renewal: ' . $e->getMessage());
            Yii::error('Stack trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'An error occurred while processing your request',
                'debug' => YII_DEBUG ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ];
        }
    }
} 
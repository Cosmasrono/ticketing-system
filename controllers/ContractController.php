<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Contract;
use app\models\ContractRenewal;

class ContractController extends Controller
{
    public function actionRenew($id)
    {
        $contract = Contract::findOne($id);
        if (!$contract) {
            throw new NotFoundHttpException('Contract not found.');
        }

        $renewalModel = new ContractRenewal();
        if ($renewalModel->load(Yii::$app->request->post())) {
            // Set the contract ID and other necessary attributes
            $renewalModel->contract_id = $contract->id; // Assuming you have a foreign key
            $renewalModel->end_date = date('Y-m-d', strtotime("+{$renewalModel->extension_period} months", strtotime($contract->end_date)));
            
            if ($renewalModel->validate() && $renewalModel->save()) {
                Yii::$app->session->setFlash('success', 'Contract renewal request submitted successfully.');
                return $this->redirect(['view', 'id' => $contract->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to submit contract renewal: ' . implode(', ', $renewalModel->getFirstErrors()));
            }
        }

        return $this->render('renew-contract', [
            'company' => $contract->company, // Assuming you have a relation to get the company
            'renewalModel' => $renewalModel,
        ]);
    }
} 
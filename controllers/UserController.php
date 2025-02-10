<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\models\User;
use Yii;
use yii\web\NotFoundHttpException;
use app\models\Company;
use app\models\Ticket;
use app\models\ContractRenewal;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],  // Authenticated users only
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check user status only if logged in
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user->status == 0) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact administrator.');
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }
        }

        return true;
    }

    public function actionProfile($id)
    {
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }

        // Get the associated company data
        $company = Company::find()
            ->where(['company_name' => $user->company_name])
            ->one();

        if ($company === null) {
            throw new NotFoundHttpException('Company not found.');
        }

        // Create new renewal model instance
        $model = new ContractRenewal();

        // Get the associated company data with explicit ID selection
        $companyDetails = Yii::$app->db->createCommand("
            SELECT 
                c.id as id,
                c.company_name,
                c.company_email,
                c.start_date,
                c.end_date,
                c.status,
                c.company_type,
                c.subscription_level,
                c.created_at,
                c.updated_at
            FROM company c
            WHERE c.company_name = :company_name
            LIMIT 1
        ")
        ->bindValue(':company_name', $user->company_name)
        ->queryOne();

        // Debug logging
        Yii::debug("Company Details Query Result:", 'application');
        Yii::debug($companyDetails, 'application');

        if (!isset($companyDetails['id'])) {
            Yii::error("Company ID not found for company_name: {$user->company_name}");
            throw new NotFoundHttpException('Company details not found.');
        }

        // Fetch tickets for this company
        $tickets = Ticket::find()
            ->where(['company_id' => $company->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Fetch contract renewals for this company
        $renewals = ContractRenewal::find()
            ->where(['company_id' => $company->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'tickets' => $tickets,
            'renewals' => $renewals,
            'companyDetails' => $companyDetails,
            'model' => $model,
        ]);
    }

    public function actionGetDevelopers()
    {
        $developers = User::find()->where(['role' => 3])->all(); // Assuming role 3 is for developers
        $result = [];
        foreach ($developers as $developer) {
            $result[] = [
                'id' => $developer->id,
                'name' => $developer->name,
            ];
        }
        return $this->asJson($result);
    }

    public function actionToggleStatus()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = \Yii::$app->request->post('id');
            
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'User ID is required'
                ];
            }

            $user = User::findOne($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Toggle status between active (10) and inactive (0)
            $user->status = ($user->status == 10) ? 0 : 10;

            if ($user->save(false)) {
                $statusText = $user->status == 10 ? 'activated' : 'deactivated';
                return [
                    'success' => true,
                    'message' => "User successfully {$statusText}",
                    'newStatus' => $user->status,
                    'userId' => $user->id
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update user status'
            ];

        } catch (\Exception $e) {
            \Yii::error('Error toggling user status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => \YII_DEBUG ? $e->getMessage() : 'An error occurred while updating user status'
            ];
        }
    }

    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Set response format to JSON
        $id = Yii::$app->request->post('id');

        if ($id) {
            $user = User::findOne($id);
            if ($user) {
                if ($user->delete()) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Failed to delete user.'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found.'];
            }
        }
        return ['success' => false, 'message' => 'Invalid request.'];
    }
}

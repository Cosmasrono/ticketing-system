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

    public function actionProfile($id = null)
    {
        // If no ID is provided, use the current user's ID
        if ($id === null) {
            $id = Yii::$app->user->id;
        }

        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }

        // Get the associated company data
        $company = Company::findOne(['company_name' => $user->company_name]);
        if ($company === null) {
            throw new NotFoundHttpException('Company not found.');
        }

        // Fetch tickets and renewals
        $tickets = Ticket::find()->where(['company_id' => $company->id])->all();
        $renewals = ContractRenewal::find()->where(['company_id' => $company->id])->all();

        // Check if the current user is a super admin (role 4)
        $isSuperAdmin = Yii::$app->user->identity->role === 4;

        // Fetch additional statistics for super admin
        $ticketStats = null;
        $renewalStats = null;
        
        if ($isSuperAdmin) {
            $ticketStats = [
                'total' => Ticket::find()->where(['company_id' => $company->id])->count(),
                'pending' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'pending'])->count(),
                'approved' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'approved'])->count(),
                'closed' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'closed'])->count(),
                'breached_sla' => Ticket::find()->where(['company_id' => $company->id, 'sla_status' => 'breached'])->count(),
            ];

            $renewalStats = [
                'total' => ContractRenewal::find()->where(['company_id' => $company->id])->count(),
                'pending' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'pending'])->count(),
                'approved' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'approved'])->count(),
            ];
        }

        // Company details for display
        $companyDetails = [
            'name' => $company->company_name,
            'email' => $company->company_email,
            'status' => $company->status,
            'startDate' => $company->start_date,
            'endDate' => $company->end_date,
        ];

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'tickets' => $tickets,
            'renewals' => $renewals,
            'companyDetails' => $companyDetails,
            'isSuperAdmin' => $isSuperAdmin,
            'ticketStats' => $ticketStats,
            'renewalStats' => $renewalStats,
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

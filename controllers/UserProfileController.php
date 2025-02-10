<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\models\Company;
use app\models\Ticket;
use app\models\ContractRenewal;
use DateTime;

class UserProfileController extends Controller
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

    public function actionView($id)
    {
        // Add this debug code
        Yii::info([
            'user_id' => Yii::$app->user->id,
            'user_status_before' => Yii::$app->user->identity->status,
            'timestamp' => date('Y-m-d H:i:s'),
        ], 'profile-debug');

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
    
        // Check if user has access to this profile
        if ($user->id !== Yii::$app->user->id) {
            // Check the role of the current user
            $currentUserRole = Yii::$app->user->identity->role;
            
            // Allow access based on roles
            if ($currentUserRole === 3) { // Developer
                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            } elseif ($currentUserRole === 1) { // Admin
                // Admin can view any profile
            } elseif ($currentUserRole === 2) { // User
                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            } else {
                throw new NotFoundHttpException('Invalid profile access.');
            }
        }
        
        // Fetch recent tickets
        $recentTickets = Ticket::find()
            ->where(['assigned_to' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Check if the logged-in user is a super admin (role 4)
        $isSuperAdmin = Yii::$app->user->identity->role === 4;

        // Fetch ticket and renewal statistics only if the user is a super admin
        if ($isSuperAdmin) {
            // Fetch ticket statistics for the company
            $ticketStats = [
                'total' => Ticket::find()->where(['company_id' => $company->id])->count(),
                'pending' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'pending'])->count(),
                'approved' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'approved'])->count(),
                'closed' => Ticket::find()->where(['company_id' => $company->id, 'status' => 'closed'])->count(),
                'breached_sla' => Ticket::find()->where(['company_id' => $company->id, 'sla_status' => 'breached'])->count(),
            ];

            // Fetch renewal statistics for the company
            $renewalStats = [
                'total' => ContractRenewal::find()->where(['company_id' => $company->id])->count(),
                'pending' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'pending'])->count(),
                'approved' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'approved'])->count(),
            ];
        }

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'tickets' => $tickets,
            'renewals' => $renewals,
            'ticketStats' => $isSuperAdmin ? $ticketStats : null,
            'renewalStats' => $isSuperAdmin ? $renewalStats : null,
            'isSuperAdmin' => $isSuperAdmin,
            'recentTickets' => $recentTickets,
        ]);
    }
}
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
        // Get current user's role
        $currentUserRole = Yii::$app->user->identity->role;
        $isSuperAdmin = $currentUserRole === 4; // Role 4 is superadmin

        // Get basic user and company info
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        $company = Company::findOne(['company_name' => $user->company_name]);
        if (!$company) {
            throw new NotFoundHttpException('Company not found.');
        }

        // Initialize variables
        $companyStats = [];
        $ticketStats = [];
        $userTickets = [];
        $developers = [];

        // If user is superadmin, fetch additional data
        if ($isSuperAdmin) {
            // Get company statistics
            $companyStats = [
                'total_companies' => Company::find()->count(),
                'active_contracts' => Company::find()->where(['status' => 1])->count(),
                'total_users' => User::find()->count(),
            ];

            // Get users who created tickets (ticket raisers)
            $userTickets = User::find()
                ->select(['user.username', 'COUNT(ticket.id) as ticket_count'])
                ->leftJoin('ticket', 'user.id = ticket.created_by')
                ->groupBy(['user.id', 'user.username'])
                ->having(['>', 'ticket_count', 0])
                ->orderBy(['ticket_count' => SORT_DESC])
                ->limit(10)
                ->asArray()
                ->all();

            // Get developers (users assigned to tickets)
            $developers = User::find()
                ->select(['user.username', 'COUNT(ticket.id) as assigned_count'])
                ->leftJoin('ticket', 'user.id = ticket.developer_id')
                ->where(['not', ['ticket.developer_id' => null]])
                ->groupBy(['user.id', 'user.username'])
                ->having(['>', 'assigned_count', 0])
                ->orderBy(['assigned_count' => SORT_DESC])
                ->limit(10)
                ->asArray()
                ->all();

            // Get ticket statistics
            $ticketStats = [
                'total' => Ticket::find()->count(),
                'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
                'high_severity' => Ticket::find()->where(['>=', 'severity_level', 3])->count(),
                'breached_sla' => Ticket::find()->where(['sla_status' => 'breached'])->count(),
                'most_common_issues' => Ticket::find()
                    ->select(['issue', 'COUNT(*) as count'])
                    ->groupBy('issue')
                    ->orderBy(['count' => SORT_DESC])
                    ->limit(5)
                    ->asArray()
                    ->all(),
            ];
        }

        // Get company details for the view
        $companyDetails = [
            'id' => $company->id,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'start_date' => $company->start_date,
            'end_date' => $company->end_date,
            'status' => $company->status,
            'created_at' => $company->created_at,
            'updated_at' => $company->updated_at,
            'role' => $currentUserRole,
        ];

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'companyDetails' => $companyDetails,
            'companyStats' => $companyStats,
            'ticketStats' => $ticketStats,
            'userTickets' => $userTickets,
            'developers' => $developers,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
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
        $isCEO = $currentUserRole === 4;

        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }
    
        // Get the associated company data
        $company = Company::findOne(['company_name' => $user->company_name]);
    
        if ($company === null) {
            throw new NotFoundHttpException('Company not found.');
        }

        // For CEO (role 4), fetch all tickets with detailed information
        if ($isCEO) {
            $tickets = Ticket::find()
                ->select([
                    'ticket.*',
                    'developer.username as developer_name',
                    'creator.username as created_by_name',
                    'company.company_name',
                    'company.company_email'
                ])
                ->alias('ticket')
                ->leftJoin('user developer', 'ticket.developer_id = developer.id')
                ->leftJoin('user creator', 'ticket.created_by = creator.id')
                ->leftJoin('company', 'ticket.company_id = company.id')
                ->orderBy(['last_update_at' => SORT_DESC])
                ->all();

            // Calculate detailed statistics for CEO
            $ticketStats = [
                'total' => Ticket::find()->count(),
                'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
                'approved' => Ticket::find()->where(['status' => 'approved'])->count(),
                'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
                'breached_sla' => Ticket::find()->where(['sla_status' => 'breached'])->count(),
                'high_severity' => Ticket::find()->where(['>=', 'severity_level', 3])->count(),
                'avg_resolution_time' => Ticket::find()
                    ->where(['IS NOT', 'time_taken', null])
                    ->average('time_taken'),
            ];

            // Get company-wise ticket distribution
            $companyStats = Ticket::find()
                ->select(['company.company_name', 'COUNT(*) as ticket_count'])
                ->leftJoin('company', 'ticket.company_id = company.id')
                ->groupBy(['company.company_name'])
                ->asArray()
                ->all();

            // Get module-wise distribution
            $moduleStats = Ticket::find()
                ->select(['module', 'COUNT(*) as count'])
                ->groupBy(['module'])
                ->asArray()
                ->all();

            // Get severity-wise distribution
            $severityStats = Ticket::find()
                ->select(['severity_level', 'COUNT(*) as count'])
                ->groupBy(['severity_level'])
                ->asArray()
                ->all();

            // Get developer performance stats
            $developerStats = Ticket::find()
                ->select([
                    'developer.username as developer_name',
                    'COUNT(*) as total_tickets',
                    'AVG(CASE WHEN time_taken IS NOT NULL THEN time_taken END) as avg_resolution_time',
                    'SUM(CASE WHEN sla_status = "breached" THEN 1 ELSE 0 END) as breached_tickets'
                ])
                ->leftJoin('user developer', 'ticket.developer_id = developer.id')
                ->groupBy(['developer.username'])
                ->having(['>', 'total_tickets', 0])
                ->asArray()
                ->all();

        } else {
            // Regular ticket query for non-CEO users
            $tickets = Ticket::find()
                ->where(['company_id' => $company->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
            
            $ticketStats = null;
            $companyStats = null;
            $moduleStats = null;
            $severityStats = null;
            $developerStats = null;
        }

        // Fetch renewal statistics for the company
        $renewals = ContractRenewal::find()->where(['company_id' => $company->id])->all();
        $renewalStats = [
            'total' => ContractRenewal::find()->where(['company_id' => $company->id])->count(),
            'pending' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'pending'])->count(),
            'approved' => ContractRenewal::find()->where(['company_id' => $company->id, 'renewal_status' => 'approved'])->count(),
        ];

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'tickets' => $tickets,
            'renewals' => $renewals,
            'ticketStats' => $ticketStats,
            'companyStats' => $companyStats,
            'moduleStats' => $moduleStats,
            'severityStats' => $severityStats,
            'developerStats' => $developerStats,
            'isCEO' => $isCEO,
            'renewalStats' => $renewalStats,
            'companyDetails' => [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'start_date' => $company->start_date,
                'end_date' => $company->end_date,
                'status' => $company->status,
                'created_at' => $company->created_at,
                'updated_at' => $company->updated_at,
            ],
        ]);
    }
}
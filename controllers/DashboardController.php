<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\db\Query;
use app\models\Ticket;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin();
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        // Get developer statistics
        $developerStats = (new Query())
            ->select([
                'name' => 'user.name',
                'active_tickets' => 'COUNT(CASE WHEN ticket.status IN ("pending", "assigned") THEN 1 END)',
                'completed_tickets' => 'COUNT(CASE WHEN ticket.status = "closed" THEN 1 END)'
            ])
            ->from('user')
            ->leftJoin('ticket', 'ticket.assigned_to = user.id')
            ->where(['user.role' => 'developer'])
            ->groupBy('user.id, user.name')
            ->all();

        // Get ticket status data
        $ticketStatusData = (new Query())
            ->select(['status'])
            ->from('ticket')
            ->groupBy('status')
            ->indexBy('status')
            ->column();

        // Get recent activity (simplified query)
        $recentActivity = (new Query())
            ->select([
                'timestamp' => 'ticket.created_at',
                'ticket_id' => 'ticket.id',
                'developer' => 'user.name',
                'status' => 'ticket.status'
            ])
            ->from('ticket')
            ->leftJoin('user', 'ticket.assigned_to = user.id')
            ->orderBy(['ticket.created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Get top companies
        $topCompanies = (new Query())
            ->select([
                'company.name',
                'ticket_count' => 'COUNT(ticket.id)'
            ])
            ->from('company')
            ->leftJoin('ticket', 'ticket.company_id = company.id')
            ->groupBy('company.id, company.name')
            ->orderBy(['ticket_count' => SORT_DESC])
            ->limit(5)
            ->all();

        // Get total companies count
        $totalCompanies = (new Query())
            ->from('company')
            ->count();

        $tickets = Ticket::find()->select(['id', 'comments', 'status', 'created_at'])->all();

        return $this->render('index', [
            'developerStats' => $developerStats,
            'ticketStatusData' => $ticketStatusData,
            'recentActivity' => $recentActivity,
            'topCompanies' => $topCompanies,
            'totalCompanies' => $totalCompanies,
            'tickets' => $tickets,
        ]);
    }
} 
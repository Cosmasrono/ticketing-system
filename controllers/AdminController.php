<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Ticket;
use app\models\User;
use app\models\Developer;

class AdminController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin;
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionDashboard()
    {
        $totalTickets = Ticket::find()->count();
        $cancelledTickets = Ticket::find()->where(['status' => 'cancelled'])->count();
        $pendingTickets = Ticket::find()->where(['status' => 'pending'])->count();
        $approvedTickets = Ticket::find()->where(['status' => 'approved'])->count();
        $closedTickets = Ticket::find()->where(['status' => 'closed'])->count();
        $assignedTickets = Ticket::find()->where(['not', ['assigned_to' => null]])->count();
        $notAssignedTickets = Ticket::find()->where(['assigned_to' => null])->count();
        $totalUsers = User::find()->count();
        $totalDevelopers = Developer::find()->count();

        $recentTickets = Ticket::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'totalTickets' => $totalTickets,
            'cancelledTickets' => $cancelledTickets,
            'pendingTickets' => $pendingTickets,
            'approvedTickets' => $approvedTickets,
            'closedTickets' => $closedTickets,
            'assignedTickets' => $assignedTickets,
            'notAssignedTickets' => $notAssignedTickets,
            'totalUsers' => $totalUsers,
            'totalDevelopers' => $totalDevelopers,
            'recentTickets' => $recentTickets,
        ]);
    }
}

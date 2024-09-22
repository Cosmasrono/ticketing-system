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
        $cancelledTickets = Ticket::find()->where(['status' => 'cancelled'])->count();
        $totalTickets = Ticket::find()->count();
        $pendingTickets = Ticket::find()->where(['status' => 'pending'])->count();
        $approvedTickets = Ticket::find()->where(['status' => 'approved'])->count();
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
            'totalUsers' => $totalUsers,
            'totalDevelopers' => $totalDevelopers,
            'recentTickets' => $recentTickets,
        ]);
    }
}

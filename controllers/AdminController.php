<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Ticket;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
                            return Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                }
            ],
        ];
    }

    public function actionIndex()
    {
        $query = Ticket::find()
            ->joinWith('createdBy');

        // Calculate ticket counts by status
        $ticketCounts = Ticket::find()
            ->select(['status', 'COUNT(*) as count'])
            ->groupBy('status')
            ->indexBy('status')
            ->column();

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with('creator'),
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'ticketCounts' => $ticketCounts,
        ]);
    }

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }
}

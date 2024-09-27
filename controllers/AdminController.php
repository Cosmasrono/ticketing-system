<?php

use yii\data\ActiveDataProvider;
use app\models\Ticket;
use Yii;
use yii\web\ForbiddenHttpException;

class AdminController extends \yii\web\Controller
{   
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }
    
    public function actionAdmin()
    {
    // Ensure only admin users can access this action
    if (!Yii::$app->user->identity->isAdmin) {
        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    $ticketCounts = [
        'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
        'approved' => Ticket::find()->where(['status' => 'approved'])->count(),
        'cancelled' => Ticket::find()->where(['status' => 'cancelled'])->count(),
        'assigned' => Ticket::find()->where(['not', ['assigned_to' => null]])->count(),
        'notAssigned' => Ticket::find()->where(['assigned_to' => null])->count(),
        'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
        'total' => Ticket::find()->count(),
    ];

    $dataProvider = new ActiveDataProvider([
        'query' => Ticket::find(),
        'pagination' => [
            'pageSize' => 10, // Adjust this value as needed
        ],
        'sort' => [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ],
    ]);

    return $this->render('admin', [
        'dataProvider' => $dataProvider,
        'ticketCounts' => $ticketCounts,
    ]);

    
}
}
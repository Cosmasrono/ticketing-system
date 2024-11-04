<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Ticket;
use app\models\Developer;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\User;

class DeveloperController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['viewDashboard'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('Access denied.');
                }
            ],
        ];
    }

    public function actionView()
    {
        $user = Yii::$app->user->identity;
        
        // Add this dataProvider for the GridView
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => \app\models\Ticket::find()
                ->where(['assigned_to' => $user->id]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('view', [
            'user' => $user,
            'dataProvider' => $dataProvider,  // Pass dataProvider to the view
        ]);
    }

    // Remove or comment out all other actions (approve-ticket, close-ticket, etc.)
}

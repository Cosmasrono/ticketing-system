<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use app\models\Ticket;
use Yii;
use yii\data\ActiveDataProvider;

class DeveloperController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->company_email == 'ptiongik@gmail.com';
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionView()
    {
        $developerId = 2;  // This is the correct assigned_to value
        $developerName = 'dev1';

        $query = Ticket::find()->where(['assigned_to' => $developerId]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $ticketCount = $query->count();

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'developerName' => $developerName,
            'developerId' => $developerId,
            'ticketCount' => $ticketCount,
        ]);
    }

    public function actionCloseTicket($id)
    {
        $ticket = Ticket::findOne($id);
        if ($ticket && $ticket->assigned_to == 2) {
            $ticket->status = 'closed';
            if ($ticket->save()) {
                Yii::$app->session->setFlash('success', 'Ticket closed successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to close ticket.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Invalid ticket or not assigned to you.');
        }
        return $this->redirect(['view']);
    }

    // Other actions...
}

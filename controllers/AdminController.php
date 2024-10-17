<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use app\models\Admin;
use app\models\Ticket;
use app\models\Developer;

class AdminController extends Controller
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
                            $user = Yii::$app->user->identity;
                            return $user && Admin::find()->where(['company_email' => $user->company_email])->exists();
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('You are not allowed to access this page.');
                }
            ],
        ];
    }
    
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionAssignTicket($id)
    {
        $ticket = Ticket::findOne($id);
        if ($ticket === null) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        if ($ticket->status !== 'escalated') {
            throw new ForbiddenHttpException('This ticket cannot be assigned as it is not escalated.');
        }

        // Load available developers
        $developers = Developer::find()->all();

        if (Yii::$app->request->isPost) {
            $developerId = Yii::$app->request->post('developer_id');
            $ticket->assigned_to = $developerId;
            $ticket->status = 'assigned';
            if ($ticket->save()) {
                Yii::$app->session->setFlash('success', 'Ticket has been assigned successfully.');
                return $this->redirect(['view', 'id' => $ticket->id]);
            } else {
                Yii::$app->session->setFlash('error', 'There was an error assigning the ticket.');
            }
        }

        return $this->render('assign-ticket', [
            'ticket' => $ticket,
            'developers' => $developers,
        ]);
    }

    // Add other admin-specific actions here
}

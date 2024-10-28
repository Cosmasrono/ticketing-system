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
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            if ($user->role !== 'developer') {
                                throw new ForbiddenHttpException('You must be a developer to access this page.');
                            }
                            return true;
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionView()
    {
        $user = Yii::$app->user->identity;
        
        $dataProvider = new ActiveDataProvider([
            'query' => $user->getAssignedTickets(),
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
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApproveTicket($id)
    {
        $user = Yii::$app->user->identity;
        
        if ($user->role !== 'developer') {
            throw new ForbiddenHttpException('You must be a developer to approve tickets.');
        }

        $ticket = Ticket::findOne($id);

        if (!$ticket) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        if ($ticket->assigned_to !== $user->id) {
            throw new ForbiddenHttpException('You can only approve tickets assigned to you.');
        }

        if ($ticket->status === 'closed') {
            Yii::$app->session->setFlash('error', 'Cannot approve a closed ticket.');
            return $this->redirect(['view']);
        }

        $ticket->status = 'approved';
        if ($ticket->save()) {
            Yii::$app->session->setFlash('success', 'Ticket approved successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to approve the ticket.');
        }

        return $this->redirect(['view']);
    }

    public function actionCloseTicket($id)
    {
        $user = Yii::$app->user->identity;
        
        if ($user->role !== 'developer') {
            throw new ForbiddenHttpException('You must be a developer to close tickets.');
        }

        $ticket = Ticket::findOne($id);

        if (!$ticket) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        if ($ticket->assigned_to !== $user->id) {
            throw new ForbiddenHttpException('You can only close tickets assigned to you.');
        }

        if ($ticket->status === 'closed') {
            Yii::$app->session->setFlash('error', 'This ticket is already closed.');
            return $this->redirect(['view']);
        }

        $ticket->status = 'closed';
        if ($ticket->save()) {
            Yii::$app->session->setFlash('success', 'Ticket closed successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to close the ticket.');
        }

        return $this->redirect(['view']);
    }

  

    // Other actions...
}

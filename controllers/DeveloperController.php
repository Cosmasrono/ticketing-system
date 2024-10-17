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
                            $user = Yii::$app->user->identity;
                            return $user->role === 'developer' && Developer::findByCompanyEmail($user->company_email) !== null;
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionView()
    {
        $user = Yii::$app->user->identity;
        $developer = Developer::findByCompanyEmail($user->company_email);
        
        if (!$developer) {
            throw new ForbiddenHttpException('Developer not found for the given email.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $developer->getAssignedTickets(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'developer' => $developer,
        ]);
    }

    public function actionCloseTicket($id)
    {
        $user = Yii::$app->user->identity;
        $developer = Developer::findByCompanyEmail($user->company_email);

        if (!$developer) {
            throw new ForbiddenHttpException('Developer not found for the given email.');
        }

        $ticket = Ticket::findOne($id);
        if ($ticket && $ticket->assigned_to == $developer->id && $ticket->status !== 'closed') {
            $ticket->status = 'closed';
            $ticket->closed_by = $developer->id;
            if ($ticket->save(false, ['status', 'closed_by'])) {  // Only save these fields
                Yii::$app->session->setFlash('success', 'Ticket closed successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to close the ticket.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ticket not found, already closed, or you are not authorized to close this ticket.');
        }

        return $this->redirect(['view']);
    }

    public function actionEscalateTicket($id)
    {
        $ticket = Ticket::findOne($id);
        if ($ticket === null) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        $ticket->status = 'escalated';
        if ($ticket->save()) {
            Yii::$app->session->setFlash('success', 'Ticket has been escalated successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'There was an error escalating the ticket.');
        }

        return $this->redirect(['view', 'id' => $ticket->developer_id]);
    }

    // Other actions...
}

<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use app\models\Admin;
use app\models\Ticket;
use app\models\Developer;
use app\models\Client;
use app\models\User;
use app\models\AdminInvitation;
use app\models\InviteForm;

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
                            return Yii::$app->user->identity->isSuperAdmin();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionManageUsers()
    {
        // Implement user management logic here
        return $this->render('manageUsers');
    }

    public function actionManageTickets()
    {
        // Implement ticket management logic here
        return $this->render('manageTickets');
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

    public function actionCreateClient()
    {
        $model = new CreateClientForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($user = $model->createClient()) {
                Yii::$app->session->setFlash('success', 'New client created successfully.');
                return $this->redirect(['view-clients']); // Redirect to a list of clients
            }
        }

        return $this->render('create-client', [
            'model' => $model,
        ]);
    }

    public function actionInvite()
    {
        $model = new InviteForm();

        if ($model->load(Yii::$app->request->post()) && $model->invite()) {
            Yii::$app->session->setFlash('success', 'Invitation sent successfully.');
            return $this->refresh();
        }

        return $this->render('invite', [
            'model' => $model,
        ]);
    }

    public function actionSignup($token)
    {
        $invitation = AdminInvitation::findOne(['token' => $token]);
        if (!$invitation) {
            throw new NotFoundHttpException('Invalid invitation token.');
        }

        $user = new User();

        if ($user->load(Yii::$app->request->post()) && $user->save()) {
            // Assign admin role
            $auth = Yii::$app->authManager;
            $adminRole = $auth->getRole('admin');
            $auth->assign($adminRole, $user->id);

            // Delete the invitation
            $invitation->delete();

            Yii::$app->session->setFlash('success', 'Admin account created successfully.');
            return $this->redirect(['site/login']);
        }

        return $this->render('signup', [
            'model' => $user,
        ]);
    }

    // Add other admin-specific actions here
}

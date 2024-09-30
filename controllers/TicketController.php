<?php

namespace app\controllers;

use Yii;
use app\models\Developer;
use app\models\Ticket;
use app\models\TicketSearch;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

class TicketController extends Controller
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
                    ],
                ],
            ],
           'verbs' => [
    'class' => VerbFilter::class,
    'actions' => [
        'delete' => ['POST'],
        'approve' => ['POST'],
        'approve-ticket' => ['POST'],
        'cancel-ticket' => ['POST'],
        'get-elapsed-time' => ['GET'],
    ],

            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find(),
            // ... other configurations ...
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionView()
    {
        $companyEmail = Yii::$app->user->identity->company_email;
        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find()->where(['company_email' => $companyEmail]),
        ]);

        $hasResults = $dataProvider->getCount() > 0;

        // Add this logging
        foreach ($dataProvider->models as $ticket) {
            Yii::info("Ticket ID: {$ticket->id}, Status: '{$ticket->status}'", 'ticket');
        }

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'companyEmail' => $companyEmail,
            'hasResults' => $hasResults,
        ]);
    }
    
    

    public function actionCreate()
    {
        // Check if the current user is an admin
        if (Yii::$app->user->identity->isAdmin()) {
            throw new ForbiddenHttpException('Admins are not allowed to create tickets.');
        }

        $model = new Ticket();
    
        // Get the logged-in user's company email
        $userCompanyEmail = Yii::$app->user->identity->company_email;
    
        if ($model->load(Yii::$app->request->post())) {
            // Set the company_email to the logged-in user's company email
            $model->company_email = $userCompanyEmail;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Ticket created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            // Pre-fill the company_email field
            $model->company_email = $userCompanyEmail;
        }
    
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    private function updateCompanyEmail($companyEmail)
    {
        // Assuming you have a User model and a 'users' table
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->company_email = $companyEmail;
            if (!$user->save()) {
                Yii::error('Failed to update company email: ' . print_r($user->errors, true), __METHOD__);
            }
        } else {
            // If the user is not logged in, you might want to store this in a session or handle it differently
            Yii::$app->session->set('company_email', $companyEmail);
        }
    }
    public function actionAssign($id)
    {
        $ticket = $this->findModel($id);
        $developers = Developer::find()->all();
        $isAssigned = !empty($ticket->assigned_to);

        if (Yii::$app->request->isPost) {
            $developerId = Yii::$app->request->post('Ticket')['assigned_to'];
            $developer = Developer::findOne($developerId);

            if (!$developer) {
                Yii::error("Developer not found with ID: $developerId", 'ticket');
                Yii::$app->session->setFlash('error', "Developer not found with ID: $developerId");
                return $this->refresh();
            }

            $ticket->assigned_to = $developer->id;

            if ($ticket->save()) {
                Yii::$app->session->setFlash('success', "Ticket successfully " . ($isAssigned ? "reassigned" : "assigned") . " to {$developer->name}.");
                return $this->redirect(['view', 'id' => $id]);
            } else {
                Yii::error("Failed to assign ticket. Errors: " . json_encode($ticket->errors), 'ticket');
                Yii::$app->session->setFlash('error', 'Failed to assign ticket: ' . json_encode($ticket->errors));
            }
        }

        return $this->render('assign', [
            'ticket' => $ticket,
            'developers' => $developers,
            'isAssigned' => $isAssigned,
        ]);
    }
    public function actionApprove()
    {
        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);
        if ($ticket) {
            $ticket->status = 'closed';
            $ticket->closed_at = date('Y-m-d H:i:s');
            if ($ticket->save()) {
                return $this->asJson(['success' => true]);
            }
        }
        return $this->asJson(['success' => false, 'message' => 'Failed to approve ticket']);
    }
    
    
    public function actionCancel()
    {
        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);
        if ($ticket) {
            $ticket->status = 'closed';
            $ticket->closed_at = date('Y-m-d H:i:s');
            if ($ticket->save()) {
                return $this->asJson(['success' => true]);
            }
        }
        return $this->asJson(['success' => false, 'message' => 'Failed to cancel ticket']);
    }
    

   
    protected function findModel($id)
    {
        if (($model = Ticket::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetElapsedTime($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ticket = $this->findModel($id);
        return [
            'elapsed_time' => $ticket->getElapsedTime(),
        ];
    }

    public function actionClose()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        
        if ($model->status !== 'Closed') {
            $model->status = 'Closed';
            $model->closed_at = new \yii\db\Expression('NOW()');
            if ($model->save()) {
                return ['success' => true];
            }
        }
        
        return ['success' => false];
    }

    public function actionGetTimeSpent($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $ticket = Ticket::findOne($id);
        
        if (!$ticket || $ticket->status !== Ticket::STATUS_CLOSED) {
            return ['success' => false];
        }
        
        $createdAt = new \DateTime($ticket->created_at);
        $closedAt = new \DateTime($ticket->closed_at); // Assuming you have a closed_at field
        $interval = $createdAt->diff($closedAt);
        
        $timeSpent = '';
        if ($interval->y > 0) {
            $timeSpent .= $interval->y . ' year' . ($interval->y > 1 ? 's ' : ' ');
        }
        if ($interval->m > 0) {
            $timeSpent .= $interval->m . ' month' . ($interval->m > 1 ? 's ' : ' ');
        }
        if ($interval->d > 0) {
            $timeSpent .= $interval->d . ' day' . ($interval->d > 1 ? 's ' : ' ');
        }
        if ($interval->h > 0) {
            $timeSpent .= $interval->h . ' hour' . ($interval->h > 1 ? 's ' : ' ');
        }
        if ($interval->i > 0) {
            $timeSpent .= $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        }
        
        return [
            'success' => true,
            'timeSpent' => trim($timeSpent) ?: 'Less than a minute',
        ];
    }

    public function actionOpenIssue()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);

        if ($ticket && $ticket->status === 'Closed') {
            // Logic to open an issue for this ticket
            // This might involve creating a new ticket, changing the status, or adding a flag
            // For example:
            $ticket->status = 'Reopened';
            if ($ticket->save()) {
                return ['success' => true];
            }
        }

        return ['success' => false];
    }

    public function actionReopen()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        
        $ticket = Ticket::findOne($id);

        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.'];
        }

        if ($ticket->status !== Ticket::STATUS_CLOSED) {
            return ['success' => false, 'message' => 'Only closed tickets can be reopened.'];
        }

        $oldStatus = $ticket->status;
        $ticket->status = Ticket::STATUS_PENDING;  // Set to 'pending' when reopening
        $ticket->action = 'reopen';

        Yii::info("Attempting to reopen ticket ID: $id. Old status: $oldStatus, New status: {$ticket->status}", 'ticket');

        if ($ticket->save()) {
            Yii::info("Successfully reopened ticket ID: $id", 'ticket');
            return ['success' => true, 'message' => "Ticket successfully reopened. Old status: $oldStatus, New status: pending"];
        } else {
            Yii::error("Failed to reopen ticket ID: $id. Errors: " . json_encode($ticket->errors), 'ticket');
            return [
                'success' => false, 
                'message' => 'Failed to reopen the ticket. Errors: ' . json_encode($ticket->errors)
            ];
        }
    }
}
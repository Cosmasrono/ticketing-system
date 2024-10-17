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
 
use app\models\Client;

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
            'query' => Ticket::find()->where(['company_email' => Yii::$app->user->identity->company_email]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }


       public function actionView($id = null)
    {
        if ($id === null) {
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        
        // Check if the ticket belongs to the current user
        if ($model->company_email !== Yii::$app->user->identity->company_email) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to view this ticket.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }
    

    public function actionCreate()
    {
        if (!$this->isClient()) {
            Yii::$app->session->setFlash('error', 'Only registered customers are allowed to create tickets.');
            return $this->redirect(['site/index']); // or wherever you want to redirect non-clients
        }

        $model = new Ticket();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ticket created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
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
        $ticket = Ticket::findOne($id);
        if (!$ticket) {
            Yii::$app->session->setFlash('error', "Ticket with ID {$id} not found.");
            return $this->redirect(['site/admin']); // Redirect back to the admin page
        }

        $developers = Developer::find()
            ->where(['!=', 'id', $ticket->assigned_to])
            ->all();

        if (Yii::$app->request->isPost) {
            $developerId = Yii::$app->request->post('Ticket')['assigned_to'];
            $developer = Developer::findOne($developerId);
            
            if ($developer) {
                $ticket->assigned_to = $developer->id;
                $ticket->status = Ticket::STATUS_PENDING; // or whatever status you want after reassignment
                
                if ($ticket->save()) {
                    Yii::$app->session->setFlash('success', 'Ticket successfully reassigned to ' . $developer->name);
                    return $this->redirect(['site/admin']);
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to reassign ticket: ' . json_encode($ticket->errors));
                }
            } else {
                Yii::$app->session->setFlash('error', 'Selected developer not found.');
            }
        }

        return $this->render('assign', [
            'ticket' => $ticket,
            'developers' => $developers,
        ]);
    }




    
    public function actionApprove()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->post('id');
            $closed_at = Yii::$app->request->post('closed_at');
            
            if (!$id) {
                throw new \yii\web\BadRequestHttpException('Ticket ID is required.');
            }
            
            $ticket = Ticket::findOne($id);
            if (!$ticket) {
                throw new \yii\web\NotFoundHttpException('Ticket not found.');
            }
            
            if ($ticket->status === 'cancelled') {
                return [
                    'success' => false,
                    'message' => 'Cannot approve a cancelled ticket.',
                    'alert' => 'This ticket has been cancelled and cannot be approved.'
                ];
            }
            
            $ticket->status = 'approved';
            $ticket->closed_at = $closed_at;
            
            if (!$ticket->save()) {
                throw new \yii\web\ServerErrorHttpException('Failed to approve ticket: ' . json_encode($ticket->errors));
            }
            
            return [
                'success' => true,
                'message' => 'Ticket approved successfully.',
                'alert' => 'The ticket has been approved.'
            ];
        } catch (\Exception $e) {
            \Yii::error('Error in actionApprove: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'alert' => 'An error occurred while processing your request.'
            ];
        }
    }
    
    
      
    public function actionCancel()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);
        
        if ($ticket && $ticket->status === 'pending') {
            $ticket->status = 'cancelled';
            $ticket->closed_at = date('Y-m-d H:i:s');
            
            if ($ticket->save()) {
                return [
                    'success' => true,
                    'message' => 'Ticket successfully cancelled.',
                    'alert' => 'Ticket has been cancelled successfully.'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => $ticket ? 'Failed to cancel ticket. Only pending tickets can be cancelled.' : 'Ticket not found.',
            'alert' => $ticket ? 'Unable to cancel ticket. Please ensure it is in pending status.' : 'Ticket not found. Unable to process cancellation.'
        ];
    }
    

   
    protected function findModel($id)
    {
        $model = Ticket::findOne([
            'id' => $id,
            'company_email' => Yii::$app->user->identity->company_email
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('The requested ticket does not exist or you do not have permission to view it.');
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket not found'
            ];
        }

        if ($ticket->status !== Ticket::STATUS_CLOSED && $ticket->status !== Ticket::STATUS_DELETED) {
            return [
                'success' => false,
                'message' => 'Only closed or deleted tickets can be reopened'
            ];
        }

        $ticket->status = Ticket::STATUS_PENDING; // or whatever status you use for reopened tickets
        
        if ($ticket->save()) {
            return [
                'success' => true,
                'message' => 'Ticket successfully reopened'
            ];
        } else {
            Yii::error('Failed to reopen ticket: ' . json_encode($ticket->errors), 'ticket');
            return [
                'success' => false,
                'message' => 'Failed to reopen ticket: ' . json_encode($ticket->errors)
            ];
        }
    }

  

    private function isClient()
    {
        return Client::find()->where(['company_email' => Yii::$app->user->identity->company_email])->exists();
    }

    public function actionGetAssignInfo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $ticket = Ticket::findOne($id);
        if (!$ticket) {
            return ['canAssign' => false, 'message' => 'Ticket not found.'];
        }
        
        if ($ticket->status !== 'escalated') {
            return ['canAssign' => false, 'message' => 'Only escalated tickets can be assigned or reassigned.'];
        }
        
        return ['canAssign' => true];
    }

    public function actionSoftDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket not found'
            ];
        }

        $ticket->status = Ticket::STATUS_DELETED;
        
        if ($ticket->save()) {
            return [
                'success' => true,
                'message' => 'Ticket successfully deleted'
            ];
        } else {
            Yii::error('Failed to delete ticket: ' . json_encode($ticket->errors), 'ticket');
            return [
                'success' => false,
                'message' => 'Failed to delete ticket: ' . json_encode($ticket->errors)
            ];
        }
    }
}

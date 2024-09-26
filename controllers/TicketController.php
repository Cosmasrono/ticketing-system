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

    public function actionView($id = null)
    {
        $companyEmail = Yii::$app->user->identity->company_email;
        
        $query = Ticket::find()->where(['company_email' => $companyEmail]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        $hasResults = $query->exists();

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'hasResults' => $hasResults,
            'companyEmail' => $companyEmail,
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
        $model = $this->findModel($id);
        $developers = Developer::find()->all();

        // Determine if the ticket is already assigned
        $isAssigned = !empty($model->assigned_to);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return [
                    'success' => true,
                    'message' => 'Ticket assigned successfully.',
                    'redirectUrl' => Yii::$app->request->referrer ?: ['index'],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to assign ticket.',
                    'errors' => $model->errors,
                ];
            }
        }

        return $this->render('assign', [
            'model' => $model,
            'developers' => $developers,
            'isAssigned' => $isAssigned,
        ]);
    }
    public function actionApprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        try {
            $ticketId = Yii::$app->request->post('id');
            $ticket = Ticket::findOne($ticketId);
    
            if (!$ticket) {
                return ['success' => false, 'message' => 'Ticket not found.'];
            }
    
            Yii::info("Ticket found: " . json_encode($ticket), __METHOD__);
    
            if ($ticket->status === 'pending') {
                $ticket->status = 'approved'; // Change status to approved
                if ($ticket->save()) {
                    return ['success' => true];
                } else {
                    Yii::error("Failed to save ticket: " . json_encode($ticket->getErrors()), __METHOD__);
                    return ['success' => false, 'message' => 'Failed to save ticket.'];
                }
            } else {
                return ['success' => false, 'message' => 'Ticket cannot be approved. Current status: ' . $ticket->status];
            }
        } catch (\Exception $e) {
            Yii::error("Exception occurred: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Internal Server Error.'];
        }
    }
    
    
    

    public function actionCancel($id)
    {
        $ticket = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            if ($ticket->status === 'approved') {
                Yii::$app->session->setFlash('error', 'Approved tickets cannot be cancelled.');
            } elseif ($ticket->status !== 'cancelled') {
                $ticket->status = 'cancelled';
                
                if ($ticket->save()) {
                    Yii::$app->session->setFlash('success', 'Ticket cancelled successfully');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to cancel ticket: ' . json_encode($ticket->errors));
                }
            } else {
                Yii::$app->session->setFlash('error', 'Ticket is already cancelled');
            }
            
            return $this->redirect(['/site/admin']);
        }
        
        throw new BadRequestHttpException('Invalid request method.');
    }
    

   
    protected function findModel($id)
    {
        if (($model = Ticket::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested ticket does not exist.');
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        if ($model->status === 'Closed' && $model->closed_at !== null) {
            $createdAt = new \DateTime($model->created_at);
            $closedAt = new \DateTime($model->closed_at);
            $interval = $createdAt->diff($closedAt);
            
            $timeSpent = sprintf(
                '%d days, %02d:%02d:%02d',
                $interval->days,
                $interval->h,
                $interval->i,
                $interval->s
            );
            
            return ['success' => true, 'timeSpent' => $timeSpent];
        }
        
        return ['success' => false];
    }
}
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
    ],

            ],
        ];
    }


//     public function actionIndex()
// {
//     $query = Ticket::find();

//     $dataProvider = new ActiveDataProvider([
//         'query' => $query,
//     ]);

//     return $this->render('index', [
//         'dataProvider' => $dataProvider,
//     ]);
// }

    public function actionView()
    {
        $user = Yii::$app->user->identity;
        $companyEmail = $user->company_email;

        $query = Ticket::find()->where(['company_email' => $companyEmail]);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
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

    public function actionAssign($id = null)
    {
        if ($id === null) {
            $id = Yii::$app->request->post('id');
        }
        
        $ticket = $this->findModel($id);
    
        $developers = Developer::find()->all();
    
        if ($ticket->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post('Ticket');
    
            if (isset($postData['assigned_to'])) {
                $newAssignedTo = $postData['assigned_to'];
                $newDeveloper = Developer::findOne($newAssignedTo);
    
                if (!$newDeveloper) {
                    Yii::$app->session->setFlash('error', 'Selected developer not found.');
                } else {
                    $ticket->assigned_to = $newAssignedTo;
                    $ticket->developer_id = $newAssignedTo;
                    if ($ticket->save()) {
                        Yii::$app->session->setFlash('success', 'Ticket assigned successfully.');
                        return $this->refresh();
                    } else {
                        Yii::$app->session->setFlash('error', 'Failed to assign ticket.');
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', 'Developer not selected.');
            }
        }
    
        return $this->render('assign', [
            'model' => $ticket,
            'developers' => $developers,
            'isAssigned' => !empty($ticket->assigned_to),
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
        $ticket = Ticket::findOne($id);

        if ($ticket) {
            $ticket->status = 'cancelled'; // Change status to 'cancelled'
            if ($ticket->save(false, ['status'])) { // Only save the status field
                Yii::$app->session->setFlash('success', 'Ticket cancelled successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to cancel the ticket.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ticket not found.');
        }

        return $this->redirect(['view', 'id' => $ticket->id]);
    }
    

    
    // public function actionView($id = null)
    // {
    //     if (Yii::$app->user->isGuest) {
    //         return $this->redirect(['site/login']);
    //     }

    //     $companyEmail = Yii::$app->user->identity->company_email;
        
    //     if ($id === null) {
    //         $query = Ticket::find()
    //             ->where(['company_email' => $companyEmail])
    //             ->orderBy(['created_at' => SORT_DESC]);
    //         $dataProvider = new ActiveDataProvider([
    //             'query' => $query,
    //             'sort' => [
    //                 'defaultOrder' => ['created_at' => SORT_DESC],
    //             ],
    //             'pagination' => [
    //                 'pageSize' => 50,
    //             ],
    //         ]);
    //         return $this->render('index', [
    //             'dataProvider' => $dataProvider,
    //             'companyEmail' => $companyEmail,
    //         ]);
    //     } else {
    //         try {
    //             $model = $this->findModel($id);
                
    //             if ($model->company_email !== $companyEmail) {
    //                 throw new ForbiddenHttpException('You are not allowed to view this ticket.');
    //             }
                
    //             return $this->render('view', [
    //                 'model' => $model,
    //             ]);
    //         } catch (NotFoundHttpException $e) {
    //             Yii::error('Ticket not found: ' . $id, 'ticket.view');
    //             throw $e;
    //         } catch (\Exception $e) {
    //             Yii::error('Error while fetching ticket: ' . $e->getMessage(), 'ticket.view');
    //             return $this->redirect(['view']);
    //         }
    //     }
    // }

    protected function findModel($id)
    {
        if (($model = Ticket::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested ticket does not exist.');
    }
}
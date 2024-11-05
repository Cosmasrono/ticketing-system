<?php

namespace app\controllers;

use Yii;
use app\models\Developer;
use app\models\Ticket;
use app\models\TicketSearch;
use app\models\User;
use app\models\Invitation; // Make sure to import the Invitation model
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
use yii\db\Query;
use yii\filters\Cors;
 
 

class TicketController extends Controller
{
    private $_moduleIssues = [
        'HR' => ['Employee Onboarding', 'Leave Management', 'Payroll', 'Other'],
        'IT' => ['Hardware', 'Software', 'Network', 'Other'],
        'Finance' => ['Invoicing', 'Payments', 'Expenses', 'Other'],
        
    ];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'create', 'view', 'update', 'delete'], // specify which actions to check
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Deny access if user has admin role
                            return !Yii::$app->user->can('admin');
                        },
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException('Administrators do not have access to tickets.');
                        }
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
        'close' => ['post'],
    ],

            ],
            'corsFilter' => [
                'class' => Cors::className(),
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Administrators do not have access to tickets.');
        }

        return true;
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
        $model = new Ticket();
        $user = Yii::$app->user->identity;

        // Get the invitation for the current user
        $invitation = Invitation::findOne([
            'company_email' => $user->company_email
        ]);

        if (!$invitation) {
            Yii::$app->session->setFlash('error', 'No invitation found. Please contact support.');
            return $this->redirect(['site/index']);
        }

        // Set the module from invitation
        $model->module = $invitation->module;

        if ($model->load(Yii::$app->request->post())) {
            // Get current user's details
            $currentUser = User::findOne($user->id);
            
            // Explicitly set all required fields
            $model->created_by = $currentUser->id;
            $model->company_name = $currentUser->company_name;  // Set company name directly
            $model->company_email = $currentUser->company_email;
            $model->status = Ticket::STATUS_PENDING;
            $model->created_at = time();

            // Debug log before save
            Yii::debug([
                'Setting company name' => $model->company_name,
                'User company name' => $currentUser->company_name,
            ]);

            if ($model->save()) {
                // Debug log after successful save
                Yii::debug([
                    'Ticket saved' => $model->id,
                    'Company name saved' => $model->company_name,
                ]);
                
                Yii::$app->session->setFlash('success', 'Ticket created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error([
                    'Failed to save ticket' => $model->errors,
                    'Attempted company name' => $model->company_name,
                ], 'ticket');
                
                Yii::$app->session->setFlash('error', 'There was an error creating the ticket: ' . print_r($model->errors, true));
            }
        }

        // Get module-specific issues
        $currentIssues = isset($this->_moduleIssues[$model->module]) ? 
            $this->_moduleIssues[$model->module] : [];

        $recentTickets = Ticket::find()
            ->where(['created_by' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('create', [
            'model' => $model,
            'moduleIssues' => $this->_moduleIssues,
            'currentIssues' => $currentIssues,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Gets issues for a specific module or all modules
     * @param string|null $module
     * @return array
     */
    private function getModuleIssues($module = null)
    {
        if ($module !== null && isset($this->_moduleIssues[$module])) {
            return $this->_moduleIssues[$module];
        }
        return $this->_moduleIssues;
    }

    private function getUserRole($user)
    {
        if ($user->isAdmin()) {
            return 'admin';
        } elseif ($user->isDeveloper()) {
            return 'developer';
        } else {
            return 'user';
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Set company name if it's not provided
            if (empty($model->company_name)) {
                $model->company_name = 'Default Company'; // Or however you want to set a default
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    private function getInvitationModule()
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            $invitation = Invitation::findOne(['company_email' => $user->company_email]);
            if ($invitation) {
                return $invitation->module;
            }
        }
        return null;
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
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        $developers = User::find()
            ->where(['role' => User::ROLE_DEVELOPER]);
        
        // Handle form submission
        if ($ticket->load(Yii::$app->request->post())) {
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Store the original status
                $originalStatus = $ticket->status;
                
                // First save the assigned_to field
                if ($ticket->save(true, ['assigned_to'])) {
                    // Check if it's an escalated ticket
                    if ($originalStatus === Ticket::STATUS_ESCALATED) {
                        // Direct database update to ensure status change
                        $success = Yii::$app->db->createCommand()
                            ->update('ticket', // replace with your actual table name
                                ['status' => 'reassigned'],
                                ['id' => $ticket->id]
                            )->execute();

                        if (!$success) {
                            throw new \Exception('Failed to update ticket status');
                        }

                        // Refresh the ticket model to get the new status
                        $ticket->refresh();
                    }
                    
                    $transaction->commit();
                    
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return [
                            'success' => true,
                            'message' => 'Developer reassigned successfully',
                            'newStatus' => $ticket->status,
                            'ticketId' => $ticket->id
                        ];
                    }

                    Yii::$app->session->setFlash('success', 'Developer reassigned successfully.');
                    return $this->redirect(['view', 'id' => $ticket->id]);
                } else {
                    throw new \Exception('Failed to save assigned_to: ' . json_encode($ticket->errors));
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Assignment error: ' . $e->getMessage());
                
                if (Yii::$app->request->isAjax) {
                    return [
                        'success' => false,
                        'message' => 'Failed to reassign developer: ' . $e->getMessage()
                    ];
                }
                
                Yii::$app->session->setFlash('error', 'Failed to reassign developer: ' . $e->getMessage());
            }
        }

        $developers = $developers->all();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_assign_form', [
                'ticket' => $ticket,
                'developers' => $developers,
            ]);
        }

        return $this->render('assign', [
            'ticket' => $ticket,
            'developers' => $developers,
        ]);
    }


    private function getDevelopers()
    {
        $developers = User::find()->where(['role' => 'developer'])->all();
        return ArrayHelper::map($developers, 'id', 'name'); // Assuming 'name' is the field for the developer's name
    }


    public function actionApprove()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $id = Yii::$app->request->post('id');
            
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'Ticket ID is required'
                ];
            }

            $ticket = Ticket::findOne($id);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket not found'
                ];
            }

            // Update the status to approved
            $ticket->status = Ticket::STATUS_APPROVED;
            
            if ($ticket->save()) {
                return [
                    'success' => true,
                    'message' => 'Ticket approved successfully'
                ];
            } else {
                Yii::error('Failed to approve ticket: ' . json_encode($ticket->errors));
                return [
                    'success' => false,
                    'message' => 'Failed to approve ticket: ' . json_encode($ticket->errors)
                ];
            }

        } catch (\Exception $e) {
            Yii::error('Error in actionApprove: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while approving the ticket'
            ];
        }
    }
    
    
    
    
       
    public function actionCancel()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($id);
        
            if ($ticket && $ticket->status === Ticket::STATUS_PENDING) {
            $ticket->status = Ticket::STATUS_CANCELLED;
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
        $ticket = Ticket::findOne($id);
        
        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Ticket not found'
            ];
        }

        if ($ticket->status === 'closed') {
            return [
                'success' => false,
                'message' => 'Ticket is already closed'
            ];
        }

        try {
            $ticket->status = 'closed';
            $ticket->closed_at = time(); // Use current Unix timestamp instead of Expression
            $ticket->closed_by = Yii::$app->user->id;
            
            if ($ticket->save()) {
                return [
                    'success' => true,
                    'message' => 'Ticket closed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to close ticket: ' . json_encode($ticket->errors)
                ];
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage()); // Log the error
            return [
                'success' => false,
                'message' => 'An error occurred while closing the ticket'
            ];
        }
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
        
        try {
            $id = Yii::$app->request->post('id');
            
            if (empty($id)) {
                throw new \Exception('Ticket ID is required');
            }
            
            $ticket = Ticket::findOne($id);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            // Change status to reopen using the constant
            $ticket->status = Ticket::STATUS_REOPEN;
            
            if (!$ticket->save()) {
                $errors = json_encode($ticket->errors);
                Yii::error("Failed to reopen ticket #$id. Errors: $errors");
                throw new \Exception("Failed to reopen ticket: $errors");
            }

            return [
                'success' => true,
                'message' => 'Ticket reopened successfully'
            ];

        } catch (\Exception $e) {
            Yii::error('Error reopening ticket: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDelete($id)
    {
        $ticket = $this->findModel($id);
        
        // Check if the current user is the owner of the ticket
        if (Yii::$app->user->id != $ticket->created_by) {
            Yii::$app->session->setFlash('error', 'You can only delete your own tickets.');
            return $this->redirect(['index']);
        }

        if ($ticket->delete()) {
            Yii::$app->session->setFlash('success', 'Ticket deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete the ticket.');
        }

        return $this->redirect(['index']);
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

    public function actionDebugRbac()
    {
        $auth = Yii::$app->authManager;
        
        echo "All Roles:\n";
        print_r($auth->getRoles());
        
        echo "\nDeveloper Role:\n";
        $developerRole = $auth->getRole('developer');
        print_r($developerRole);
        
        echo "\nAll Assignments:\n";
        print_r($auth->getAssignments(null));
        
        echo "\nUsers with Developer Role:\n";
        $developerUserIds = $auth->getUserIdsByRole('developer');
        print_r($developerUserIds);
        
        echo "\nAll Users:\n";
        $users = \app\models\User::find()->select(['id', 'username', 'email'])->asArray()->all();
        print_r($users);
    }

    public function actionDebugDevelopers()
    {
        $developers = User::find()
            ->where(['role' => 'developer'])
            ->select(['id', 'username', 'email', 'role'])
            ->asArray()
            ->all();

        echo "Developers in the database:\n";
        print_r($developers);

        echo "\nTotal developers found: " . count($developers);
    }

    public function actionEscalate()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->post('id');
            $currentUserId = Yii::$app->user->id;
            
            if (empty($id)) {
                throw new \Exception('Ticket ID is required');
            }
            
            $ticket = Ticket::findOne($id);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            // Verify the ticket is assigned to the current user
            if ($ticket->assigned_to !== $currentUserId) {
                throw new \Exception('You are not authorized to escalate this ticket');
            }

            // Only update the status
            $ticket->status = 'escalated';
            
            if (!$ticket->save(false, ['status'])) {  // Only save the status field
                Yii::error('Failed to escalate ticket: ' . json_encode($ticket->errors));
                throw new \Exception('Failed to save ticket');
            }

            return [
                'success' => true,
                'message' => 'Ticket escalated successfully'
            ];

        } catch (\Exception $e) {
            Yii::error('Error escalating ticket: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionCheckAccess()
    {
        if (Yii::$app->user->isGuest) {
            return 'Guest user';
        }

        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        $result = [
            'userId' => Yii::$app->user->id,
            'roles' => array_keys($roles),
            'can_access_tickets' => !Yii::$app->user->can('admin') && !Yii::$app->user->can('superadmin'),
        ];

        return $this->asJson($result);
    }

   
}








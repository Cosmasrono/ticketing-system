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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->identity->isSuperAdmin() && $action->id === 'create') {
            throw new ForbiddenHttpException('Super admins are not allowed to create tickets.');
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
    

    public function actionCreate($invitationId = null)
    {
        $model = new Ticket();
        $user = Yii::$app->user->identity;

        if ($invitationId === null) {
            $invitation = Invitation::find()
                ->where(['company_email' => $user->company_email])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if (!$invitation) {
                throw new \yii\web\ForbiddenHttpException('You do not have any valid invitations to create a ticket.');
            }
        } else {
            $invitation = Invitation::findOne($invitationId);
            if (!$invitation) {
                throw new \yii\web\NotFoundHttpException('The invitation was not found.');
            }

            if ($invitation->company_email !== $user->company_email) {
                throw new \yii\web\ForbiddenHttpException('You are not authorized to use this invitation.');
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = $user->id;
            $model->company_email = $user->company_email;
            $model->status = Ticket::STATUS_PENDING;
            // Remove this line: $model->created_at = time();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Ticket created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('Failed to save ticket: ' . json_encode($model->errors), 'ticket');
                Yii::$app->session->setFlash('error', 'There was an error creating the ticket: ' . print_r($model->errors, true));
            }
        } else {
            // Set the initial module value from the invitation
            $model->module = $invitation->module;
        }

        $moduleIssues = $this->getModuleIssues($invitation->module);
        $recentTickets = Ticket::find()
            ->where(['created_by' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('create', [
            'model' => $model,
            'invitation' => $invitation,
            'recentTickets' => $recentTickets,
            'moduleIssues' => $moduleIssues,
        ]);
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

    private function getModuleIssues($module)
    {
        $issues = [
            'HR' => [
                'Payroll Discrepancy',
                'Leave Request',
                'Employee Onboarding',
                'Performance Review',
                'Benefits Inquiry',
            ],
            'IT' => [
                'Network Connectivity',
                'Software Installation',
                'Hardware Malfunction',
                'Account Access',
                'Email Issues',
            ],
            'Finance' => [
                'Invoice Query',
                'Budget Request',
                'Expense Report',
                'Financial Report',
                'Audit Support',
            ],
            'Customer Service' => [
                'Customer Complaint',
                'Refund Request',
                'Product Information',
                'Order Status',
                'Shipping Inquiry',
            ],
            'Marketing' => [
                'Campaign Approval',
                'Content Creation',
                'Social Media Management',
                'Analytics Report',
                'Brand Guidelines',
            ],
            // Add more modules and their issues as needed
        ];

        return $issues[$module] ?? [];
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


    public function actionAssign()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $ticketId = Yii::$app->request->post('ticketId');
            $developerId = Yii::$app->request->post('developerId');
            
            if (!$ticketId || !$developerId) {
                throw new \Exception('Missing required parameters');
            }

            $ticket = Ticket::findOne($ticketId);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            // Keep status as escalated but update assigned developer
            $ticket->assigned_to = $developerId;
            
            if (!$ticket->save()) {
                throw new \Exception('Failed to assign ticket');
            }

            return [
                'success' => true,
                'message' => 'Ticket reassigned successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getDevelopers()
    {
        $developers = User::find()->where(['role' => 'developer'])->all();
        return ArrayHelper::map($developers, 'id', 'name'); // Assuming 'name' is the field for the developer's name
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
            
            if ($ticket->status === Ticket::STATUS_CANCELLED) {
                return [
                    'success' => false,
                    'message' => 'Cannot approve a cancelled ticket.',
                    'alert' => 'This ticket has been cancelled and cannot be approved.'
                ];
            }
            
            $ticket->status = Ticket::STATUS_APPROVED;
            
            // Handle closed_at
            if ($closed_at) {
                $ticket->closed_at = strtotime($closed_at); // Convert to Unix timestamp
            } else {
                $ticket->closed_at = time(); // Set to current time if not provided
            }
            
            // Ensure created_at is set and is an integer
            if (!$ticket->created_at || !is_int($ticket->created_at)) {
                $ticket->created_at = time();
            }
            
            if (!$ticket->save()) {
                Yii::error('Failed to approve ticket. Errors: ' . json_encode($ticket->errors));
                Yii::error('Ticket data: ' . json_encode($ticket->attributes));
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
            
            // Verify the ticket is assigned to the current user
            if ($ticket->assigned_to !== Yii::$app->user->id) {
                throw new \Exception('You are not authorized to close this ticket');
            }

            // Change status to closed
            $ticket->status = Ticket::STATUS_CLOSED;
            $ticket->closed_at = time(); // Set closed timestamp
            
            if (!$ticket->save()) {
                Yii::error('Failed to close ticket: ' . json_encode($ticket->errors));
                throw new \Exception('Failed to save ticket');
            }

            return [
                'success' => true,
                'message' => 'Ticket closed successfully'
            ];

        } catch (\Exception $e) {
            Yii::error('Error closing ticket: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
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
            
            if (empty($id)) {
                throw new \Exception('Ticket ID is required');
            }
            
            $ticket = Ticket::findOne($id);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            // Change status to escalated
            $ticket->status = Ticket::STATUS_ESCALATE;
            
            if (!$ticket->save()) {
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
}








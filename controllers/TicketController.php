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
use yii\queue\Queue;
use app\jobs\SendTicketAssignmentEmail;
use app\models\Notification;
 
 

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
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['@'],  // Allow authenticated users
                    ],
                    [
                        'actions' => ['escalate'],
                        'allow' => true,
                        'roles' => ['developer'],
                        'matchCallback' => function ($rule, $action) {
                            return true; // Allow all developers to access this action
                        }
                    ],
                    // Rule for regular users
                    [
                        'actions' => ['create', 'view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'user';
                        }
                    ],
                    // Rule for admin and superadmin
                    [
                        'actions' => ['approve', 'update', 'delete', 'assign', 'admin', 'view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return in_array(Yii::$app->user->identity->role, ['admin', 'superadmin']);
                        }
                    ],
                    // Rule for developers
                    [
                        'actions' => ['view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'developer';
                        }
                    ],
                    [
                        'actions' => ['close'],
                        'allow' => true,
                        'roles' => ['developer'], // Allow developers to close tickets
                        'matchCallback' => function ($rule, $action) {
                            // Additional check to ensure developer can only close their assigned tickets
                            $ticketId = Yii::$app->request->post('id');
                            $ticket = Ticket::findOne($ticketId);
                            return $ticket && $ticket->assigned_to === Yii::$app->user->id;
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', 'You are not authorized to perform this action.');
                    return $this->redirect(['site/index']);
                }
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'approve' => ['POST'],
                    'delete' => ['POST'],
                    'assign' => ['POST', 'GET'],
                    'close' => ['POST'],
                    'escalate' => ['POST'],
                    'cancel' => ['POST'],
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

    /**
     * Finds the Ticket model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ticket the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ticket::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested ticket does not exist.');
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
    
        // Find the invitation for the current user
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
            // Explicitly set the current timestamp and other fields
            $model->created_at = date('Y-m-d H:i:s');
            $model->created_by = $user->id;
            $model->company_name = $user->company_name;
            $model->company_email = $user->company_email;
            $model->status = Ticket::STATUS_PENDING;
    
            if ($model->save()) {
                // Call the method to send email notification to admin
                $htmlContent = $this->renderPartial('@app/mail/ticketNotification', [
                    'username' => $user->username,
                    'company_name' => $model->company_name,
                    'description' => $model->description,
                    'ticketId' => $model->id,
                    'module' => $model->module,
                    'issue' => $model->issue
                ]);

                $emailSent = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['senderEmail'] => 'Ticket System'])
                    ->setTo([Yii::$app->params['adminEmail'] => 'Admin'])
                    ->setReplyTo([$user->company_email => $user->username])
                    ->setSubject("New Ticket #{$model->id} - {$model->company_name} - {$model->module}")
                    ->setHtmlBody($htmlContent)
                    ->send();

                Yii::$app->session->setFlash(
                    $emailSent ? 'success' : 'warning',
                    $emailSent 
                        ? 'Ticket created successfully and email notification sent.'
                        : 'Ticket created but email notification failed.'
                );

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'There was an error creating the ticket: ' . print_r($model->errors, true));
            }
        }
    
        // Get module-specific issues and recent tickets
        $currentIssues = isset($this->_moduleIssues[$model->module]) ? $this->_moduleIssues[$model->module] : [];
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
     * Sends an email notification to the admin when a new ticket is raised.
     * @param Ticket $model The ticket model
     * @param User $user The current user raising the ticket
     */
    protected function sendAdminNotification($model, $user)
    {
        try {
            Yii::$app->mailer->compose('ticketNotification', [
                'username' => $user->username,
                'description' => $model->description,
            ])
            ->setFrom([$user->company_email => $user->username])  // Use user's email and name
            ->setTo(Yii::$app->params['adminEmail'])  // Admin email from params
            ->setSubject('New Ticket Raised')
            ->send();
        } catch (\Exception $e) {
            Yii::error('Email failed to send: ' . $e->getMessage(), 'email');
        }
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
    
    
    


    // public function actionAssign($id)
    // {
    //     $ticket = Ticket::findOne($id);
    //     if (!$ticket) {
    //         throw new NotFoundHttpException('The requested ticket does not exist.');
    //     }

    //     $developers = User::find()
    //         ->where(['role' => User::ROLE_DEVELOPER]);
        
    //     // Handle form submission
    //     if ($ticket->load(Yii::$app->request->post())) {
    //         // Start transaction
    //         $transaction = Yii::$app->db->beginTransaction();
    //         try {
    //             // Store the original status
    //             $originalStatus = $ticket->status;
                
    //             // First save the assigned_to field
    //             if ($ticket->save(true, ['assigned_to'])) {
    //                 // Check if it's an escalated ticket
    //                 if ($originalStatus === Ticket::STATUS_ESCALATED) {
    //                     // Direct database update to ensure status change
    //                     $success = Yii::$app->db->createCommand()
    //                         ->update('ticket', // replace with your actual table name
    //                             ['status' => 'reassigned'],
    //                             ['id' => $ticket->id]
    //                         )->execute();

    //                     if (!$success) {
    //                         throw new \Exception('Failed to update ticket status');
    //                     }

    //                     // Refresh the ticket model to get the new status
    //                     $ticket->refresh();
    //                 }
                    
    //                 $transaction->commit();
                    
    //                 if (Yii::$app->request->isAjax) {
    //                     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //                     return [
    //                         'success' => true,
    //                         'message' => 'Developer reassigned successfully',
    //                         'newStatus' => $ticket->status,
    //                         'ticketId' => $ticket->id
    //                     ];
    //                 }

    //                 Yii::$app->session->setFlash('success', 'Developer reassigned successfully.');
    //                 return $this->redirect(['view', 'id' => $ticket->id]);
    //             } else {
    //                 throw new \Exception('Failed to save assigned_to: ' . json_encode($ticket->errors));
    //             }
    //         } catch (\Exception $e) {
    //             $transaction->rollBack();
    //             Yii::error('Assignment error: ' . $e->getMessage());
                
    //             if (Yii::$app->request->isAjax) {
    //                 return [
    //                     'success' => false,
    //                     'message' => 'Failed to reassign developer: ' . $e->getMessage()
    //                 ];
    //             }
                
    //             Yii::$app->session->setFlash('error', 'Failed to reassign developer: ' . $e->getMessage());
    //         }
    //     }

    //     $developers = $developers->all();

    //     if (Yii::$app->request->isAjax) {
    //         return $this->renderAjax('_assign_form', [
    //             'ticket' => $ticket,
    //             'developers' => $developers,
    //         ]);
    //     }

    //     return $this->render('assign', [
    //         'ticket' => $ticket,
    //         'developers' => $developers,
    //     ]);
    // }




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
                // Get the assigned developer's details
                $developer = User::findOne($ticket->assigned_to);
                
                // Send email notification to the assigned developer
                $htmlContent = $this->renderPartial('@app/mail/assignmentNotification', [
                    'developer_name' => $developer->username,
                    'ticket_id' => $ticket->id,
                    'company_name' => $ticket->company_name,
                    'description' => $ticket->description,
                    'module' => $ticket->module,
                    'issue' => $ticket->issue
                ]);

                $emailSent = Yii::$app->mailer->compose()
                    ->setFrom([Yii::$app->params['senderEmail'] => 'Ticket System'])
                    ->setTo([$developer->company_email => $developer->username])
                    ->setSubject("Ticket #{$ticket->id} - {$ticket->company_name} - {$ticket->module}")
                    ->setHtmlBody($htmlContent)
                    ->send();

                // Check if it's an escalated ticket
                if ($originalStatus === Ticket::STATUS_ESCALATED) {
                    // Direct database update to ensure status change
                    $success = Yii::$app->db->createCommand()
                        ->update('ticket',
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
                        'message' => 'Developer reassigned successfully' . ($emailSent ? ' and notified' : ''),
                        'newStatus' => $ticket->status,
                        'ticketId' => $ticket->id
                    ];
                }

                Yii::$app->session->setFlash('success', 
                    'Developer reassigned successfully' . ($emailSent ? ' and notified' : '')
                );
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->post('id');
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'Ticket ID is required'
                ];
            }

            $ticket = $this->findModel($id);
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket not found'
                ];
            }

            // Validate user permissions
            if (!Yii::$app->user->can('developer')) {
                return [
                    'success' => false,
                    'message' => 'Only developers can escalate tickets'
                ];
            }

            // Validate ticket assignment
            if ($ticket->assigned_to != Yii::$app->user->id) {
                return [
                    'success' => false,
                    'message' => 'You can only escalate tickets assigned to you'
                ];
            }

            // Check ticket status
            if ($ticket->status === 'escalated') {
                return [
                    'success' => false,
                    'message' => 'Ticket is already escalated'
                ];
            }

            if ($ticket->status === 'closed') {
                return [
                    'success' => false,
                    'message' => 'Cannot escalate closed tickets'
                ];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ticket->status = 'escalated';
                $ticket->escalated_at = date('Y-m-d H:i:s');

                if ($ticket->save()) {
                    $transaction->commit();
                    return [
                        'success' => true,
                        'message' => 'Ticket has been escalated successfully'
                    ];
                }

                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to escalate ticket'
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Yii::error('Error in escalate action: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while escalating the ticket'
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

    public function actionAdmin()
    {
        // Get current user's role
        $currentUserRole = Yii::$app->user->identity->role;

        // Create query
        $query = Ticket::find()
            ->orderBy(['created_at' => SORT_DESC]); // Sort by created_at in descending order

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Adjust page size as needed
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('admin', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionClose()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'Ticket ID is required'
            ];
        }

        try {
            $model = $this->findModel($id);
            
            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            
            $model->status = 'closed';
            $model->closed_at = date('Y-m-d H:i:s');
            
            if ($model->save()) {
                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Ticket closed successfully'
                ];
            } else {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to close ticket: ' . implode(', ', $model->getErrorSummary(true))
                ];
            }
            
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollBack();
            }
            
            Yii::error('Error closing ticket: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while closing the ticket: ' . $e->getMessage()
            ];
        }
    }

    public function actionCancel()
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

            $ticket = $this->findModel($id);
            
            // Check if ticket can be cancelled
            if (in_array($ticket->status, ['cancelled', 'closed'])) {
                return [
                    'success' => false,
                    'message' => 'Ticket cannot be cancelled in its current state'
                ];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ticket->status = 'cancelled';
                
                if ($ticket->save()) {
                    $transaction->commit();
                    return [
                        'success' => true,
                        'message' => 'Ticket cancelled successfully'
                    ];
                }
                
                $transaction->rollBack();
                Yii::error('Failed to save ticket: ' . json_encode($ticket->errors));
                return [
                    'success' => false,
                    'message' => 'Failed to save ticket changes'
                ];
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Database error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Yii::error('Error in cancel action: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while cancelling the ticket'
            ];
        }
    }

}








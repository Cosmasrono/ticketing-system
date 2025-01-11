<?php

namespace app\controllers;

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $ticket app\models\Ticket */
/* @var $reason string */


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
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use Cloudinary\Cloudinary;
use app\models\Company;
 
 
 

class TicketController extends Controller
{
    private $_moduleIssues = [
        'Members Portal' => [
            'Login Issues',
            'Registration Problems',
            'Profile Updates',
            'Password Reset',
            'Account Verification',
            'Other'
        ],
        'Mobile App' => [
            'App Crashes',
            'Login Failed',
            'Payment Issues',
            'App Update Problems',
            'Connectivity Issues',
            'Other'
        ],
        'Power BI' => [
            'Report Access',
            'Data Refresh Issues',
            'Dashboard Loading',
            'Visualization Problems',
            'Data Connection',
            'Other'
        ],
        'USSD' => [
            'USSD Crashes',
            'USSD Not Working',
            'Other'
        ],
        'Finance' => [
            'Payment Issues',
            'Account Management',
            'Other'
        ],
        'Credit' => [
            'Credit Issues',
            'Account Management',
            'Other'
        ],
        'General' => [
            'General Issues',
            'Other'
        ],
        'Admin and Security' => [
            'Admin and Security Issues',
            'Other'
        ],
        'HR' => [
            'HR Issues',
            'Other'
        ],
    ];
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['approve', 'cancel', 'assign'], // Include ticket management actions
                'rules' => [
                    // Authenticated user access
                    [
                        'actions' => ['approve', 'cancel', 'assign'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Allow roles admin, super_admin, and roles 1 and 4
                            return in_array((int)Yii::$app->user->identity->role, [1, 4]) || 
                                   Yii::$app->user->identity->role === 'admin' || 
                                   Yii::$app->user->identity->role === 'super_admin';
                        },
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->session->setFlash('error', 'Only authorized users can access tickets.');
                            return Yii::$app->response->redirect(['/site/index']);
                        }
                    ],
                    
                    // Developer Access
                    [
                        'actions' => ['close'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $isDevRole = (int)Yii::$app->user->identity->role === User::ROLE_DEVELOPER;
                            $ticketId = Yii::$app->request->post('id');
                            $ticket = Ticket::findOne($ticketId);
                            return $isDevRole && $ticket && $ticket->assigned_to === Yii::$app->user->id;
                        }
                    ],
    
                    // API Endpoints
                    [
                        'actions' => ['get-issues'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'approve' => ['POST'],
                    'delete' => ['POST', 'GET'],
                    'assign' => ['POST', 'GET'],
                    'close' => ['POST'],
                    'escalate' => ['POST'],
                    'cancel' => ['POST'],
                    'reopen' => ['POST'],
                    'get-issues' => ['POST'],
                    'upload-to-cloudinary' => ['POST'],
                ],
            ],
        ];
    }
    public function actions()
{
    return [
        'error' => [
            'class' => 'yii\web\ErrorAction',
            'view' => '@app/views/site/error'
        ],
    ];
}
    public function beforeAction($action)
{
    if (!parent::beforeAction($action)) {
        return false;
    }

    // Check if user is a super admin and restrict access
    if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'superadmin') {
        Yii::$app->session->setFlash('error', 'Super administrators cannot create or view tickets.');
        return $this->redirect(['site/index']); // Redirect to index or wherever appropriate
    }

    // Additional check for closing tickets
    if ($action->id === 'close') {
        $ticketId = Yii::$app->request->post('id');
        $ticket = Ticket::findOne($ticketId);

        if (!$ticket || $ticket->created_by !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to close this ticket.');
        }
    }

    return true;
}


    public function actionIndex()
    {
        $query = Ticket::find()
            ->select(['ticket.*', 'user.company_name'])
            ->leftJoin('user', 'user.id = ticket.user_id')
            ->where(['ticket.user_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
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

    public function actionView($id)
    {
        $ticket = $this->findModel($id);
        
        // Debug logging
        Yii::debug([
            'ticket_id' => $ticket->id,
            'created_by' => $ticket->created_by,
            'creator_details' => User::findOne($ticket->created_by),
        ], 'ticket-view');
        
        return $this->render('view', [
            'model' => $ticket,
        ]);
    }

    public function actionGetIssues()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $selectedModule = Yii::$app->request->post('module');
            
            // Debug logging
            Yii::info('Received module: ' . $selectedModule);
            
            // Define issues for each module (ensure exact string matching)
            $moduleIssues = [
                'Members Portal' => [
                    'Login Issues',
                    'Password Reset',
                    'Profile Update Problems',
                    'Document Upload Issues',
                    'Payment Gateway Problems',
                    'Account Access',
                    'Form Submission Errors',
                    'Data Display Issues',
                    'Registration Problems',
                    'Account Verification',
                    'Other'
                ],
                'Power BI' => [
                    'Report Loading Issues',
                    'Data Refresh Problems',
                    'Dashboard Access',
                    'Visualization Errors',
                    'Filter Malfunction',
                    'Export Issues',
                    'Performance Problems',
                    'Data Connection Issues',
                    'Report Access',
                    'Other'
                ],
                'Mobile App' => [
                    'App Crashes',
                    'Login Problems',
                    'Data Sync Issues',
                    'Performance Issues',
                    'Feature Not Working',
                    'UI/UX Problems',
                    'Notification Issues',
                    'Update Problems',
                    'Account Issues',
                    'Other'
                ],
                'USSD' => [
                    'USSD Crashes',
                    'USSD Not Working',
                    'Other'
                ],
                'Finance' => [
                    'Payment Issues',
                    'Account Management',
                    'Other'
                ],  
            ];

            // Case-insensitive module matching
            $normalizedModule = null;
            foreach (array_keys($moduleIssues) as $key) {
                if (strtolower($key) === strtolower($selectedModule)) {
                    $normalizedModule = $key;
                    break;
                }
            }
            
            // Debug logging
            Yii::info('Normalized module: ' . ($normalizedModule ?? 'not found'));
            
            $issues = $normalizedModule ? $moduleIssues[$normalizedModule] : [];
            
            // Debug logging
            Yii::info('Found issues: ' . print_r($issues, true));
            
            return [
                'success' => true,
                'issues' => $issues,
                'module' => $selectedModule,
                'debug' => [
                    'receivedModule' => $selectedModule,
                    'normalizedModule' => $normalizedModule,
                    'issuesCount' => count($issues)
                ]
            ];
            
        } catch (\Exception $e) {
            Yii::error('Error in getIssues: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error loading issues',
                'error' => YII_DEBUG ? $e->getMessage() : null
            ];
        }
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

    // private function getInvitationModule()
    // {
    //     $user = Yii::$app->user->identity;
    //     if ($user) {
    //         $invitation = Invitation::findOne(['company_email' => $user->company_email]);
    //         if ($invitation) {
    //             return $invitation->module;
    //         }
    //     }
    //     return null;
    // }

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
            
            // Debug log
            Yii::info('Attempting to approve ticket #' . $id);
            
            $ticket = Ticket::findOne($id);
            
            if (!$ticket) {
                Yii::error('Ticket not found: #' . $id);
                return [
                    'success' => false,
                    'message' => 'Ticket not found.'
                ];
            }

            // Check current status
            Yii::info('Current ticket status: ' . $ticket->status);

            // Validate ticket status
            if ($ticket->status === 'approved') {
                return [
                    'success' => false,
                    'message' => 'Ticket is already approved.'
                ];
            }

            if ($ticket->status === 'cancelled') {
                return [
                    'success' => false,
                    'message' => 'Cannot approve a cancelled ticket.'
                ];
            }

            // Update ticket status only
            $ticket->status = 'approved';

            // Debug log before save
            Yii::info('Attempting to save ticket with data: ' . json_encode($ticket->attributes));

            if (!$ticket->save(false)) {
                Yii::error('Failed to save ticket: ' . json_encode($ticket->errors));
                return [
                    'success' => false,
                    'message' => 'Failed to save ticket: ' . json_encode($ticket->errors)
                ];
            }

            // Log success
            Yii::info('Successfully approved ticket #' . $id);

            return [
                'success' => true,
                'message' => 'Ticket has been approved successfully.'
            ];

        } catch (\Exception $e) {
            // Log the full error
            Yii::error('Error approving ticket: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => YII_DEBUG ? 
                    'Error: ' . $e->getMessage() : 
                    'An error occurred while approving the ticket.'
            ];
        }
    }

    protected function sendApprovalEmail($ticket)
    {
        try {
            $user = User::findOne($ticket->created_by);
            if (!$user) {
                Yii::error('User not found for ticket #' . $ticket->id);
                return false;
            }

            $htmlContent = Yii::$app->controller->renderPartial('//mail/ticket-approved', [
                'ticket' => $ticket,
                'user' => $user
            ]);

            Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($user->email)
                ->setSubject('Ticket #' . $ticket->id . ' Approved')
                ->setHtmlBody($htmlContent)
                ->send();

            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to send approval email: ' . $e->getMessage());
            return false;
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




    // public function actionAssign($id)
    // {
    //     // Set JSON response format for AJAX requests
    //     if (Yii::$app->request->isAjax) {
    //         Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //     }

    //     // Get developers first
    //     $developers = User::find()
    //         ->where(['role' => User::ROLE_DEVELOPER])
    //         ->andWhere(['status' => 10])
    //         ->all();

    //     if (empty($developers)) {
    //         if (Yii::$app->request->isAjax) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'No developers available for assignment.'
    //             ];
    //         }
    //         Yii::$app->session->setFlash('error', 'No developers available for assignment.');
    //         return $this->redirect(['index']);
    //     }

    //     $ticket = Ticket::findOne($id);
    //     if (!$ticket) {
    //         throw new NotFoundHttpException('The requested ticket does not exist.');
    //     }

    //     if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
    //         $transaction = Yii::$app->db->beginTransaction();
    //         try {
    //             $developerId = Yii::$app->request->post('Ticket')['assigned_to'] ?? null;
                
    //             // Validate developer exists in our fetched list
    //             $developerExists = false;
    //             foreach ($developers as $developer) {
    //                 if ($developer->id == $developerId) {
    //                     $developerExists = true;
    //                     break;
    //                 }
    //             }

    //             if (!$developerExists) {
    //                 throw new \Exception('Selected developer is not valid');
    //             }

    //             $originalTicket = Ticket::findOne($ticket->id); // Get the original ticket
    //             $ticket->escalated_to = $developerId;
    //             $ticket->assigned_to = $developerId;    
    //             $ticket->status = 'reassigned';         
    //             $ticket->escalation_comment = $originalTicket->escalation_comment; // Get comment from original ticket
                
    //             if ($ticket->save(false)) {
    //                 // Check if it's an escalated ticket
    //                 if ($ticket->status === 'escalated') {
    //                     $ticket->status = 'reassigned';
    //                     $ticket->save(false);
    //                     $message = 'Developer reassigned successfully';
    //                 } else {
    //                     $message = 'Developer assigned successfully';
    //                 }

    //                 $transaction->commit();
                    
    //                 // Send email notification
    //                 try {
    //                     $emailSent = Yii::$app->mailer->compose('assignmentNotification', [
    //                         'developer_name' => $developer->name,  // Using name
    //                         'ticket_id' => $ticket->id,
    //                         'company_name' => $ticket->company_name,
    //                         'description' => $ticket->description,
    //                         'module' => $ticket->module,
    //                         'issue' => $ticket->issue
    //                     ])
    //                     ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
    //                     ->setTo([$developer->company_email => $developer->name])  // Using name in recipient
    //                     ->setSubject("Ticket Assignment #{$ticket->id} - {$ticket->company_name}")
    //                     ->send();

    //                     if (!$emailSent) {
    //                         Yii::error('Failed to send email notification to developer: ' . $developer->name);
    //                     }
    //                 } catch (\Exception $e) {
    //                     Yii::error('Email sending failed for developer ' . $developer->name . ': ' . $e->getMessage());
    //                     // Continue execution even if email fails
    //                 }

    //                 return [
    //                     'success' => true,
    //                     'message' => $message,
    //                     'newStatus' => $ticket->status,
    //                     'ticketId' => $ticket->id
    //                 ];
    //             }
                
    //             throw new \Exception('Failed to save ticket');
                
    //         } catch (\Exception $e) {
    //             $transaction->rollBack();
    //             return [
    //                 'success' => false,
    //                 'message' => ($ticket->status === 'escalated' ? 'Failed to reassign' : 'Failed to assign') . 
    //                             ' developer: ' . $e->getMessage()
    //             ];
    //         }
    //     }

    //     // For non-AJAX requests, render the form
    //     return $this->render('assign', [
    //         'ticket' => $ticket,
    //         'developers' => $developers
    //     ]);
    // }

    // private function getDevelopers()
    // {
    //     $developers = User::find()->where(['role' => 'developer'])->all();
    //     return ArrayHelper::map($developers, 'id', 'name'); // Assuming 'name' is the field for the developer's name
    // }

 





    private function getDevelopers()
    {
        $developers = User::find()->where(['role' => 'developer'])->all();
        return ArrayHelper::map($developers, 'id', 'name'); // Assuming 'name' is the field for the developer's name
    }

 

    public function actionDelete($id)
    {
        $ticket = $this->findModel($id);
        $user = Yii::$app->user->identity;

        // Check if the user owns this ticket or is an admin
        if ($ticket->created_by !== $user->id && !Yii::$app->user->can('admin')) {
            Yii::$app->session->setFlash('error', 'You do not have permission to delete this ticket.');
            return $this->redirect(['index']);
        }

        try {
            // Delete the ticket
            if ($ticket->delete()) {
                // Delete associated screenshot if exists
                if ($ticket->screenshot) {
                    $uploadPath = Yii::getAlias('@webroot/uploads/');
                    $filePath = $uploadPath . $ticket->screenshot;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                Yii::$app->session->setFlash('success', 'Ticket has been deleted successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to delete the ticket.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'An error occurred while deleting the ticket.');
            Yii::error('Error deleting ticket: ' . $e->getMessage());
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
            $comment = Yii::$app->request->post('comment');
            
            if (empty($comment)) {
                return [
                    'success' => false,
                    'message' => 'Escalation comment is required'
                ];
            }

            $ticket = $this->findModel($id);
            
            // Check if ticket can be escalated
            if ($ticket->status === 'escalated') {
                return [
                    'success' => false,
                    'message' => 'Ticket is already escalated'
                ];
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // Store the current developer's ID in escalated_to before changing status
                $ticket->escalated_to = Yii::$app->user->id; // Store the ID of developer who is escalating
                $ticket->status = 'escalated';
                $ticket->assigned_to = null; // Clear the assigned developer since it's escalated
                
                $ticket->escalation_comment = $comment;
                $ticket->escalated_at = new \yii\db\Expression('NOW()'); // If you have this column
                $ticket->escalated_by = Yii::$app->user->id; // If you have this column
                
                if ($ticket->save(false)) {
                    // Optional: Log the escalation
                    Yii::info("Ticket #{$ticket->id} escalated. Comment: {$comment}", 'ticket');
                    
                    // Optional: Send notification
                    $this->sendEscalationNotification($ticket);
                    
                    $transaction->commit();
                    
                    return [
                        'success' => true,
                        'message' => 'Ticket has been escalated successfully'
                    ];
                }
                
                throw new \Exception('Failed to save ticket');
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("Escalation failed: " . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'An error occurred while escalating the ticket'
            ];
        }
    }

    protected function sendEscalationNotification($ticket)
    {
        // Optional: Send email notification about escalation
        try {
            $adminEmail = Yii::$app->params['adminEmail'];
            
            Yii::$app->mailer->compose()
                ->setFrom(['iansoft.ticketing@gmail.com' => 'Iansoft Ticketing'])
                ->setTo($adminEmail)
                ->setSubject("Ticket #{$ticket->id} Escalated")
                ->setHtmlBody("
                    <h3>Ticket Escalation Notification</h3>
                    <p>Ticket #{$ticket->id} has been escalated.</p>
                    <p><strong>Escalation Comment:</strong></p>
                    <p>{$ticket->escalation_comment}</p>
                    <p><strong>Original Issue:</strong></p>
                    <p>{$ticket->issue}</p>
                    <hr>
                    <p>Please review this escalation as soon as possible.</p>
                ")
                ->send();
        } catch (\Exception $e) {
            Yii::error("Failed to send escalation notification: " . $e->getMessage());
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
        Yii::$app->response->format = Response::FORMAT_JSON;
        
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

            // Check if the current user is the ticket creator
            if ($ticket->created_by !== Yii::$app->user->id) {
                return [
                    'success' => false,
                    'message' => 'You can only close tickets that you created'
                ];
            }

            $ticket->status = 'closed';
            $ticket->closed_at = new \yii\db\Expression('NOW()');
            $ticket->closed_by = Yii::$app->user->id;

            if ($ticket->save(false)) {
                return [
                    'success' => true,
                    'message' => 'Ticket has been closed successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to close ticket'
            ];

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while closing the ticket'
            ];
        }
    }

    public function actionCancel()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->post('id');
            
            if (!$id) {
                Yii::error('Cancel action called without ticket ID');
                return [
                    'success' => false,
                    'message' => 'Ticket ID is required'
                ];
            }

            $ticket = Ticket::findOne($id);
            if ($ticket === null) {
                Yii::error('Attempted to cancel non-existent ticket ID: ' . $id);
                return [
                    'success' => false,
                    'message' => 'Ticket not found'
                ];
            }

            // Log current ticket state
            Yii::info('Attempting to cancel ticket #' . $id . ' Current status: ' . $ticket->status);

            // Check if ticket can be cancelled
            if ($ticket->status === Ticket::STATUS_CANCELLED) {
                return [
                    'success' => false,
                    'message' => 'Ticket is already cancelled'
                ];
            }

            if ($ticket->status === Ticket::STATUS_CLOSED) {
                return [
                    'success' => false,
                    'message' => 'Cannot cancel a closed ticket'
                ];
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Update ticket status only
                $ticket->status = Ticket::STATUS_CANCELLED;

                // Detailed logging of save attempt
                Yii::info('Attempting to save cancelled ticket with attributes: ' . json_encode($ticket->attributes));

                // Use save(false) to skip validation since we're only updating the status
                if (!$ticket->save(false)) {
                    Yii::error('Failed to save cancelled ticket');
                    throw new \Exception('Failed to save ticket');
                }

                $transaction->commit();
                
                Yii::info('Successfully cancelled ticket #' . $id);
                
                return [
                    'success' => true,
                    'message' => 'Ticket cancelled successfully'
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error during ticket cancellation: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Yii::error('Ticket cancellation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => YII_DEBUG ? 
                    'Failed to save ticket changes: ' . $e->getMessage() : 
                    'Failed to save ticket changes'
            ];
        }
    }

    public function actionReopen()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->post('id');
            $reason = Yii::$app->request->post('reason');
            
            if (!$id || !$reason) {
                return [
                    'success' => false,
                    'message' => 'Ticket ID and reason are required'
                ];
            }

            $ticket = Ticket::findOne($id);
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket not found'
                ];
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // Update ticket
                $ticket->status = 'approved';
                $ticket->reopen_reason = $reason;
                
                if (!$ticket->save()) {
                    throw new \Exception('Failed to save ticket changes');
                }

                // Get assigned developer email
                if ($ticket->assigned_to) {
                    $developer = User::findOne($ticket->assigned_to);
                    if ($developer && $developer->company_email) {
                        $this->sendEmailNotification(
                            $developer->company_email,
                            $ticket,
                            $reason,
                            'Assigned Developer'
                        );
                    }
                }

                // Get admin emails and send notifications
                $admins = User::find()
                    ->select(['id', 'company_email'])
                    ->where(['role' => 'admin'])
                    ->andWhere(['IS NOT', 'company_email', null])
                    ->all();

                foreach ($admins as $admin) {
                    $this->sendEmailNotification(
                        $admin->company_email,
                        $ticket,
                        $reason,
                        'Admin'
                    );
                }

                $transaction->commit();
                
                return [
                    'success' => true,
                    'message' => 'Ticket reopened and notifications sent successfully'
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Yii::error('Error in reopen action: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'An error occurred while reopening the ticket'
            ];
        }
    }

    private function sendEmailNotification($model, $type = 'create')
    {
        try {
            switch ($type) {
                case 'create':
                    // Send notification to admin for new ticket
                    return Yii::$app->mailer->compose()
                        ->setTo(Yii::$app->params['adminEmail'])
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                        ->setSubject('New Ticket Created: #' . $model->id . ' - ' . $model->company_name)
                        ->setTextBody("A new ticket has been created.\n\n" .
                            "Details:\n\n" .
                            "Company: {$model->company_name}\n" .
                            "Module: {$model->module}\n" .
                            "Issue: {$model->issue}\n" .
                            "Description: {$model->description}\n\n" .
                            "Please review the ticket.")
                        ->send();

                case 'assign':
                    // Get the developer
                    $developer = User::findOne($model->assigned_to);
                    if (!$developer) {
                        throw new \Exception('Developer not found');
                    }

                    return Yii::$app->mailer->compose('assignmentNotification', [
                        'developer_name' => $developer->name,
                        'ticket_id' => $model->id,
                        'company_name' => $model->company_name,
                        'description' => $model->description,
                        'module' => $model->module,
                        'issue' => $model->issue,
                        'status' => $model->status
                    ])
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setTo([$developer->company_email => $developer->name])
                    ->setSubject("Ticket " . ucfirst($model->status) . " #{$model->id} - {$model->company_name}")
                    ->send();

                default:
                    throw new \Exception('Invalid notification type');
            }
        } catch (\Exception $e) {
            Yii::error('Failed to send email notification: ' . $e->getMessage());
            return false;
        }
    }



//   public function actionCreate()
// {
//     if (Yii::$app->user->identity->role == 1 || Yii::$app->user->identity->role == 'admin') {
//         Yii::$app->session->setFlash('error', 'Administrators cannot create tickets. Please use a regular user account.');
//         return $this->redirect(['/site/index']);
//     }

//     $model = new Ticket();

//     // Get current user's company modules and update user's selectedModules
//     $userCompany = Company::findOne(['company_email' => Yii::$app->user->identity->company_email]);
    
//     if ($userCompany) {
//         // Update the user's selectedModules with company modules
//         $user = User::findOne(Yii::$app->user->id);
        
//         // If user's selectedModules is empty, get modules from company table
//         $companyModules = $userCompany->modules;
        
//         if ($user && empty($user->selectedModules)) {
//             $user->selectedModules = json_encode($companyModules);
//             $user->save(false);
//         }
        
//         // Always use company modules for the dropdown
//         $companyModules = is_array($companyModules) ? $companyModules : [];
//     } else {
//         $companyModules = [];
//     }

//     if ($model->load(Yii::$app->request->post())) {
//         // Set company-related data
//         $model->company_name = $userCompany->company_name;
//         $model->company_email = $userCompany->company_email;
//         $model->created_by = Yii::$app->user->id;
//         $model->created_at = date('Y-m-d H:i:s');
//         $model->created_by = $user->id;
//         $model->company_name = $user->company_name;
//         $model->company_email = $user->company_email;
//         $model->status = Ticket::STATUS_PENDING;
//         $model->module = $model->selectedModule;

//         // Check if screenshotUrl is set and is a valid Cloudinary URL
//         if (!empty($model->screenshotUrl)) {
//             try {
//                 // Verify it's a Cloudinary URL
//                 if (strpos($model->screenshotUrl, Yii::$app->params['cloudinary']['cloud_name']) === false) {
//                     throw new \Exception('Invalid screenshot URL format');
//                 }

//                 // Optional: Verify the image exists on Cloudinary
//                 $cloudinary = new Cloudinary([
//                     'cloud' => [
//                         'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
//                         'api_key' => Yii::$app->params['cloudinary']['api_key'],
//                         'api_secret' => Yii::$app->params['cloudinary']['api_secret']
//                     ]
//                 ]);

//                 // Extract public_id from URL
//                 preg_match('/\/v\d+\/(.+)\.\w+$/', $model->screenshotUrl, $matches);
//                 if (isset($matches[1])) {
//                     $publicId = $matches[1];
//                     // Verify image exists
//                     $cloudinary->adminApi()->asset($publicId);
//                     Yii::info('Screenshot verified on Cloudinary: ' . $model->screenshotUrl, __METHOD__);
//                 }

//                 if ($model->save()) {
//                     // Send email notification to admin
//                     $this->sendEmailNotification($model, 'create');

//                     Yii::$app->session->setFlash('success', 'Ticket created successfully with screenshot.');
//                     return $this->redirect(['view', 'id' => $model->id]);
//                 }
//             } catch (\Exception $e) {
//                 Yii::error('Cloudinary verification failed: ' . $e->getMessage(), __METHOD__);
//                 $model->addError('screenshotUrl', 'Failed to verify screenshot: ' . $e->getMessage());
//             }
//         } else {
//             // No screenshot provided, just save the ticket
//             if ($model->save()) {
//                 // Send email notification to admin
//                 $this->sendEmailNotification($model, 'create');

//                 Yii::$app->session->setFlash('success', 'Ticket created successfully.');
//                 return $this->redirect(['view', 'id' => $model->id]);
//             }
//         }
//     }

//     return $this->render('create', [
//         'model' => $model,
//         'companyModules' => array_combine($companyModules, $companyModules) // For dropdown
//     ]);
// }



public function actionCreate()
{
    // Check if the user is logged in
    if (Yii::$app->user->isGuest) {
        throw new \yii\web\ForbiddenHttpException('You are not allowed to perform this action.');
    }

    $model = new Ticket();
    
    // Get current user's company email
    $userCompanyEmail = Yii::$app->user->identity->company_email;

    // Check if the user is in the company table
    $companyData = Yii::$app->db->createCommand('
        SELECT * 
        FROM company 
        WHERE company_email = :company_email
    ')
    ->bindValue(':company_email', $userCompanyEmail)
    ->queryOne();

    // If the user is not found in the company table, log them out
    if ($companyData === false) {
        Yii::$app->user->logout(); // Log out the user
        Yii::$app->session->setFlash('error', 'You are not associated with any company. Please contact support.'); // Optional message
        return $this->redirect(['site/login']); // Redirect to login page
    }

    // Initialize availableModules
    $availableModules = []; // Ensure this is initialized

    // Fetch selected modules from the user table
    $userId = Yii::$app->user->id;
    $userData = Yii::$app->db->createCommand('
        SELECT selectedmodules 
        FROM user 
        WHERE id = :user_id
    ')
    ->bindValue(':user_id', $userId)
    ->queryOne();

    // Convert selected modules string to array and clean it
    if (!empty($userData['selectedmodules'])) {
        $availableModules = array_map('trim', explode(',', $userData['selectedmodules']));
    }

    // Define module issues
    $moduleIssues = [
        'DASH' => [
            'Login Issues',
            'Dashboard Not Loading',
            'Data Not Showing',
            'Performance Issues',
            'Report Generation Failed'
        ],
        'FIN' => [
            'Payment Processing Error',
            'Transaction Failed',
            'Balance Discrepancy',
            'Report Generation Error',
            'Reconciliation Issues'
        ],
        'MEMBERS' => [
            'Member Registration Failed',
            'Profile Update Issues',
            'Access Denied',
            'Data Sync Problems',
            'Member Search Not Working'
        ],
        'MOBILE' => [
            'App Crashes',
            'Login Failed',
            'Transaction Error',
            'Data Loading Issues',
            'Connection Problems'
        ],
        'HR' => [
            'Payroll Processing Error',
            'Leave Management Issues',
            'Employee Data Error',
            'Report Generation Failed',
            'Access Rights Problem'
        ]
    ];

    // Handle POST request for ticket creation
    if (Yii::$app->request->isPost) {
        $post = Yii::$app->request->post('Ticket');
        
        try {
            // Initialize Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ]
            ]);

            // Handle screenshot upload
            $screenshotUrl = null;
            if (isset($_FILES['Ticket']) && !empty($_FILES['Ticket']['name']['screenshot'])) {
                $tempFile = $_FILES['Ticket']['tmp_name']['screenshot'];
                
                // Upload to Cloudinary
                $result = $cloudinary->uploadApi()->upload($tempFile, [
                    'folder' => 'tickets',
                    'resource_type' => 'auto'
                ]);
                
                if (isset($result['secure_url'])) {
                    $screenshotUrl = $result['secure_url'];
                }
            }

            // Create database command with screenshot URL
            $command = Yii::$app->db->createCommand()->insert('ticket', [
                'user_id' => Yii::$app->user->id,
                'company_name' => Yii::$app->user->identity->company_name,
                'company_email' => Yii::$app->user->identity->company_email,
                'module' => $post['module'],
                'issue' => $post['issue'],
                'description' => $post['description'],
                'severity_level' => $post['severity_level'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'screenshotUrl' => $screenshotUrl,  // Add the Cloudinary URL
                'voice_note_url' => $post['voice_note_url'] // Add the voice note URL
            ]);

            if ($command->execute()) {
                Yii::$app->session->setFlash('success', 'Ticket saved to database successfully with screenshot and voice note.');
                return $this->redirect(['view', 'id' => Yii::$app->db->lastInsertID]);
            } else {
                Yii::error('Failed to execute database command');
                Yii::$app->session->setFlash('error', 'Failed to save ticket: Database error');
            }
        } catch (\Exception $e) {
            Yii::error('Error: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Failed to save ticket: ' . $e->getMessage());
        }
    }

    return $this->render('create', [
        'model' => $model,
        'companyModules' => $availableModules,
        'moduleIssues' => $moduleIssues
    ]);
}


    public function actionAssigned()
    {
        $tickets = Ticket::find()
            ->where(['assigned_to' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('assigned', [
            'tickets' => $tickets
        ]);
    }

    public function actionViewImage($id)
    {
        $model = Ticket::findOne($id);
        if ($model && $model->screenshot) {
            return $this->renderPartial('_image', [
                'base64Image' => 'data:image/jpeg;base64,' . $model->screenshot
            ]);
        }
        return 'No image available';
    }

    public function actionUploadToCloudinary()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            if (!isset($_FILES['file'])) {
                return [
                    'success' => false,
                    'message' => 'No file uploaded'
                ];
            }

            $file = $_FILES['file'];
            
            // Validate file size (5MB limit)
            if ($file['size'] > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File size must not exceed 5MB'
                ];
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Only JPG, PNG and GIF files are allowed'
                ];
            }

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ]
            ]);

            $response = $cloudinary->uploadApi()->upload(
                $file['tmp_name'],
                [
                    'folder' => 'tickets',
                    'resource_type' => 'image'
                ]
            );

            return [
                'success' => true,
                'url' => $response['secure_url'],
                'public_id' => $response['public_id']
            ];

        } catch (\Exception $e) {
            \Yii::error('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    public function actionUpload()
    {
        try {
            if (!isset($_FILES['file'])) {
                return $this->asJson(['error' => 'No file uploaded']);
            }

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ]
            ]);

            $response = $cloudinary->uploadApi()->upload(
                $_FILES['file']['tmp_name'],
                ['folder' => 'tickets'] // Optional: specify folder
            );

            return $this->asJson([
                'success' => true,
                'url' => $response['secure_url']
            ]);

        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getModuleIssues()
    {
        return [
            'DASH' => [
                'Loading Issues' => 'Dashboard Loading Issues',
                'Data Not Updating' => 'Dashboard Data Not Updating',
                'Widget Problems' => 'Dashboard Widget Problems',
                'Performance Issues' => 'Dashboard Performance Issues',
                'Access Problems' => 'Dashboard Access Problems'
            ],
            'BI' => [
                'Report Loading' => 'Power BI Report Loading Issues',
                'Data Refresh' => 'Data Refresh Problems',
                'Visualization Error' => 'Visualization Errors',
                'Export Issues' => 'Export Problems',
                'Connection Error' => 'Data Connection Issues'
            ],
            'REPORTS' => [
                'Generation Failed' => 'Report Generation Failed',
                'Download Issues' => 'Report Download Issues',
                'Format Problems' => 'Report Format Problems',
                'Missing Data' => 'Missing Report Data',
                'Scheduling Issues' => 'Report Scheduling Issues'
            ],
            'ADMIN' => [
                'User Management' => 'User Management Issues',
                'Permission Error' => 'Permission Configuration Problems',
                'Settings Issues' => 'System Settings Issues',
                'Audit Log' => 'Audit Log Problems',
                'Configuration' => 'General Configuration Issues'
            ],
            'FIN' => [
                'Transaction Error' => 'Transaction Processing Error',
                'Report Issues' => 'Financial Report Issues',
                'Calculation Error' => 'Financial Calculation Problems',
                'Integration Issue' => 'Financial Integration Issues',
                'Balance Mismatch' => 'Balance Reconciliation Issues'
            ],
            'HR' => [
                'Employee Records' => 'Employee Record Issues',
                'Leave Management' => 'Leave Management Problems',
                'Payroll Issues' => 'Payroll Processing Issues',
                'Document Upload' => 'Document Upload Problems',
                'Attendance System' => 'Attendance System Issues'
            ],
            'CREDIT' => [
                'Loan Processing' => 'Loan Processing Issues',
                'Credit Score' => 'Credit Score Calculation Problems',
                'Application Error' => 'Loan Application Errors',
                'Payment Issues' => 'Loan Payment Issues',
                'Document Verify' => 'Document Verification Problems'
            ],
            'MEMBERS' => [
                'Login Issues' => 'Member Login Problems',
                'Profile Update' => 'Profile Update Issues',
                'Statement Error' => 'Statement Generation Error',
                'Service Access' => 'Service Access Problems',
                'Password Reset' => 'Password Reset Issues'
            ],
            'MOBILE' => [
                'App Crash' => 'Mobile App Crash',
                'Login Failed' => 'Mobile Login Failure',
                'Transaction Error' => 'Mobile Transaction Error',
                'Update Issues' => 'App Update Problems',
                'Feature Access' => 'Feature Access Issues'
            ],
            'USSD' => [
                'Session Error' => 'USSD Session Errors',
                'Menu Issues' => 'USSD Menu Problems',
                'Transaction Failed' => 'USSD Transaction Failures',
                'Response Delay' => 'Slow USSD Response',
                'Service Access' => 'USSD Service Access Issues'
            ]
        ];
    }

 

}






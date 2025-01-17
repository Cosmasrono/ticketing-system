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
use yii\helpers\Json;
use yii\web\JsonResponse;
 
 
 
 
 

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
                'only' => ['index', 'create', 'view', 'assign', 'delete', 'close', 'approve', 'cancel'],
                'rules' => [
                    // Rule specifically for create action - only users with role 2 or 'user'
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $userRole = Yii::$app->user->identity->role;
                            return $userRole === 2 || $userRole === 'user';
                        },
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->session->setFlash('error', 'Only users can create tickets.');
                            return Yii::$app->response->redirect(['/site/index']);
                        }
                    ],
                    // Deny developers (role 3) and administrators (roles 1 and 4) from creating, viewing, or deleting tickets
                    [
                        'actions' => ['create', 'view', 'delete'],
                        'allow' => false,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $userRole = (int)Yii::$app->user->identity->role;
                            return in_array($userRole, [1, 3, 4]);
                        },
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->session->setFlash('error', 'Developers and administrators cannot create, view, or delete tickets.');
                            return Yii::$app->response->redirect(['/site/index']);
                        }
                    ],
                    // Allow only regular users to create, view, and delete tickets
                    [
                        'actions' => ['index', 'create', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $userRole = (int)Yii::$app->user->identity->role;
                            return !in_array($userRole, [1, 3, 4]);
                        }
                    ],

                    // User permissions for their own tickets
                    [
                        'actions' => ['delete', 'close'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $ticketId = Yii::$app->request->get('id') ?? Yii::$app->request->post('id');
                            $ticket = Ticket::findOne($ticketId);
                            return $ticket && $ticket->user_id == Yii::$app->user->id;
                        }
                    ],

                    // Admin access rules (only for admin actions)
                    [
                        'actions' => ['approve', 'cancel', 'assign'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return in_array((int)Yii::$app->user->identity->role, [1, 4]) || 
                                   Yii::$app->user->identity->role === 'admin' || 
                                   Yii::$app->user->identity->role === 'super_admin';
                        }
                    ],

                    // Allow developers to close their own tickets
                    [
                        'actions' => ['close'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $ticketId = Yii::$app->request->get('id') ?? Yii::$app->request->post('id');
                            $ticket = Ticket::findOne($ticketId);
                            return $ticket && (Yii::$app->user->identity->role === 3 || $ticket->user_id == Yii::$app->user->id);
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

    // Skip all checks for now
    return true;

    /* Commenting out all company checks for debugging
    // Skip company check for certain actions if needed
    $skipActions = ['index', 'view'];
    if (in_array($action->id, $skipActions)) {
        return true;
    }

    // Check if user is logged in
    if (!Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;
        $userEmail = $user->company_email;
        
        // Simple company email check
        $companyExists = \app\models\Company::find()
            ->where(['company_email' => $userEmail])
            ->exists();

        if ($companyExists) {
            return true;
        }

        Yii::$app->session->setFlash('error', 'You are not associated with any company. Please contact support.');
        return $this->redirect(['/site/index']);
    }

    Yii::$app->session->setFlash('error', 'Please login to continue.');
    return $this->redirect(['/site/login']);
    */
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
        $ticket = Ticket::findOne($id);
        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found.');
        }

        // Assuming you have the user ID to assign
        $escalatedToId = 35; // Example user ID

        // Check if the user exists in the users table
        if (\app\models\User::find()->where(['id' => $escalatedToId])->exists()) {
            $ticket->escalated_to = $escalatedToId;
        } else {
            // Handle the case where the user does not exist
            Yii::$app->session->setFlash('error', 'The user does not exist.');
            return $this->redirect(['index']); // Redirect or handle accordingly
        }

        // Proceed with the update
        $ticket->assigned_to = $assignedToId; // Set this as needed
        $ticket->status = 'reassigned';
        $ticket->last_update_at = new \yii\db\Expression('NOW()');

        if ($ticket->save()) {
            Yii::$app->session->setFlash('success', 'Ticket updated successfully.');
            return $this->redirect(['view', 'id' => $ticket->id]);
        }

        // Handle save error
        Yii::$app->session->setFlash('error', 'Failed to update ticket.');
        return $this->redirect(['index']);
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


    public function actionAssign($id)
    {
        // Check if the user is logged in and has the required role
        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, [1, 4])) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to assign tickets.');
        }
    
        // Set JSON response format for AJAX requests
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }
    
        // Get developers
        $developers = User::find()
            ->where(['role' => User::ROLE_DEVELOPER])
            ->orWhere(['role' => 3])  // Include role 3 as developer
            ->andWhere(['status' => 10])
            ->all();
    
        if (empty($developers)) {
            if (Yii::$app->request->isAjax) {
                return [
                    'success' => false,
                    'message' => 'No developers available for assignment.'
                ];
            }
            Yii::$app->session->setFlash('error', 'No developers available for assignment.');
            return $this->redirect(['index']);
        }
    
        // Find the ticket
        $ticket = Ticket::findOne($id);
        if (!$ticket) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }
    
        // Handle AJAX request for assigning the ticket
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $developerId = Yii::$app->request->post('Ticket')['assigned_to'] ?? null;
    
                // Add debug logging for the received developer ID
                Yii::debug("Received developer ID: " . $developerId);
    
                // Fetch developer with explicit query and debug logging
                $selectedDeveloper = User::find()
                    ->where(['id' => $developerId])
                    ->one();
    
                if (!$selectedDeveloper) {
                    throw new \Exception('Selected developer is not valid');
                }
    
                // Debug log the developer details
                Yii::debug("Developer found: " . print_r([
                    'id' => $selectedDeveloper->id,
                    'name' => $selectedDeveloper->name,
                    'all_attributes' => $selectedDeveloper->attributes
                ], true));
    
                $originalTicket = Ticket::findOne($ticket->id);
    
                // Check the ticket's current status
                if ($ticket->status === 'escalated') {
                    $ticket->status = 'reassigned';
                }
    
                // Just assign the developer ID
                $ticket->escalated_to = $developerId;
                $ticket->assigned_to = $developerId;
                $ticket->escalation_comment = $originalTicket->escalation_comment;
    
                if ($ticket->save(false)) {
                    $message = 'Developer assigned successfully';
                    $transaction->commit();
                    
                    // Use selectedDeveloper for email notification
                    try {
                        $emailSent = Yii::$app->mailer->compose('assignmentNotification', [
                            'developer_name' => $selectedDeveloper->name,
                            'ticket_id' => $ticket->id,
                            'company_name' => $ticket->company_name,
                            'description' => $ticket->description,
                            'module' => $ticket->module,
                            'issue' => $ticket->issue
                        ])
                        ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                        ->setTo([$selectedDeveloper->company_email => $selectedDeveloper->name])
                        ->setSubject("Ticket Assignment #{$ticket->id} - {$ticket->company_name}")
                        ->send();
    
                        if (!$emailSent) {
                            Yii::error('Failed to send email notification to developer: ' . $selectedDeveloper->name);
                        }
                    } catch (\Exception $e) {
                        Yii::error('Email sending failed for developer ' . $selectedDeveloper->name . ': ' . $e->getMessage());
                        // Continue execution even if email fails
                    }
    
                    return [
                        'success' => true,
                        'message' => $message,
                        'newStatus' => $ticket->status,
                        'ticketId' => $ticket->id
                    ];
                }
                
                throw new \Exception('Failed to save ticket');
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to assign developer: ' . $e->getMessage()
                ];
            }
        }
    
        // For non-AJAX requests, render the form
        return $this->render('assign', [
            'ticket' => $ticket,
            'developers' => $developers
        ]);
    }
    

    private function getDevelopers()
    {
        $developers = User::find()
            ->where(['role' => User::ROLE_DEVELOPER])
            ->orWhere(['role' => 3])
            ->all();
        return ArrayHelper::map($developers, 'id', 'name');
    }

 




    // private function getDevelopers()
    // {
    //     $developers = User::find()->where(['role' => 'developer'])->all();
    //     return ArrayHelper::map($developers, 'id', 'name'); // Assuming 'name' is the field for the developer's name
    // }

 

    public function actionDelete($id)
    {
        $ticket = Ticket::findOne($id);
        
        if (!$ticket) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        // Debug information
        Yii::debug([
            'User ID' => Yii::$app->user->id,
            'Ticket User ID' => $ticket->user_id,
            'User Role' => Yii::$app->user->identity->role,
            'Is Owner' => (Yii::$app->user->id === $ticket->user_id ? 'Yes' : 'No')
        ], 'ticket_delete');

        // Check if user owns the ticket
        if (Yii::$app->user->id === $ticket->user_id) {
            if ($ticket->delete()) {
                Yii::$app->session->setFlash('success', 'Ticket deleted successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to delete ticket.');
            }
        } else {
            throw new ForbiddenHttpException('You do not have permission to delete this ticket.');
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
            $targetId = Yii::$app->request->post('targetId');
            $targetType = Yii::$app->request->post('targetType');

            // Validate request
            if (empty($id) || empty($comment) || empty($targetType)) {
                return [
                    'success' => false,
                    'message' => 'Missing required parameters'
                ];
            }

            $ticket = $this->findModel($id);
            
            // Allow users with role 3 to escalate tickets
            if ($ticket->assigned_to !== Yii::$app->user->id && Yii::$app->user->identity->role !== 3) {
                return [
                    'success' => false,
                    'message' => 'You are not authorized to escalate this ticket'
                ];
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Store original assignee for notification
                $originalAssignee = $ticket->assigned_to;

                // Handle different escalation types
                switch ($targetType) {
                    case 'developer':
                        // Check if targetId is provided for developer escalation
                        if (empty($targetId)) {
                            throw new \Exception('Developer must be selected');
                        }

                        // Find the developer and explicitly check their role in the users table
                        $developer = User::find()
                            ->where(['id' => $targetId])
                            ->andWhere(['role' => 3])
                            ->one();

                        if (!$developer) {
                            throw new \Exception('Selected user is not a developer (role 3)');
                        }
                        
                        // Prevent self-assignment
                        if ($developer->id === Yii::$app->user->id) {
                            throw new \Exception('Cannot escalate to yourself');
                        }

                        $ticket->escalated_to = $developer->id;
                        $ticket->assigned_to = $developer->id;
                        $ticket->status = 'reassigned';
                        break;

                    case 'admin':
                        // For admin escalation, set status to 'escalated'
                        $ticket->status = 'escalated';
                        $ticket->escalated_to = null; // Clear specific assignee
                        $ticket->assigned_to = null;  // Clear current assignee
                        break;

                    default:
                        throw new \Exception('Invalid escalation type');
                }

                // Update common ticket fields
                $ticket->escalation_comment = $comment;
                $ticket->escalated_at = new \yii\db\Expression('NOW()');
                $ticket->escalated_by = Yii::$app->user->id;
                $ticket->last_update_at = new \yii\db\Expression('NOW()');

                if (!$ticket->save(false)) {
                    throw new \Exception('Failed to save ticket changes');
                }

                // Send notifications
                $this->sendEscalationNotifications($ticket, $targetType, $originalAssignee);

                $transaction->commit();

                return [
                    'success' => true,
                    'message' => 'Ticket has been ' . 
                        ($targetType === 'admin' ? 'escalated to admin' : 'reassigned to another developer'),
                    'newStatus' => $ticket->status
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Yii::error('Escalation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'An error occurred while processing the escalation'
            ];
        }
    }


    
protected function sendEscalationNotifications($ticket, $targetType, $originalAssignee)
{
    try {
        // Get email template based on escalation type
        $template = $targetType === 'admin' ? 'adminEscalation' : 'developerReassignment';
        
        // Prepare recipients
        $recipients = [];
        
        if ($targetType === 'admin') {
            // Get all admin emails
            $adminEmails = User::find()
                ->select(['email', 'name'])
                ->where(['role' => User::ROLE_ADMIN])
                ->andWhere(['status' => User::STATUS_ACTIVE])
                ->all();
                
            foreach ($adminEmails as $admin) {
                $recipients[] = ['email' => $admin->email, 'name' => $admin->name];
            }
        } else {
            // Developer reassignment - notify new developer
            $newDeveloper = User::findOne($ticket->assigned_to);
            if ($newDeveloper) {
                $recipients[] = ['email' => $newDeveloper->email, 'name' => $newDeveloper->name];
            }
        }

        // Send notifications
        foreach ($recipients as $recipient) {
            Yii::$app->mailer->compose($template, [
                'ticket' => $ticket,
                'recipientName' => $recipient['name'],
                'escalatedBy' => Yii::$app->user->identity->name,
                'comment' => $ticket->escalation_comment
            ])
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo([$recipient['email'] => $recipient['name']])
            ->setSubject("Ticket #{$ticket->id} " . 
                ($targetType === 'admin' ? 'Escalated' : 'Reassigned'))
            ->send();
        }
    } catch (\Exception $e) {
        Yii::error('Failed to send escalation notifications: ' . $e->getMessage());
        // Don't throw the exception - we don't want to fail the escalation if notifications fail
    }
}

    // public function actionEscalate()
    // {
    //     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
    //     try {
    //         $id = Yii::$app->request->post('id');
    //         $comment = Yii::$app->request->post('comment');
            
    //         if (empty($comment)) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Escalation comment is required'
    //             ];
    //         }

    //         $ticket = $this->findModel($id);
            
    //         // Check if ticket can be escalated
    //         if ($ticket->status === 'escalated') {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Ticket is already escalated'
    //             ];
    //         }

    //         // Start transaction
    //         $transaction = Yii::$app->db->beginTransaction();
            
    //         try {
    //             // Store the current developer's ID in escalated_to before changing status
    //             $ticket->escalated_to = Yii::$app->user->id; // Store the ID of developer who is escalating
    //             $ticket->status = 'escalated';
    //             $ticket->assigned_to = null; // Clear the assigned developer since it's escalated
                
    //             $ticket->escalation_comment = $comment;
    //             $ticket->escalated_at = new \yii\db\Expression('NOW()'); // If you have this column
    //             $ticket->escalated_by = Yii::$app->user->id; // If you have this column
                
    //             if ($ticket->save(false)) {
    //                 // Optional: Log the escalation
    //                 Yii::info("Ticket #{$ticket->id} escalated. Comment: {$comment}", 'ticket');
                    
    //                 // Optional: Send notification
    //                 $this->sendEscalationNotification($ticket);
                    
    //                 $transaction->commit();
                    
    //                 return [
    //                     'success' => true,
    //                     'message' => 'Ticket has been escalated successfully'
    //                 ];
    //             }
                
    //             throw new \Exception('Failed to save ticket');
                
    //         } catch (\Exception $e) {
    //             $transaction->rollBack();
    //             Yii::error("Escalation failed: " . $e->getMessage());
    //             throw $e;
    //         }

    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => YII_DEBUG ? $e->getMessage() : 'An error occurred while escalating the ticket'
    //         ];
    //     }
    // }

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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $id = Yii::$app->request->post('id');

            // Validate request
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'Missing required parameters'
                ];
            }

            // Find the ticket model
            $ticket = $this->findModel($id);

            // Check if the current user is authorized to close the ticket
            // Allow users with role 3 to close the ticket
            if ($ticket->assigned_to !== Yii::$app->user->id && Yii::$app->user->identity->role !== 3) {
                return [
                    'success' => false,
                    'message' => 'You are not authorized to close this ticket'
                ];
            }

            // Check if the ticket is already closed
            if ($ticket->status === 'closed') {
                return [
                    'success' => false,
                    'message' => 'This ticket is already closed'
                ];
            }

            // Close the ticket
            $ticket->status = 'closed'; // Assuming 'closed' is the status for closed tickets

            // Save the ticket status
            if (!$ticket->save(false)) {
                return [
                    'success' => false,
                    'message' => 'Failed to close the ticket'
                ];
            }

            return [
                'success' => true,
                'message' => 'Ticket has been closed successfully'
            ];
        } catch (\Exception $e) {
            Yii::error('Close ticket error: ' . $e->getMessage() . ' | Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'An error occurred while closing the ticket. Please check the logs for more details.'
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


    public function actionCreate()
    {
        $model = new Ticket();

        // Log the start of ticket creation
        Yii::info('Starting ticket creation process', 'ticket-create');

        // Fetch user's company email from users table
        $userCompanyEmail = Yii::$app->db->createCommand('
            SELECT company_email 
            FROM users 
            WHERE id = :user_id
        ')
        ->bindValue(':user_id', Yii::$app->user->id)
        ->queryScalar();

        // Log fetched email
        Yii::info("Fetched company email: $userCompanyEmail", 'ticket-create');

        // Fetch and prepare modules list
        $userModules = Yii::$app->db->createCommand('
            SELECT modules 
            FROM users 
            WHERE id = :user_id
        ')
        ->bindValue(':user_id', Yii::$app->user->id)
        ->queryOne();

        // Log fetched modules
        Yii::info('Fetched user modules:', 'ticket-create');
        Yii::info($userModules, 'ticket-create');

        $modulesList = [];
        if (!empty($userModules['modules'])) {
            $modules = explode(',', $userModules['modules']);
            foreach ($modules as $module) {
                $module = trim($module);
                if (!empty($module)) {
                    $modulesList[$module] = $module;
                }
            }
        }

        // Get module issues
        $moduleIssues = $this->getModuleIssues();

        // Handle POST request for ticket creation
        if (Yii::$app->request->isPost) {
            try {
                // Log received POST data
                Yii::info('Received POST data:', 'ticket-create');
                Yii::info(Yii::$app->request->post(), 'ticket-create');

                // Load POST data
                if (!$model->load(Yii::$app->request->post())) {
                    throw new \Exception('Failed to load form data');
                }

                // Get the module and issue from POST data directly
                $postData = Yii::$app->request->post('Ticket');
                $selectedModule = isset($postData['module']) ? $postData['module'] : null;
                $selectedIssue = isset($postData['issue']) ? $postData['issue'] : null;

                // Log the selected values
                Yii::info('Selected Module: ' . $selectedModule, 'ticket-create');
                Yii::info('Selected Issue: ' . $selectedIssue, 'ticket-create');

                // Set all attributes explicitly
                $model->setAttributes([
                    'user_id' => Yii::$app->user->id,
                    'created_by' => Yii::$app->user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'company_name' => Yii::$app->user->identity->company_name ?? '(not set)',
                    'company_email' => $userCompanyEmail,
                    'status' => 'pending',
                    'severity_level' => $model->severity ?? 1,
                    'module' => $selectedModule,  // Explicitly set module
                    'issue' => $selectedIssue,    // Explicitly set issue
                    'description' => $postData['description'] ?? null
                ]);

                // Log model attributes after setting
                Yii::info('Model attributes after setting:', 'ticket-create');
                Yii::info($model->attributes, 'ticket-create');

                // Validate essential fields
                if (empty($model->module)) {
                    throw new \Exception('Module is required');
                }
                if (empty($model->issue)) {
                    throw new \Exception('Issue is required');
                }

                // Handle screenshot upload
                if (isset($_FILES['Ticket']) && isset($_FILES['Ticket']['tmp_name']['screenshot'])) {
                    Yii::info('Processing screenshot upload', 'ticket-create');
                    $tempFile = $_FILES['Ticket']['tmp_name']['screenshot'];
                    
                    if (is_uploaded_file($tempFile)) {
                        try {
                            $cloudinary = new \Cloudinary\Cloudinary([
                                'cloud' => [
                                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                                ]
                            ]);
                            
                            $result = $cloudinary->uploadApi()->upload($tempFile, [
                                'folder' => 'tickets/images',
                                'resource_type' => 'auto',
                                'unique_filename' => true,
                                'overwrite' => false,
                                'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif'],
                            ]);
                            
                            if (isset($result['secure_url'])) {
                                $model->screenshot_url = $result['secure_url'];
                                Yii::info('Screenshot uploaded successfully: ' . $result['secure_url'], 'ticket-create');
                            }
                        } catch (\Exception $e) {
                            Yii::error('Screenshot upload error: ' . $e->getMessage(), 'ticket-create');
                            $model->screenshot_url = 'No screenshot available';
                        }
                    }
                }

                // Validate the model
                if (!$model->validate()) {
                    Yii::error('Validation failed:', 'ticket-create');
                    Yii::error($model->errors, 'ticket-create');
                    throw new \Exception('Validation failed: ' . json_encode($model->errors));
                }

                // Log final model state before save
                Yii::info('Final model state before save:', 'ticket-create');
                Yii::info($model->attributes, 'ticket-create');

                // Save the model
                if (!$model->save()) {
                    Yii::error('Save failed:', 'ticket-create');
                    Yii::error($model->errors, 'ticket-create');
                    throw new \Exception('Failed to save ticket: ' . json_encode($model->errors));
                }

                // Log successful save
                Yii::info('Ticket saved successfully with ID: ' . $model->id, 'ticket-create');

                // Return success response
                if (Yii::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => true,
                        'ticket_id' => $model->id,
                        'title' => 'Success!',
                        'text' => 'Ticket created successfully',
                        'redirectUrl' => Url::to(['view', 'id' => $model->id]),
                        'saved_data' => $model->attributes
                    ]);
                }

                Yii::$app->session->setFlash('success', 'Ticket created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                Yii::error('Error in ticket creation: ' . $e->getMessage(), 'ticket-create');
                
                if (Yii::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => false,
                        'message' => YII_DEBUG ? $e->getMessage() : 'Failed to create ticket'
                    ]);
                }
                
                Yii::$app->session->setFlash('error', YII_DEBUG ? $e->getMessage() : 'Failed to create ticket');
            }
        }

        // Render the form
        return $this->render('create', [
            'model' => $model,
            'companyModules' => $modulesList,
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
            'MOBILE' => [
                'Member Registration Failed',
                'Profile Update Issues',
                'Access Denied',
                'Data Sync Problems',
                'Member Search Not Working',
                'Member Verification Failed',
                'Document Upload Issues',
                'Member Status Update Failed'
            ],
            'DASH' => [
                'Loading Issues',
                'Data Not Updating',
                'Widget Problems',
                'Performance Issues',
                'Access Problems'
            ],
            'BI' => [
                'Report Loading',
                'Data Refresh',
                'Visualization Error',
                'Export Issues',
                'Connection Error'
            ],
            'REPORTS' => [
                'Generation Failed',
                'Download Issues',
                'Format Problems',
                'Missing Data',
                'Scheduling Issues'
            ],
            'ADMIN' => [
                'User Management',
                'Permission Error',
                'Settings Issues',
                'Audit Log',
                'Configuration'
            ],
            'FIN' => [
                'Transaction Error',
                'Report Issues',
                'Calculation Error',
                'Integration Issue',
                'Balance Mismatch'
            ],
            'HR' => [
                'Employee Records',
                'Leave Management',
                'Payroll Issues',
                'Document Upload',
                'Attendance System'
            ],
            'CREDIT' => [
                'Loan Processing',
                'Credit Score',
                'Application Error',
                'Payment Issues',
                'Document Verify'
            ],
            'MEMBERS' => [
                'Login Issues',
                'Profile Update',
                'Statement Error',
                'Service Access',
                'Password Reset'
            ],
            'USSD' => [
                'Session Error',
                'Menu Issues',
                'Transaction Failed',
                'Response Delay',
                'Service Access'
            ]
        ];
    }


    private function sendTicketNotification($model)
    {
        try {
            // Get admin email from params or configuration
            $adminEmail = Yii::$app->params['adminEmail'] ?? 'admin@example.com';
            
            // Prepare severity text
            $severityMap = [
                1 => 'Low',
                2 => 'Medium',
                3 => 'High',
                4 => 'Critical'
            ];
            
            $severityText = $severityMap[$model->severity_level] ?? 'Unknown';
            
            // Prepare email content
            $subject = "New Support Ticket #{$model->id} - {$model->company_name}";
            
            $body = "A new support ticket has been created:\n\n" .
                    "Ticket ID: #{$model->id}\n" .
                    "Company: {$model->company_name}\n" .
                    "Module: {$model->module}\n" .
                    "Issue: {$model->issue}\n" .
                    "Severity: {$severityText}\n" .
                    "Description: {$model->description}\n\n" .
                    "Screenshot: " . ($model->screenshot_url ? $model->screenshot_url : 'No screenshot attached') . "\n\n" .
                    "Created at: {$model->created_at}\n" .
                    "Created by: {$model->company_email}";
    
            // Send email to admin
            $adminMail = Yii::$app->mailer->compose()
                ->setTo($adminEmail)
                ->setFrom([Yii::$app->params['supportEmail'] ?? 'noreply@example.com' => Yii::$app->params['senderName'] ?? 'Ticket System'])
                ->setReplyTo([$model->company_email => $model->company_name])
                ->setSubject($subject)
                ->setTextBody($body);
    
            // Send email to user
            $userMail = Yii::$app->mailer->compose()
                ->setTo($model->company_email)
                ->setFrom([Yii::$app->params['supportEmail'] ?? 'noreply@example.com' => Yii::$app->params['senderName'] ?? 'Ticket System'])
                ->setSubject("Your Support Ticket #{$model->id} has been created")
                ->setTextBody(
                    "Thank you for submitting a support ticket. Our team will review it shortly.\n\n" .
                    "Ticket Details:\n" .
                    "Ticket ID: #{$model->id}\n" .
                    "Module: {$model->module}\n" .
                    "Issue: {$model->issue}\n" .
                    "Severity: {$severityText}\n" .
                    "Description: {$model->description}\n\n" .
                    "We will contact you soon regarding this ticket."
                );
    
            // Send both emails
            $adminMailSent = $adminMail->send();
            $userMailSent = $userMail->send();
    
            // Log the email sending attempt
            Yii::info("Ticket #{$model->id} notification sent. Admin: $adminMailSent, User: $userMailSent");
    
            return $adminMailSent && $userMailSent;
    
        } catch (\Exception $e) {
            Yii::error("Failed to send ticket notification: " . $e->getMessage());
            return false;
        }
    }


    public function actionUploadVoiceNote()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            if (!isset($_FILES['voice_note'])) {
                throw new \Exception('No voice note file received');
            }

            // Initialize Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ]
            ]);

            // Upload to Cloudinary with simplified audio settings
            $result = $cloudinary->uploadApi()->upload(
                $_FILES['voice_note']['tmp_name'],
                [
                    'resource_type' => 'video', // Cloudinary uses 'video' for audio files
                    'folder' => 'tickets/voice-notes',
                    'format' => 'mp3'  // Only specify the format
                ]
            );

            if (!isset($result['secure_url'])) {
                throw new \Exception('Failed to get secure URL from Cloudinary');
            }

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'message' => 'Voice note uploaded successfully'
            ];

        } catch (\Exception $e) {
            Yii::error('Voice note upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to upload voice note: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sends email notification to admin when a new ticket is created
     * @param Ticket $ticket
     * @return bool
     */
  

}






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
use Cloudinary;
use Cloudinary\Uploader;
use app\models\Company;
use yii\helpers\Json;
use yii\web\JsonResponse;
use GuzzleHttp\Client;
 
 
 
 
 
 

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

                    // Allow all authenticated users to close tickets
                    [
                        'actions' => ['close'],
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
            ->alias('t')  // Give alias to main table
            ->leftJoin('users u', 'u.id = t.user_id');  // Changed 'user' to 'users'

        if (!Yii::$app->user->can('admin')) {
            $query->where(['t.user_id' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
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
    
                // Fetch developer with company_email from users table
                $selectedDeveloper = User::find()
                    ->select(['users.id', 'users.name', 'users.company_email']) // Explicitly select fields
                    ->from('users')
                    ->where(['users.id' => $developerId])
                    ->andWhere(['users.status' => 10])
                    ->one();
    
                if (!$selectedDeveloper) {
                    throw new \Exception('Selected developer is not valid');
                }
    
                // Debug log to verify the data
                Yii::debug("Developer data fetched: " . print_r([
                    'id' => $selectedDeveloper->id,
                    'name' => $selectedDeveloper->name,
                    'company_email' => $selectedDeveloper->company_email,
                ], true));
    
                // Verify company_email exists
                if (empty($selectedDeveloper->company_email)) {
                    // Check if the email exists in database directly
                    $developerEmail = Yii::$app->db->createCommand('
                        SELECT company_email 
                        FROM users 
                        WHERE id = :id', 
                        [':id' => $developerId]
                    )->queryScalar();
    
                    if ($developerEmail) {
                        $selectedDeveloper->company_email = $developerEmail;
                    } else {
                        throw new \Exception("No company email found for developer {$selectedDeveloper->name}");
                    }
                }
    
                $originalTicket = Ticket::findOne($ticket->id);
    
                // Update only assigned_to by default
                $ticket->assigned_to = $developerId;
                $ticket->assigned_at = date('Y-m-d H:i:s');

                // Only update escalated_to if the ticket is escalated
                if ($ticket->status === 'escalated') {
                    $ticket->status = 'reassigned';
                    $ticket->escalated_to = $developerId;
                    $ticket->escalation_comment = $originalTicket->escalation_comment;
                }

                if ($ticket->save(false)) {
                    // Send email notification with detailed error logging
                    try {
                        Yii::debug('Attempting to send email notification');
                        
                        if (empty($selectedDeveloper->company_email)) {
                            throw new \Exception("No company email found for developer {$selectedDeveloper->name}");
                        }

                        // Debug email parameters without accessing transport properties
                        Yii::debug("Email Parameters: " . print_r([
                            'from' => Yii::$app->params['senderEmail'],
                            'to' => $selectedDeveloper->company_email,
                            'subject' => "Ticket Assignment #{$ticket->id} - {$ticket->company_name}"
                        ], true));

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
                            throw new \Exception("Failed to send email to {$selectedDeveloper->company_email}");
                        }

                        $message = 'Developer assigned successfully and notification sent';
                        
                    } catch (\Exception $e) {
                        Yii::error('Email sending error details: ' . $e->getMessage());
                        $message = 'Developer assigned successfully but email notification failed: ' . $e->getMessage();
                    }

                    $transaction->commit();
                    
                    return [
                        'success' => true,
                        'message' => $message,
                        'newStatus' => $ticket->status,
                        'ticketId' => $ticket->id,
                        'assignedAt' => $ticket->assigned_at,
                        'emailStatus' => isset($emailSent) && $emailSent
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
        // Create base query
        $query = Ticket::find();
        
        // Get filter value
        $company_name = Yii::$app->request->get('company_name');
        

        // Create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);

        // Render view
        return $this->render('admin', [
            'dataProvider' => $dataProvider,
            'company_name' => $company_name,
        ]);
    }
  



public function actionClose()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            $ticketId = $request->post('id');
            $comment = $request->post('comment');

            // Find the ticket model
            $ticket = Ticket::findOne($ticketId);
            if ($ticket) {
                // Calculate time taken from assigned_at to now
                if ($ticket->assigned_at) {
                    $assignedAt = new \DateTime($ticket->assigned_at);
                    $closedAt = new \DateTime();
                    $timeDiff = $assignedAt->diff($closedAt);
                    
                    // Convert to hours with decimal points
                    $hoursTotal = $timeDiff->days * 24 + $timeDiff->h + 
                                ($timeDiff->i / 60) + ($timeDiff->s / 3600);
                    
                    // Update ticket
                    $ticket->status = 'closed';
                    $ticket->comments = $comment;
                    $ticket->closed_at = $closedAt->format('Y-m-d H:i:s');
                    $ticket->time_taken = round($hoursTotal, 2); // Round to 2 decimal places
                    $ticket->closed_by = Yii::$app->user->identity->username;

                    if ($ticket->save()) {
                        return $this->asJson([
                            'success' => true,
                            'timeTaken' => $ticket->time_taken,
                            'message' => 'Ticket closed. Time taken: ' . $ticket->time_taken . ' hours'
                        ]);
                    }
                } else {
                    return $this->asJson([
                        'success' => false,
                        'message' => 'Ticket was never assigned a start time.'
                    ]);
                }
            }
            return $this->asJson(['success' => false, 'message' => 'Failed to close ticket.']);
        }
        return $this->asJson(['success' => false, 'message' => 'Invalid request.']);
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


 function actionReopen()
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

        // Get user's modules from database
        $userInfo = Yii::$app->db->createCommand('
            SELECT company_email, modules 
            FROM users 
            WHERE id = :user_id
        ')
        ->bindValue(':user_id', Yii::$app->user->id)
        ->queryOne();

        // Prepare modules list
        $companyModules = [];
        if (!empty($userInfo['modules'])) {
            $modules = explode(',', $userInfo['modules']);
            foreach ($modules as $module) {
                $module = trim($module);
                if (!empty($module)) {
                    $companyModules[$module] = $module;
                }
            }
        }

        // Get module issues
        $moduleIssues = $this->getModuleIssues();

        if (Yii::$app->request->isPost) {
            try {
                // Load POST data first
                $model->load(Yii::$app->request->post());

                // Handle voice note URL if provided
                $voiceNoteUrl = Yii::$app->request->post('voice_note_url');
                if (!empty($voiceNoteUrl)) {
                    $model->voice_note_url = $voiceNoteUrl;
                    Yii::info('Voice note URL received: ' . $voiceNoteUrl);
                }

                // Handle screenshot upload
                if (isset($_FILES['Ticket']) && isset($_FILES['Ticket']['tmp_name']['screenshot'])) {
                    $tempFile = $_FILES['Ticket']['tmp_name']['screenshot'];
                    
                    if (is_uploaded_file($tempFile)) {
                        // Upload to Cloudinary
                        $screenshotUrl = $this->uploadToCloudinary($tempFile);
                        
                        // Log the URL for debugging
                        Yii::info('Cloudinary URL received: ' . $screenshotUrl);

                        // Directly update the model's screenshot_url
                        $model->setAttribute('screenshot_url', $screenshotUrl);
                        
                        // Double-check the attribute was set
                        Yii::info('Model screenshot_url after setting: ' . $model->screenshot_url);
                    }
                }

                // Set other required attributes
                $model->setAttributes([
                    'user_id' => Yii::$app->user->id,
                    'created_by' => Yii::$app->user->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'company_name' => Yii::$app->user->identity->company_name ?? '(not set)',
                    'company_email' => $userInfo['company_email'] ?? null,
                ]);

                // Try to save and verify
                if (!$model->save()) {
                    Yii::error('Failed to save ticket: ' . json_encode($model->errors));
                    throw new \Exception('Failed to save ticket: ' . json_encode($model->errors));
                }

                // Verify voice note URL was saved
                $saved = Yii::$app->db->createCommand('SELECT screenshot_url, voice_note_url FROM ticket WHERE id = :id')
                    ->bindValue(':id', $model->id)
                    ->queryOne();
                
                Yii::info('Saved voice_note_url in database: ' . ($saved['voice_note_url'] ?? 'null'));

                // If voice note URL wasn't saved properly, try direct update
                if (empty($saved['voice_note_url']) && !empty($voiceNoteUrl)) {
                    Yii::$app->db->createCommand()
                        ->update('ticket', 
                            ['voice_note_url' => $voiceNoteUrl],
                            ['id' => $model->id])
                        ->execute();
                    
                    Yii::info('Performed direct database update for voice_note_url');
                }

                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                Yii::error('Error in ticket creation: ' . $e->getMessage());
                throw $e;
            }
        }

        return $this->render('create', [
            'model' => $model,
            'companyModules' => $companyModules,
            'moduleIssues' => $moduleIssues
        ]);
    }



   private function uploadToCloudinary($tempFile)
{
    try {
        // Create HTTP client with SSL verification disabled
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false,
            'http_errors' => false,
            'defaults' => [
                'verify' => false,
                'exceptions' => false
            ]
        ]);

        // Initialize Cloudinary with the custom client
        $cloudinary = new \Cloudinary\Cloudinary([
            'cloud' => [
                'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                'api_key' => Yii::$app->params['cloudinary']['api_key'],
                'api_secret' => Yii::$app->params['cloudinary']['api_secret']
            ],
            'api' => [
                'http_client' => $httpClient
            ]
        ]);

        // Verify the file exists and is readable
        if (!is_readable($tempFile)) {
            throw new \Exception("Cannot read upload file: $tempFile");
        }

        // Log attempt
        Yii::info('Attempting to upload file to Cloudinary: ' . $tempFile);

        // Upload file and get result
        $result = $cloudinary->uploadApi()->upload($tempFile, [
            'folder' => 'tickets/screenshots',
            'resource_type' => 'image',
            'unique_filename' => true
        ]);

        // Log success
        Yii::info('Successfully uploaded to Cloudinary. Result: ' . json_encode($result));

        return $result['secure_url'] ?? null;

    } catch (\Exception $e) {
        // Log detailed error
        Yii::error('Cloudinary upload error: ' . $e->getMessage());
        Yii::error('Error trace: ' . $e->getTraceAsString());
        
        // You might want to handle this error differently
        throw new \Exception('Failed to upload image to Cloudinary: ' . $e->getMessage());
    }
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
    protected function getModuleIssues()
    {
        return [
            'Finance' => [
                'General Ledger Issues',
                'Transaction Processing',
                'Financial Reports',
                'Bank Reconciliation',
                'Budget Management'
            ],
            'HR' => [
                'Employee Records',
                'Leave Management',
                'Performance Reviews',
                'Recruitment Process',
                'Training Management'
            ],
            'Payroll' => [
                'Salary Processing',
                'Deduction Calculation',
                'Tax Computation',
                'Payslip Generation',
                'Statutory Reports'
            ],
            'BOSA' => [
                'Member Registration',
                'Loan Processing',
                'Dividend Calculation',
                'Share Management',
                'Guarantor Management'
            ],
            'FOSA' => [
                'Account Opening',
                'Transaction Processing',
                'ATM Management',
                'Standing Orders',
                'Statement Generation'
            ],
            'EDMS' => [
                'Document Upload',
                'File Retrieval',
                'Document Indexing',
                'Access Rights',
                'Version Control'
            ],
            'Member Portal' => [
                'Login Issues',
                'Account Access',
                'Online Services',
                'Password Reset',
                'Profile Updates'
            ],
            'Mobile App' => [
                'App Installation',
                'Login Problems',
                'Transaction Failures',
                'Balance Inquiry',
                'App Navigation'
            ],
            'Procurement' => [
                'Purchase Orders',
                'Vendor Management',
                'Inventory Control',
                'Requisition Process',
                'Contract Management'
            ],
            'CRM' => [
                'Member Inquiries',
                'Service Requests',
                'Communication Issues',
                'Feedback Management',
                'Campaign Management'
            ],
            'USSD' => [
                'General Ledger Issues',
                'Transaction Processing',
                'Financial Reports',
                'Bank Reconciliation',
                'Budget Management'
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

            $tempFile = $_FILES['voice_note']['tmp_name'];
            
            // Initialize Cloudinary
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ]
            ]);

            // Upload to Cloudinary
            $result = $cloudinary->uploadApi()->upload($tempFile, [
                'folder' => 'tickets/voice_notes',
                'resource_type' => 'video' // Use 'video' for audio files
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url']
            ];

        } catch (\Exception $e) {
            Yii::error('Voice note upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'Failed to upload voice note'
            ];
        }
    }

    public function actionSearch()
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('search', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
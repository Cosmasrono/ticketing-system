<?php

namespace app\controllers;

use yii\helpers\Html;
use yii\helpers\Url;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;

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
//  
use Cloudinary\Uploader;
use app\models\Company;
use yii\helpers\Json;
use yii\web\JsonResponse;
use GuzzleHttp\Client;
// use Cloudinary;
// use Cloudinary\Configuration\Configuration;
use app\models\TicketMessage;
 
 
 
 
 
 

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

    public function init()
    {
        parent::init();
        
        // Configure Cloudinary with your credentials from params.php or env variables
        Configuration::instance([
            'cloud' => [
                'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'], 
                'api_key' => Yii::$app->params['cloudinary']['api_key'],
                'api_secret' => Yii::$app->params['cloudinary']['api_secret']
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        
        // If you absolutely cannot enable OpenSSL, you can disable TLS (not recommended)
        // Configuration::instance()->cloud->useTLS = false;
    }

    public function actionIndex()
    {
        $searchModel = new \app\models\TicketSearch();
        $query = Ticket::find()
            ->select(['ticket.*'])
            ->leftJoin('users', 'users.id = ticket.user_id')
            ->where(['ticket.user_id' => Yii::$app->user->id]);

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
            'searchModel' => $searchModel,
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
        $ticket = Ticket::findOne($id);
        if ($ticket === null) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

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
    
        // Get active developers using raw SQL to handle type conversion
        $developers = Yii::$app->db->createCommand("
            SELECT id, name 
            FROM users 
            WHERE status = 1 
            AND (
                CAST(role as NVARCHAR(50)) = '3' 
                OR CAST(role as NVARCHAR(50)) = 'developer'
            )
            ORDER BY name ASC
        ")->queryAll();

        // Debug log the query results
        Yii::info("Found developers: " . json_encode($developers));

        if (empty($developers)) {
            Yii::error("No developers found with query");
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
        
                    // Fetch developer and validate their role
                    $selectedDeveloper = User::find()
                        ->where(['id' => $developerId])
                        ->andWhere(['status' => User::STATUS_ACTIVE])
                        ->andWhere("(CAST(role as NVARCHAR(50)) = '3' OR CAST(role as NVARCHAR(50)) = 'developer')")
                        ->one();
        
                    if (!$selectedDeveloper) {
                        throw new \Exception('Selected developer is not valid or not active');
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
                'developers' => ArrayHelper::map($developers, 'id', 'name')
            ]);
        }
        

    /**
     * Helper method to get valid ticket statuses
     * @return array
     */
    private function getValidTicketStatuses() {
        // Try to get valid statuses from the model rules
        $ticket = new Ticket();
        $validStatuses = [];
        
        // Analyze the validation rules for the 'status' attribute
        foreach ($ticket->rules() as $rule) {
            if (is_array($rule) && in_array('status', (array)$rule[0])) {
                if (isset($rule[1]) && $rule[1] === 'in' && isset($rule['range'])) {
                    $validStatuses = $rule['range'];
                    break;
                }
            }
        }
        
        // If we couldn't extract from rules, use some common defaults
        if (empty($validStatuses)) {
            $validStatuses = [
                'pending', 'open', 'assigned', 'in_progress', 'on_hold', 
                'reassigned', 'escalated', 'resolved', 'closed'
            ];
        }
        
        return $validStatuses;
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
                $originalAssignee = $ticket->assigned_to;

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

                // Update common ticket fields with proper timestamp values
                $currentTime = time(); // Get current Unix timestamp
                
                $ticket->escalation_comment = $comment;
                $ticket->escalated_at = $currentTime;  // Use Unix timestamp
                $ticket->escalated_by = Yii::$app->user->id;
                $ticket->last_update_at = $currentTime; // Use Unix timestamp
                $ticket->sla_status = '0';  // Make sure this is a string if needed

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
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ticketId = (int)Yii::$app->request->post('id');
                $closeMessage = Yii::$app->request->post('message');
                $ticket = Ticket::findOne($ticketId);
                
                if (!$ticket) {
                    throw new NotFoundHttpException('Ticket not found');
                }

                // Check if developer is assigned
                if (empty($ticket->assigned_to)) {
                    throw new \Exception('Cannot close ticket: No developer assigned');
                }

                // Update ticket status
                $ticket->status = 'closed';
                $ticket->sla_status = 'completed';
                $ticket->closed_at = time();
                $ticket->closed_by = (string)Yii::$app->user->id;

                if (!$ticket->save()) {
                    Yii::error('Failed to close ticket: ' . json_encode($ticket->errors));
                    throw new \Exception('Validation failed: ' . json_encode($ticket->errors));
                }

                // If there's a closing message, store it and send notifications
                if (!empty($closeMessage)) {
                    // Store message in ticket_message table
                    $ticketMessage = new TicketMessage([
                        'ticket_id' => $ticketId,
                        'sender_id' => Yii::$app->user->id,
                        'recipient_id' => $ticket->created_by,
                        'subject' => 'Ticket #' . $ticketId . ' - Closed',
                        'message' => $closeMessage,
                        'sent_at' => time(),
                        'message_type' => 'closure_message',
                        'is_internal' => 0,
                    ]);
                    
                    if (!$ticketMessage->save()) {
                        throw new \Exception('Failed to save closure message');
                    }

                    // Send email to ticket creator
                    $creator = User::findOne($ticket->created_by);
                    if ($creator && $creator->company_email) {
                        Yii::$app->mailer->compose()
                            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' Support'])
                            ->setTo($creator->company_email)
                            ->setSubject('Ticket #' . $ticketId . ' has been closed')
                            ->setHtmlBody(
                                '<p>Dear ' . Html::encode($creator->name) . ',</p>' .
                                '<p>Your ticket #' . $ticketId . ' has been closed.</p>' .
                                '<p><strong>Developer\'s Message:</strong></p>' .
                                '<div style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; margin: 10px 0;">' .
                                nl2br(Html::encode($closeMessage)) .
                                '</div>' .
                                '<p>Thank you for using our support system.</p>'
                            )
                            ->send();
                    }

                    // Notify admins
                    $admins = User::find()
                        ->where(['role' => 'admin'])
                        ->all();
                        
                    foreach ($admins as $admin) {
                        if ($admin->company_email) {
                            Yii::$app->mailer->compose()
                                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' System'])
                                ->setTo($admin->company_email)
                                ->setSubject('Ticket #' . $ticketId . ' has been closed')
                                ->setHtmlBody(
                                    '<p>Ticket #' . $ticketId . ' has been closed by ' . Yii::$app->user->identity->name . '.</p>' .
                                    '<p><strong>Closing Message:</strong></p>' .
                                    '<div style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; margin: 10px 0;">' .
                                    nl2br(Html::encode($closeMessage)) .
                                    '</div>' .
                                    '<p><a href="' . Yii::$app->urlManager->createAbsoluteUrl(['/ticket/view', 'id' => $ticketId]) . '">View Ticket</a></p>'
                                )
                                ->send();
                        }
                    }
                }

                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Ticket closed successfully' . 
                                (!empty($closeMessage) ? ' and notifications have been sent' : '')
                ];
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Failed to close ticket: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        throw new MethodNotAllowedHttpException('Method not allowed');
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
            // Debug incoming data
            Yii::debug('POST data received: ' . print_r($_POST, true));
            
            $base64Screenshot = Yii::$app->request->post('screenshot-base64');
            if (empty($base64Screenshot)) {
                throw new \Exception('Screenshot data is missing.');
            }

            // Debug the incoming data
            Yii::debug('Base64 screenshot length: ' . strlen($base64Screenshot));

            // Create a new Cloudinary instance instead of using static configuration
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Yii::$app->params['cloudinary']['cloud_name'],
                    'api_key' => Yii::$app->params['cloudinary']['api_key'],
                    'api_secret' => Yii::$app->params['cloudinary']['api_secret']
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
            
            try {
                // Extract the actual base64 content without the prefix
                if (strpos($base64Screenshot, 'data:image') !== false) {
                    // Extract mime type
                    $mime = substr($base64Screenshot, 5, strpos($base64Screenshot, ';') - 5);
                    
                    // Extract base64 content
                    $base64Content = substr($base64Screenshot, strpos($base64Screenshot, ',') + 1);
                    
                    // Decode base64 content
                    $decodedImage = base64_decode($base64Content);
                    
                    if ($decodedImage === false) {
                        throw new \Exception('Invalid base64 encoding');
                    }

                    // Set up temporary directory
                    $tempDir = Yii::getAlias('@runtime/temp');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0777, true);
                    }

                    // Create temporary file with appropriate extension
                    $extension = explode('/', $mime)[1] ?? 'png';
                    $tempFile = tempnam($tempDir, 'cloudinary_');
                    $tempFileWithExt = $tempFile . '.' . $extension;
                    rename($tempFile, $tempFileWithExt);

                    // Write decoded content to file
                    if (file_put_contents($tempFileWithExt, $decodedImage) === false) {
                        throw new \Exception('Failed to write temporary file');
                    }

                    // Upload to Cloudinary
                    $uploadResult = $cloudinary->uploadApi()->upload($tempFileWithExt, [
                        'resource_type' => 'image'
                    ]);

                    // Store the Cloudinary URL
                    $model->screenshot_url = $uploadResult['secure_url'];
                } else {
                    throw new \Exception('Invalid image format: Base64 image data not found');
                }
            } catch (\Exception $cloudinaryError) {
                // Add more detailed error logging
                Yii::error('Cloudinary upload error: ' . $cloudinaryError->getMessage());
                Yii::error('Base64 data preview: ' . substr($base64Screenshot, 0, 100) . '...');
                
                // Store the original base64 data as fallback
                $model->screenshot = $base64Screenshot;
                
                throw new \Exception('Cloudinary configuration error: ' . $cloudinaryError->getMessage());
            }
            
            // Replace these direct POST assignments with proper format
            $model->attributes = [
                'module' => Yii::$app->request->post('Ticket')['module'],
                'issue' => Yii::$app->request->post('Ticket')['issue'],
                'description' => Yii::$app->request->post('Ticket')['description'],
                'severity' => Yii::$app->request->post('Ticket')['severity'],
                'user_id' => Yii::$app->user->id,
                'created_by' => Yii::$app->user->id,
                'status' => 'pending',
                'company_name' => Yii::$app->user->identity->company_name ?? '(not set)',
                'company_email' => $userInfo['company_email'] ?? null,
                'severity_level' => Yii::$app->request->post('Ticket')['severity'] ?? 3,
                'sla_status' => 'pending'
            ];

            // Debug the values before save
            Yii::debug('Model attributes before save: ' . print_r($model->attributes, true));

            if ($model->getIsNewRecord()) {
                $model->created_at = new \yii\db\Expression('DATEDIFF(SECOND, \'1970-01-01\', GETDATE())');
                $model->last_update_at = new \yii\db\Expression('DATEDIFF(SECOND, \'1970-01-01\', GETDATE())');
                $model->resolution_deadline = new \yii\db\Expression('DATEDIFF(SECOND, \'1970-01-01\', DATEADD(MINUTE, 2880, GETDATE()))');
                $model->next_update_due = new \yii\db\Expression('DATEDIFF(SECOND, \'1970-01-01\', DATEADD(MINUTE, 480, GETDATE()))');
            }
    
            // Try to save with detailed error logging
            if (!$model->save()) {
                Yii::error('Model validation errors: ' . print_r($model->errors, true));
                throw new \Exception('Validation failed: ' . json_encode($model->errors));
            }

            // Verify data was saved
            $savedTicket = Ticket::findOne($model->id);
            if (!$savedTicket) {
                throw new \Exception('Could not find saved ticket');
            }

            // Check if we have either a Cloudinary URL or a local screenshot
            if (empty($savedTicket->screenshot_url) && empty($savedTicket->screenshot)) {
                // Log the actual database values
                $dbValues = Yii::$app->db->createCommand('
                    SELECT screenshot, screenshot_url FROM ticket WHERE id = :id
                ', [':id' => $savedTicket->id])->queryOne();
                
                Yii::error('Screenshot data missing from database. DB values: ' . print_r($dbValues, true));
                throw new \Exception('No screenshot data was saved to database');
            }

            $transaction->commit();
            
            // Success message indicates where the image was stored
            $successMessage = 'Ticket created successfully';
            if (!empty($savedTicket->screenshot_url)) {
                $successMessage .= ' with screenshot uploaded to Cloudinary';
            } else {
                $successMessage .= ' with screenshot stored in database';
            }
            
            Yii::$app->session->setFlash('success', $successMessage);
            return $this->redirect(['view', 'id' => $model->id]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            if (isset($transaction)) {
                $transaction->rollBack();
            }
            
            Yii::error('Screenshot processing error: ' . $e->getMessage());
            Yii::error('Error trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    return $this->render('create', [
        'model' => $model,
        'companyModules' => $companyModules,
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
                    "Screenshot: " . ($model->screenshot ? $model->screenshot : 'No screenshot attached') . "\n\n" .
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
            $result = $this->convertToBase64($tempFile, 'voice');

            if (!$result['success']) {
                throw new \Exception($result['error']);
            }

            return [
                'success' => true,
                'url' => $result['url'],
                'format' => $result['format'] ?? null
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

    /**
     * Get human-readable error message for file upload errors
     * @param int $errorCode PHP file upload error code
     * @return string Human-readable error message
     */
    private function getUploadErrorMessage($errorCode) 
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder for file uploads';
 case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    public function actionGetMessages($ticket_id)
    {
        Yii::info('actionGetMessages called with ticket_id: ' . $ticket_id, 'ticket');

        if (!Yii::$app->request->isAjax) {
            Yii::warning('Non-AJAX request to actionGetMessages', 'ticket');
            throw new MethodNotAllowedHttpException('Method not allowed');
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            // Validate ticket_id is numeric
            if (!is_numeric($ticket_id)) {
                throw new \Exception('Invalid ticket ID format');
            }

            // Check if ticket exists
            $ticket = Ticket::findOne($ticket_id);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            // Check if current user has access to this ticket
            if (Yii::$app->user->id != $ticket->user_id && Yii::$app->user->identity->role != 'admin' && 
                Yii::$app->user->identity->role != 'developer' && Yii::$app->user->id != $ticket->assigned_to) {
                throw new \Exception('You do not have permission to view messages for this ticket');
            }

            $messages = TicketMessage::find()
                ->where(['ticket_id' => $ticket_id])
                ->andWhere(['is_internal' => 0]) // Only non-internal messages
                ->orderBy(['sent_at' => SORT_DESC])
                ->all();

            Yii::info('Found ' . count($messages) . ' messages for ticket #' . $ticket_id, 'ticket');

            $formattedMessages = [];
            foreach ($messages as $message) {
                $sender = User::findOne($message->sender_id);
                $formattedMessages[] = [
                    'subject' => Html::encode($message->subject),
                    'message' => nl2br(Html::encode($message->message)),
                    'sent_at' => Yii::$app->formatter->asDatetime($message->sent_at),
                    'sender_name' => $sender ? Html::encode($sender->name) : 'Unknown',
                    'message_type' => $message->message_type,
                ];
            }

            return [
                'success' => true,
                'messages' => $formattedMessages,
            ];

        } catch (\Exception $e) {
            Yii::error('Error in actionGetMessages: ' . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString(), 'ticket');
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'Failed to load messages'
            ];
        }
    }

}
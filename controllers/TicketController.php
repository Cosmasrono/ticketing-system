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
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'close'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->identity->role === 'admin') {
                                Yii::$app->session->setFlash('error', 'Administrators do not have access to tickets.');
                                Yii::$app->response->redirect(['/site/admin'])->send();
                                return false;
                            }
                            return true;
                        },
                        'denyCallback' => function ($rule, $action) {
                            throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page.');
                        }
                    ],
                    [
                        'actions' => ['get-issues'],
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users
                    ],
                    [
                        'actions' => ['create', 'view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['update', 'delete', 'assign', 'admin'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return in_array(Yii::$app->user->identity->role, ['admin', 'superadmin']);
                        }
                    ],
                    [
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['@'],  // Allow authenticated users
                    ],

                    [
                        'actions' => ['close', 'view'],
                        'allow' => true,
                        'roles' => ['developer'],
                    ],
                    [
                        'actions' => ['escalate', 'view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Only allow developers and superadmins to escalate tickets
                            $allowedRoles = ['developer', 'superadmin'];
                            return in_array(Yii::$app->user->identity->role, $allowedRoles);
                        }
                    ],
                    // Rule for regular users - Added delete action
                    [
                        'actions' => ['create', 'view', 'index', 'delete', 'reopen'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->identity->role === 'user') {
                                if (in_array($action->id, ['delete', 'reopen'])) {
                                    $ticketId = Yii::$app->request->get('id') ?? Yii::$app->request->post('id');
                                    $ticket = Ticket::findOne($ticketId);
                                    return $ticket && $ticket->created_by === Yii::$app->user->id;
                                }
                                return true;
                            }
                            return false;
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
                        'actions' => ['view', 'index','close'],
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
                    [
                        'actions' => ['reopen'],
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users
                    ],
                    // Rule for developers to view assigned tickets
                    [
                        'actions' => ['view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->identity->role === 'developer') {
                                if ($action->id === 'view') {
                                    $ticketId = Yii::$app->request->get('id');
                                    $ticket = Ticket::findOne($ticketId);
                                    return $ticket && $ticket->assigned_to === Yii::$app->user->id;
                                }
                                // For index action, the filtering will be done in the action itself
                                return true;
                            }
                            return false;
                        }
                    ],
                    // Rule for developers to view their dashboard
                    [
                        'actions' => ['developer/view', 'view', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'developer';
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'], // @ means authenticated users
                        'matchCallback' => function ($rule, $action) {
                            $id = Yii::$app->request->get('id');
                            $ticket = $this->findModel($id);
                            $user = Yii::$app->user->identity;
                            
                            // Allow if user owns the ticket
                            return $ticket->created_by === $user->id;
                        }
                    ],
                    [
                        'actions' => ['assigned'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'developer';
                        }
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            if ($user->role === 'developer') {
                                $ticketId = Yii::$app->request->get('id');
                                $ticket = $this->findModel($ticketId);
                                return $ticket->assigned_to == $user->id;
                            }
                            return true; // Allow other roles
                        }
                    ],
                ],
                // 'denyCallback' => function ($rule, $action) {
                //     Yii::$app->session->setFlash('error', 'You are not authorized to perform this action.');
                //     return $this->redirect(['site/index']);
                // }
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
                    'get-issues' => ['post'],
                ],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if the action is 'close'
        if ($action->id === 'close') {
            // Allow only the ticket creator to close the ticket
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
            ->where(['created_by' => Yii::$app->user->id]); // Show only tickets created by current user

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC, // Latest tickets first
                ]
            ],
            'pagination' => [
                'pageSize' => 10, // Number of tickets per page
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
        // Set JSON response format for AJAX requests
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        // Get developers first
        $developers = User::find()
            ->where(['role' => User::ROLE_DEVELOPER])
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

        $ticket = Ticket::findOne($id);
        if (!$ticket) {
            throw new NotFoundHttpException('The requested ticket does not exist.');
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $developerId = Yii::$app->request->post('Ticket')['assigned_to'] ?? null;
                
                // Validate developer exists in our fetched list
                $developerExists = false;
                foreach ($developers as $developer) {
                    if ($developer->id == $developerId) {
                        $developerExists = true;
                        break;
                    }
                }

                if (!$developerExists) {
                    throw new \Exception('Selected developer is not valid');
                }

                $ticket->assigned_to = $developerId;
                
                if ($ticket->save(false)) {
                    // Check if it's an escalated ticket
                    if ($ticket->status === 'escalated') {
                        $ticket->status = 'reassigned';
                        $ticket->save(false);
                        $message = 'Developer reassigned successfully';
                    } else {
                        $message = 'Developer assigned successfully';
                    }

                    $transaction->commit();
                    
                    // Send email notification
                    try {
                        $emailSent = Yii::$app->mailer->compose('assignmentNotification', [
                            'developer_name' => $developer->name,  // Using name
                            'ticket_id' => $ticket->id,
                            'company_name' => $ticket->company_name,
                            'description' => $ticket->description,
                            'module' => $ticket->module,
                            'issue' => $ticket->issue
                        ])
                        ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                        ->setTo([$developer->company_email => $developer->name])  // Using name in recipient
                        ->setSubject("Ticket Assignment #{$ticket->id} - {$ticket->company_name}")
                        ->send();

                        if (!$emailSent) {
                            Yii::error('Failed to send email notification to developer: ' . $developer->name);
                        }
                    } catch (\Exception $e) {
                        Yii::error('Email sending failed for developer ' . $developer->name . ': ' . $e->getMessage());
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
                    'message' => ($ticket->status === 'escalated' ? 'Failed to reassign' : 'Failed to assign') . 
                                ' developer: ' . $e->getMessage()
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

    private function sendEmailNotification($to, $ticket, $reason, $recipientType)
    {
        try {
            $subject = "Ticket #{$ticket->id} Has Been Reopened";
            
            $emailContent = $this->renderPartial('//mail/ticket-reopen', [
                'ticket' => $ticket,
                'reason' => $reason,
                'recipientType' => $recipientType,
                'viewUrl' => Yii::$app->urlManager->createAbsoluteUrl(['ticket/view', 'id' => $ticket->id])
            ]);

            $sent = Yii::$app->mailer->compose()
                ->setHtmlBody($emailContent)
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                ->setTo($to)
                ->setSubject($subject)
                ->send();

            if ($sent) {
                Yii::info("Reopen notification email sent to $to", 'ticket');
            } else {
                Yii::error("Failed to send reopen notification to $to", 'ticket');
            }

        } catch (\Exception $e) {
            Yii::error("Error sending email to $to: " . $e->getMessage(), 'ticket');
        }
    }

    public function actionCreate()
    {
        $model = new Ticket();
        $user = Yii::$app->user->identity;
        
        // Get user's assigned modules from selectedModules column
        $userModules = array_map('trim', explode(',', $user->selectedModules));
        $modulesList = array_combine($userModules, $userModules); // Create associative array

        if ($model->load(Yii::$app->request->post())) {
            // Validate if selected module is assigned to user
            if (!in_array($model->selectedModule, $userModules)) {
                Yii::$app->session->setFlash('error', 'You are not authorized to create tickets for this module.');
                return $this->redirect(['create']);
            }

            $model->created_at = date('Y-m-d H:i:s');
            $model->created_by = $user->id;
            $model->company_name = $user->company_name;
            $model->company_email = $user->company_email;
            $model->status = Ticket::STATUS_PENDING;
            $model->module = $model->selectedModule;

            // Handle file upload
            $model->screenshot = UploadedFile::getInstance($model, 'screenshot');
            if ($model->screenshot) {
                $fileName = 'ticket_' . time() . '.' . $model->screenshot->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/');
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                $model->screenshot->saveAs($uploadPath . $fileName);
                $model->screenshot = $fileName;
            }

            if ($model->save()) {
                // Send email notification
                $this->sendTicketNotification($model, $user);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modulesList' => $modulesList,
        ]);
    }

    // Helper method for email notification
    private function sendTicketNotification($model, $user)
    {
        $htmlContent = $this->renderPartial('@app/mail/ticketNotification', [
            'username' => $user->username,
            'company_name' => $model->company_name,
            'description' => $model->description,
            'ticketId' => $model->id,
            'module' => $model->selectedModule,
            'issue' => $model->issue
        ]);

        $emailSent = Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->params['senderEmail'] => 'Ticket System'])
            ->setTo([Yii::$app->params['adminEmail'] => 'Admin'])
            ->setReplyTo([$user->company_email => $user->username])
            ->setSubject("New Ticket #{$model->id} - {$model->company_name} - {$model->selectedModule}")
            ->setHtmlBody($htmlContent)
            ->send();

        Yii::$app->session->setFlash(
            $emailSent ? 'success' : 'warning',
            $emailSent 
                ? 'Ticket created successfully and email notification sent.'
                : 'Ticket created but email notification failed.'
        );
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

}








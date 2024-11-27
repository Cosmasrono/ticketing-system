<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\User;
use app\models\Ticket;
use yii\data\ActiveDataProvider;
use app\models\Developer;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Admin;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use app\components\BrevoMailer;
use yii\web\ServerErrorHttpException;
use app\models\Client;
use app\models\Invitation;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use app\models\FirstLoginForm;

 


class SiteController extends Controller

{
     
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['create-user'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                    // Public actions (no login required)
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    // Admin only actions
                    [
                        'actions' => ['dashboard', 'admin'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                    // Regular user actions
                    [
                        'actions' => ['index', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->response->redirect(['site/login']);
                    } else {
                        throw new \yii\web\ForbiddenHttpException('You are not allowed to perform this action.');
                    }
                }
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'create-user' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    /**
     * Displays homepage.
     *
     * @return string
     */
  

    // public function actionApproveTicket()
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $id = Yii::$app->request->post('id');
    //     $ticket = Ticket::findOne($id);

    //     if ($ticket) {
    //         $ticket->status = 'approved'; // Change the status
    //         if ($ticket->save()) {
    //             return ['success' => true];
    //         } else {
    //             return ['success' => false, 'message' => implode(', ', $ticket->getErrors())];
    //         }
    //     }
    //     return ['success' => false, 'message' => 'Ticket not found.'];
    // }

    // public function actionCancelTicket()
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $id = Yii::$app->request->post('id');
    //     $ticket = Ticket::findOne($id);

    //     if ($ticket) {
    //         $ticket->status = 'canceled'; // Change the status
    //         if ($ticket->save()) {
    //             return ['success' => true];
    //         } else {
    //             return ['success' => false, 'message' => implode(', ', $ticket->getErrors())];
    //         }
    //     }
    //     return ['success' => false, 'message' => 'Ticket not found.'];
    // }


    public function actionIndex()
    {
        Yii::debug('Accessing index action');
        // Remove any permission checks here
        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    
    // The following code is now commented out
    // if (Yii::$app->user->can('viewReports')) {
    //     return $this->render('index');
    // } else {
    //     return $this->render('no-access');
    // }

    // ... rest of the method (if any) ...
}
 
public function actionSignup($token = null)
{
    if (!$token) {
        throw new NotFoundHttpException('Invalid invitation token.');
    }

    $invitation = Invitation::findOne(['token' => $token]);
    if (!$invitation) {
        throw new NotFoundHttpException('Invalid invitation token.');
    }

    $model = new SignupForm();
    $model->company_email = $invitation->company_email;
    $model->role = $invitation->role;

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->name = $model->name;
            $user->company_email = $model->company_email;
            $user->company_name = $model->company_name;
            $user->setPassword($model->password);
            $user->generateAuthKey();
            $user->role = $model->role;

            if ($user->save()) {
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                return $this->goHome();
            } else {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'There was an error saving your account: ' . json_encode($user->errors));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'An error occurred during registration: ' . $e->getMessage());
        }
    }

    return $this->render('signup', [
        'model' => $model,
    ]);
}

    

    private function sendVerificationEmail($user, $verificationLink)
{
    try {
        Yii::info("Attempting to send verification email to: " . $user->company_email);
        Yii::info("Verification link: " . $verificationLink);

        $result = Yii::$app->mailer->compose('emailVerify-html', ['user' => $user, 'verificationLink' => $verificationLink])
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($user->company_email)
            ->setSubject('Verify your company email')
            ->send();

        if ($result) {
            Yii::info("Verification email sent successfully to: " . $user->company_email);
            return ['success' => true, 'message' => 'Verification email sent successfully.'];
        } else {
            Yii::error("Failed to send email to: " . $user->company_email);
            return ['success' => false, 'message' => 'Failed to send verification email. Please try again later.'];
        }
    } catch (\Exception $e) {
        Yii::error("Exception when sending email to " . $user->company_email . ": " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while sending the verification email: ' . $e->getMessage()];
    }
}



    public function actionVerifyEmail($token, $companyEmail)
{
    // Log the incoming token and company email for debugging
    Yii::info("Attempting to verify email with token: " . substr($token, 0, 10) . ' and company email: ' . $companyEmail);

    // Call the method to find the user by token and company email
    $user = User::findByVerificationToken($token, $companyEmail);

    if (!$user) {
        Yii::$app->session->setFlash('error', 'Invalid or expired verification token.');
        return $this->redirect(['site/index']);
    }

    // Proceed with verification if the user is found
    $user->status = User::STATUS_ACTIVE;
    $user->verification_token = null; // Clear the token
    $user->save(false); // Save the changes without validation

    Yii::$app->session->setFlash('success', 'Your email has been successfully verified.');
    return $this->redirect(['site/index']);
}

    
    /**
     * Login action.
     *
     * @return Response|string**/


     public function developer()
     {
       return $this->render('view');

     }
// public function actionDeveloperDashboard($id)
// {
//     // Ensure the user is logged in
//     if (Yii::$app->user->isGuest) {
//         return $this->redirect(['site/login']);
//     }

//     // Find the developer by ID
//     $developer = User::findOne(['id' => $id, 'role' => 'developer']);
//         if (!$developer) {
//         throw new NotFoundHttpException('The requested page does not exist.');
//     }

//     // Ensure the logged-in user is accessing their own dashboard
//     if (Yii::$app->user->id !== $developer->id) {
//         throw new ForbiddenHttpException('You are not authorized to view this page.');
//     }
// }
 
     public function actionLogin()
     {
         if (!Yii::$app->user->isGuest) {
             return $this->redirect(['/site/index']); // Already logged in users
         }

         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             // After successful login, redirect to index page for all roles
             return $this->redirect(['/site/index']);
         }

         $model->password = '';
         return $this->render('login', [
             'model' => $model,
         ]);
     }
     
    /**
     * Logout action.
     *
     * @return Response
     */

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        // If you want to show all developers on the about page
        $developers = User::findAll(['role' => 'developer']);

        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('about', [
            'dataProvider' => $dataProvider,
            'developers' => $developers,
        ]);
    }

    public static function getStatusCounts()
{
    $counts = static::find()
        ->select(['status', 'COUNT(*) as count'])
        ->groupBy('status')
        ->indexBy('status')
        ->column();

    $statuses = ['approved', 'assigned', 'pending', 'cancelled'];
    foreach ($statuses as $status) {
        if (!isset($counts[$status])) {
            $counts[$status] = 0;
        }
    }

    return $counts;
}

public function actionDevelopers()
{
    $developers = User::find()->where(['role' => 'developer'])->all();
    
    $assignedTickets = [];
    foreach ($developers as $developer) {
        $assignedTickets[$developer->id] = Ticket::find()
            ->where(['assigned_to' => $developer->id])
            ->all();
    }

    return $this->render('developers', [
        'developers' => $developers,
        'assignedTickets' => $assignedTickets,
    ]);
}

public function actionDeveloperDashboard()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    $user = Yii::$app->user->identity;
    if ($user->isDeveloper()) {
        $developer = Developer::findOne(['email' => $user->company_email]);
        if ($developer) {
            $assignedTickets = $developer->assignedTickets;
            return $this->render('developer-dashboard', [
                'developer' => $developer,
                'assignedTickets' => $assignedTickets,
            ]);
        } else {
            Yii::error('Developer not found for email: ' . $user->company_email, __METHOD__);
        }
    } else {
        Yii::error('User is not a developer: ' . $user->company_email, __METHOD__);
    }

    throw new ForbiddenHttpException('You are not authorized to view this page.');
}


public function actionAdmin()
{
    if (Yii::$app->user->identity->role !== 'admin') {
        Yii::$app->session->setFlash('error', 'You do not have permission to access the admin area.');
        return $this->redirect(['/site/index']);
    }

    // Create data provider for tickets
    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => Ticket::find(),
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'defaultOrder' => ['id' => SORT_DESC]
        ]
    ]);

    // Get ticket counts for different statuses
    $ticketCounts = [
        'total' => Ticket::find()->count(),
        'pending' => Ticket::find()->where(['status' => Ticket::STATUS_PENDING])->count(),
        'approved' => Ticket::find()->where(['status' => Ticket::STATUS_APPROVED])->count(),
        'cancelled' => Ticket::find()->where(['status' => Ticket::STATUS_CANCELLED])->count(),
        'assigned' => Ticket::find()->where(['not', ['assigned_to' => null]])->count(),
        'notAssigned' => Ticket::find()->where(['assigned_to' => null])->count(),
        'closed' => Ticket::find()->where(['status' => Ticket::STATUS_CLOSED])->count(),
        'reopen' => Ticket::find()->where(['status' => Ticket::STATUS_REOPEN])->count(),
        'reassigned' => Ticket::find()->where(['status' => Ticket::STATUS_REASSIGNED])->count(),
        'escalated' => Ticket::find()->where(['status' => Ticket::STATUS_ESCALATED])->count(),
    ];

    return $this->render('admin', [
        'dataProvider' => $dataProvider,
        'ticketCounts' => $ticketCounts,
    ]);
}

 

public function actionRequestPasswordReset()
{
    $model = new PasswordResetRequestForm();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your company email for further instructions.');
            return $this->goHome();
        }
        Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided company email address.');
    }

    return $this->render('requestPasswordResetToken', [
        'model' => $model,
    ]);
}

public function actionResetPassword($token)
{
    Yii::info("Reset password action called with token: " . $token);

    try {
        $model = new ResetPasswordForm($token);
    } catch (InvalidArgumentException $e) {
        Yii::error("Reset password form creation failed: " . $e->getMessage());
        throw new BadRequestHttpException($e->getMessage());
    }

    if ($model->load(Yii::$app->request->post())) {
        Yii::info("Form loaded with POST data");
        
        if ($model->validate() && $model->resetPassword()) {
            Yii::info("Password reset successful");
            Yii::$app->session->setFlash('success', 'New password saved.');
            return $this->goHome();
        } else {
            Yii::error("Password reset failed. Validation errors: " . json_encode($model->errors));
        }
    }

    return $this->render('resetPassword', [
        'model' => $model,
    ]);
}

public function actionForgotPassword()
{
    $model = new ForgotPasswordForm();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendResetLink()) {
            Yii::$app->session->setFlash('success', 'If an account exists for this email, a password reset link has been sent. Please check your email for further instructions.');
            return $this->goHome();
        } else {
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to process your request at this time. Please try again later.');
        }
    }

    return $this->render('forgotPassword', [
        'model' => $model,
    ]);
}


//email verification


// public function actionVerifyEmail($token)
// {
//     $user = User::findByVerificationToken($token);
//     if (!$user) {
//         throw new NotFoundHttpException('The verification link is invalid or has expired.');
//     }

//     if ($user->verify()) {
//         Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
//         return $this->goHome();
//     }

//     Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
//     return $this->goHome();
// }

public function actionInvite()
{
    $model = new Invitation();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        // Send invitation email
        Yii::$app->mailer->compose()
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setTo($model->company_email)
            ->setSubject('Invitation to join')
            ->setTextBody("You've been invited to join as a {$model->role}. Click here to sign up: " . 
                Yii::$app->urlManager->createAbsoluteUrl(['site/signup', 'token' => $model->token]))
            ->send();

        Yii::$app->session->setFlash('success', 'Invitation sent successfully.');
        return $this->redirect(['index']);
    }

    return $this->render('invite', [
        'model' => $model,
    ]);
}

public function actionInvitation()
{
    $model = new Invitation();

    if ($model->load(Yii::$app->request->post())) {
        if ($model->save()) {
            try {
                if ($model->sendInvitationEmail()) {
                    Yii::$app->session->setFlash('success', 
                        'Invitation sent successfully to ' . $model->company_email);
                    
                    // Debug information
                    Yii::debug('Invitation URL: ' . Yii::$app->urlManager->createAbsoluteUrl([
                        'site/signup',
                        'token' => $model->token
                    ]));
                    
                    return $this->redirect(['index']);
                } else {
                    throw new \Exception('Failed to send email');
                }
            } catch (\Exception $e) {
                Yii::error('Email sending failed: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 
                    'Failed to send invitation email. Please try again later.');
            }
        } else {
            Yii::$app->session->setFlash('error', 
                'Failed to save invitation: ' . json_encode($model->errors));
        }
    }

    return $this->render('invitation', [
        'model' => $model,
    ]);
}


public function actionSendInvitation()
{
    $model = new Invitation();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        if ($model->sendInvitationEmail()) {
            Yii::$app->session->setFlash('success', 'Invitation sent successfully to ' . $model->company_email);
            return $this->redirect(['admin']); // Adjust this redirect as needed
        } else {
            Yii::$app->session->setFlash('error', 'Failed to send invitation email.');
        }
    }

    return $this->render('send-invitation', [
        'model' => $model,
    ]);
}


public function actionDebugRbac()
{
    Yii::$app->response->format = Response::FORMAT_HTML;

    $auth = Yii::$app->authManager;
    $userId = Yii::$app->user->id;

    $output = "<h1>RBAC Debug Information</h1>";
    $output .= "<p>User ID: $userId</p>";
    $output .= "<p>Is Guest: " . (Yii::$app->user->isGuest ? 'Yes' : 'No') . "</p>";

    $roles = $auth->getRolesByUser($userId);
    $roleNames = array_keys($roles);
    $output .= "<p>User Roles: " . implode(', ', $roleNames) . "</p>";

    $output .= "<h2>Permissions</h2>";
    $permissions = ['admin', 'approveTicket', 'createTicket', 'viewTicket', 'deleteTicket'];
    foreach ($permissions as $permission) {
        $output .= "<p>Can $permission: " . (Yii::$app->user->can($permission) ? 'Yes' : 'No') . "</p>";
    }

    $output .= "<h2>Role Permissions</h2>";
    foreach ($roles as $roleName => $role) {
        $output .= "<h3>Role: $roleName</h3>";
        $rolePermissions = $auth->getPermissionsByRole($roleName);
        foreach ($rolePermissions as $permission) {
            $output .= "<p>{$permission->name}</p>";
        }
    }

    return $output;
}

public function actionDebugPermissions()
{
    if (Yii::$app->user->isGuest) {
        return 'User is not logged in';
    }

    $userId = Yii::$app->user->id;
    $isAdmin = Yii::$app->user->can('admin');
    $canAssignTicket = Yii::$app->user->can('assignTicket');

    return "User ID: $userId<br>Is Admin: " . ($isAdmin ? 'Yes' : 'No') . "<br>Can Assign Ticket: " . ($canAssignTicket ? 'Yes' : 'No');
}

public function actionDebug()
{
    if (Yii::$app->user->isGuest) {
        return 'User is not logged in';
    }

    $user = Yii::$app->user->identity;
    $isAdmin = strpos($user->company_email, 'admin') !== false;

    return "User ID: {$user->id}<br>
            Email: {$user->company_email}<br>
            Is Admin: " . ($isAdmin ? 'Yes' : 'No');
}

public function actionGetIssues($module)
{
    $issues = [
        'HR' => ['Payroll', 'Recruitment', 'Employee Relations'],
        'IT' => ['Network Issues', 'Software Bugs', 'Hardware Failures'],
        // Add more modules and their issues as needed
    ];

    $options = Html::tag('option', 'Select Issue', ['value' => '']);
    foreach ($issues[$module] as $issue) {
        $options .= Html::tag('option', $issue, ['value' => $issue]);
    }

    return $options;
}

public function actionAcceptInvitation($token)
{
    $invitation = Invitation::findByToken($token);

    if (!$invitation) {
        Yii::$app->session->setFlash('error', 'Invalid or expired invitation token.');
        return $this->redirect(['site/index']);
    }

    $user = Yii::$app->user->identity;

    if ($user) {
        // Update user's company email and module if needed
        $user->company_email = $invitation->company_email;
        $user->module = $invitation->module;
        if ($user->save()) {
            // Mark the invitation as used
            $invitation->markAsUsed();
            Yii::$app->session->setFlash('success', 'Invitation accepted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update user information.');
        }
    } else {
        // Handle case where user is not logged in
        // You might want to redirect to registration page with pre-filled email
        return $this->redirect(['site/register', 'email' => $invitation->company_email]);
    }

    return $this->redirect(['site/index']);
}

public function beforeAction($action)
{
    if (!parent::beforeAction($action)) {
        return false;
    }

    if (!Yii::$app->user->isGuest) {
        $latestTicket = Ticket::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
        
        Yii::$app->view->params['companyName'] = $latestTicket ? $latestTicket->company_name : 'Unknown Company';
    }

    return true;
}

public function actionSuperAdmin()
{
    return $this->render('super-admin');
}

public function actionApproveTicket($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $ticket = Ticket::findOne($id);
    if (!$ticket) {
        return ['success' => false, 'message' => 'Ticket not found'];
    }

    Yii::info("Original ticket data: " . json_encode($ticket->attributes), 'ticket');

    $ticket->status = Ticket::STATUS_APPROVED;
    
    // Ensure created_at is an integer
    if (!is_int($ticket->created_at)) {
        $ticket->created_at = (int)$ticket->created_at;
        if (!is_int($ticket->created_at)) {
            $ticket->created_at = time();
        }
    }

    Yii::info("Modified ticket data: " . json_encode($ticket->attributes), 'ticket');

    if ($ticket->save()) {
        return ['success' => true, 'message' => 'Ticket approved successfully'];
    } else {
        Yii::error("Failed to approve ticket. Errors: " . json_encode($ticket->errors), 'ticket');
        Yii::error("Failed ticket data: " . json_encode($ticket->attributes), 'ticket');
        return [
            'success' => false, 
            'message' => 'Failed to approve ticket', 
            'errors' => $ticket->errors,
            'ticketData' => $ticket->attributes
        ];
    }
}

public function actionDeleteTicket($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $ticket = Ticket::findOne($id);
    if (!$ticket) {
        return ['success' => false, 'message' => 'Ticket not found'];
    }

    $ticket->status = 'deleted';
    
    if ($ticket->save()) {
        return [
            'success' => true, 
            'message' => 'Ticket deleted successfully',
            'deletedCount' => Ticket::find()->where(['status' => 'deleted'])->count()
        ];
    } else {
        return [
            'success' => false, 
            'message' => 'Failed to delete ticket',
            'errors' => $ticket->errors
        ];
    }
}

// public function actionSendInvitation()
// {
//     // Generate unique token
//     $token = Yii::$app->security->generateRandomString(32);
    
//     // Save invitation to database
//     $invitation = new Invitation();
//     $invitation->email = 'recipient@email.com'; // Get this from form or parameter
//     $invitation->token = $token;
//     $invitation->created_at = time();
//     $invitation->expires_at = time() + 3600; // Expires in 1 hour
    
//     if ($invitation->save()) {
//         // Send email
//         $sent = Yii::$app->mailer->compose(['html' => 'invitation-html', 'text' => 'invitation'],
//             ['token' => $token])
//             ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->name])
//             ->setTo($invitation->email)
//             ->setSubject('Invitation to join ' . Yii::$app->name)
//             ->send();

//         if ($sent) {
//             Yii::$app->session->setFlash('success', 'Invitation sent successfully.');
//         }
//     }
    
//     return $this->redirect(['index']);
// }

// Add action for handling the signup from invitation
public function actionSignupFromInvitation($token)
{
    $invitation = Invitation::findOne(['token' => $token, 'used' => false]);
    
    if (!$invitation || $invitation->expires_at < time()) {
        throw new NotFoundHttpException('Invalid or expired invitation.');
    }

    $model = new SignupForm();

    if ($model->load(Yii::$app->request->post()) && $model->signup()) {
        // Mark invitation as used
        $invitation->used = true;
        $invitation->save();
        
        Yii::$app->session->setFlash('success', 'Thank you for registration.');
        return $this->redirect(['site/login']);
    }

    return $this->render('signup', [
        'model' => $model,
        'email' => $invitation->email
    ]);
}

public function actionDeveloperTickets()
{
    if (!Yii::$app->user->identity->isAdmin()) {
        throw new ForbiddenHttpException('You are not authorized to view this page.');
    }

    $developerTicketCounts = (new \yii\db\Query())
        ->select([
            'name' => 'user.name',
            'ticket_count' => 'COUNT(ticket.id)'
        ])
        ->from('user')
        ->leftJoin('ticket', 'ticket.assigned_to = user.id')
        ->where(['user.role' => 'developer'])
        ->groupBy('user.id, user.name')
        ->all();

    return $this->render('developer-tickets', [
        'developerTicketCounts' => $developerTicketCounts,
    ]);
}

public function actionDashboard()
{
    if (Yii::$app->user->identity->role !== 'admin') {
        Yii::$app->session->setFlash('error', 'Access denied.');
        return $this->redirect(['site/index']);
    }

    // Get developer statistics
    $developerStats = User::find()
        ->select([
            'user.id',
            'user.name',
            'COUNT(CASE WHEN ticket.status != "closed" THEN 1 END) as active_tickets',
            'COUNT(CASE WHEN ticket.status = "closed" THEN 1 END) as completed_tickets',
            'COUNT(ticket.id) as total_tickets'
        ])
        ->leftJoin('ticket', 'ticket.assigned_to = user.id')
        ->where(['user.role' => 'developer'])
        ->groupBy(['user.id', 'user.name'])
        ->asArray()
        ->all();

    // Get ticket statistics
    $ticketStats = [
        'total' => Ticket::find()->count(),
        'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
        'assigned' => Ticket::find()->where(['status' => 'assigned'])->count(),
        'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
    ];

    // Prepare data for the doughnut chart
    $ticketStatusData = [
        'Pending' => $ticketStats['pending'],
        'Assigned' => $ticketStats['assigned'],
        'Closed' => $ticketStats['closed'],
    ];

    // Get recent tickets and activity
    $recentTickets = Ticket::find()
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    // Get recent activity with developer info and escalation comments
    $recentActivity = (new \yii\db\Query())
        ->select([
            'ticket.id as ticket_id',
            'ticket.status',
            'ticket.created_at as timestamp',
            'assigned_user.name as developer',
            'escalated_user.name as escalated_to',
            'ticket_comments.comment as escalation_comment'
        ])
        ->from('ticket')
        ->leftJoin('user assigned_user', 'ticket.assigned_to = assigned_user.id')
        ->leftJoin('user escalated_user', 'ticket.escalated_to = escalated_user.id')
        ->leftJoin('ticket_comments', 'ticket.id = ticket_comments.ticket_id AND ticket_comments.type = "escalation"')
        ->orderBy(['ticket.created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    return $this->render('dashboard', [
        'developerStats' => $developerStats,
        'ticketStats' => $ticketStats,
        'recentTickets' => $recentTickets,
        'recentActivity' => $recentActivity,
        'ticketStatusData' => $ticketStatusData,
    ]);
}

private function getStatusColor($status)
{
    return [
        'assigned' => 'primary',
        'closed' => 'success',
        'pending' => 'warning',
        'urgent' => 'danger',
    ][$status] ?? 'secondary';
}

private function getStatusLabel($status)
{
    return [
        'assigned' => 'Assigned',
        'closed' => 'Resolved',
        'pending' => 'Pending',
        'urgent' => 'Urgent',
    ][$status] ?? ucfirst($status);
}

public function actionCreateClient()
{
    // Check if user is admin
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->company_email !== 'ccosmas001@gmail.com') {
        throw new ForbiddenHttpException('You are not authorized to perform this action.');
    }

    $model = new SignupForm();
    // Set default role for clients
    $model->role = 'client';

    if ($model->load(Yii::$app->request->post())) {
        if ($user = $model->signup()) {
            Yii::$app->session->setFlash('success', 'Client account created successfully. Login credentials have been sent to ' . $user->company_email);
            return $this->redirect(['admin']);
        } else {
            Yii::$app->session->setFlash('error', 'There was an error creating the client account.');
        }
    }

    return $this->render('create-client', [
        'model' => $model,
    ]);
}

public function actionCreateUser()
{
    // Check if user is logged in and is admin
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== 'admin') {
        Yii::$app->session->setFlash('error', 'Access denied. Only administrators can create new users.');
        return $this->redirect(['site/index']);
    }

    $model = new User();
    $model->scenario = 'create'; // If you have a specific scenario for user creation

    if ($model->load(Yii::$app->request->post())) {
        // Set default values or additional processing
        $model->auth_key = Yii::$app->security->generateRandomString();
        $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'User created successfully.');
            return $this->redirect(['site/admin']); // or wherever you want to redirect after success
        }
    }

    return $this->render('create-user', [
        'model' => $model,
    ]);
}

/**
 * Check if required columns exist in the specified table
 * @param string $tableName
 * @param array $requiredColumns
 * @return array Missing columns
 */
private function checkMissingColumns($tableName, $requiredColumns)
{
    $schema = Yii::$app->db->schema;
    $tableSchema = $schema->getTableSchema($tableName);
    
    if ($tableSchema === null) {
        throw new \yii\base\Exception("Table '$tableName' does not exist!");
    }

    $existingColumns = array_keys($tableSchema->columns);
    return array_diff($requiredColumns, $existingColumns);
}

public function actionFirstLogin($token)
{
    $user = User::findOne(['password_reset_token' => $token]);
    
    if (!$user) {
        Yii::error("Invalid token used: " . $token);
        Yii::$app->session->setFlash('error', 'Invalid or expired password reset token.');
        return $this->redirect(['site/login']);
    }

    $model = new FirstLoginForm();
    $model->token = $token;

    if ($model->load(Yii::$app->request->post())) {
        try {
            if ($model->changePassword()) {
                Yii::$app->session->setFlash('success', 'Password changed successfully. Please login with your new password.');
                return $this->redirect(['site/login']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to change password: ' . print_r($model->errors, true));
            }
        } catch (\Exception $e) {
            Yii::error("Exception in first login: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'An error occurred while changing your password.');
        }
    }

    return $this->render('first-login', [
        'model' => $model,
        'user' => $user,
    ]);
}
}

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

 


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'super-admin'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['super-admin'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isSuperAdmin();
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
             return $this->goHome();
         }

         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             if (Yii::$app->user->identity->isSuperAdmin()) {
                 Yii::$app->session->setFlash('success', 'Welcome, Super Admin!');
             }
             if (Yii::$app->user->can('viewDeveloperDashboard')) {
                 return $this->redirect(['developer/view']);
             }
             return $this->goBack();
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
    if (Yii::$app->request->referrer && strpos(Yii::$app->request->referrer, 'ticket/assign') !== false) {
        Yii::$app->session->setFlash('error', 'An error occurred while assigning the ticket. It may have been cancelled.');
    }

    // Check if the user is logged in
    if (Yii::$app->user->isGuest) {
        // Redirect to login page if the user is not logged in
        return $this->redirect(['site/login']);
    }

    // Check if the user has an identity
    if (Yii::$app->user->identity === null) {
        // Handle the case where the user doesn't have an identity
        Yii::$app->session->setFlash('error', 'User identity not found. Please log in again.');
        return $this->redirect(['site/login']);
    }

    // Ensure only admin users can access this action
    if (!Yii::$app->user->identity->isAdmin) {
        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    $ticketCounts = [
        'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
        'approved' => Ticket::find()->where(['status' => 'approved'])->count(),
        'cancelled' => Ticket::find()->where(['status' => 'cancelled'])->count(),
        'assigned' => Ticket::find()->where(['not', ['assigned_to' => null]])->count(),
        'notAssigned' => Ticket::find()->where(['assigned_to' => null])->count(),
        'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
        'escalated' => Ticket::find()->where(['status' => 'escalated'])->count(),
        'reopen' => Ticket::find()->where(['status' => 'reopen'])->count(),
        'deleted' => Ticket::find()->where(['status' => 'deleted'])->count(),
        'total' => Ticket::find()->count(),
    ];

    // Changed to SORT_ASC for ascending order
    $query = Ticket::find()
        ->with('user')
        ->orderBy(['id' => SORT_ASC]);  // Changed to ASC

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'defaultOrder' => [
                'id' => SORT_ASC,  // Changed to ASC
            ],
        ],
    ]);

    // Set timezone to Nairobi/Kenya
    date_default_timezone_set('Africa/Nairobi');

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

}








































































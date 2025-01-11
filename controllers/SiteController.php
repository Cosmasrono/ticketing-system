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
use app\models\ChangePasswordForm;
use app\models\SetPasswordForm;
use yii\db\Query;
use app\models\Company;
use app\models\ModuleList;
use app\models\Module;
use yii\helpers\Html;
use app\models\ChangeInitialPasswordForm;
use app\models\ForgotPasswordForm;
use app\models\UserProfile;
use app\models\ContractRenewal;
use DateTime;
use app\models\Renewal;

class SiteController extends Controller

{
     
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['admin', 'approve', 'cancel', 'assign', 'dashboard'], // Include actions for ticket management and dashboard
                'rules' => [
                    [
                        'actions' => ['index', 'login', 'signup', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role == User::ROLE_SUPER_ADMIN; // Check if the user is a super admin
                        },
                    ],
                    [
                        'actions' => ['admin', 'approve', 'cancel', 'assign', 'dashboard'], // Include admin, ticket actions, and dashboard
                        'allow' => true,
                    ],
                    [
                        'allow' => false, // Deny all other actions
                    ],
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
                    'get-issues' => ['post'],
                    'upload-to-cloudinary' => ['POST'],
                ],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
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

    public function actionCreateCompany()
    {
        $model = new Company();
        $model->role = 'user';

        if ($model->load(Yii::$app->request->post())) {
            try {
                $post = Yii::$app->request->post('Company');
                
                // Start transaction
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    // Make company name unique by combining company name and user name
                    $uniqueCompanyName = $post['company_name'] . '-' . $post['name'];

                    // Remove any special characters and spaces, replace with dashes
                    $uniqueCompanyName = preg_replace('/[^A-Za-z0-9\-]/', '-', $uniqueCompanyName);
                    // Convert to lowercase
                    $uniqueCompanyName = strtolower($uniqueCompanyName);
                    // Remove multiple consecutive dashes
                    $uniqueCompanyName = preg_replace('/-+/', '-', $uniqueCompanyName);
                    // Trim dashes from beginning and end
                    $uniqueCompanyName = trim($uniqueCompanyName, '-');

                    // Insert into company table
                    $sql = "INSERT INTO company (
                        name, 
                        company_email, 
                        start_date, 
                        end_date, 
                        role, 
                        status, 
                        company_name,
                        created_at, 
                        updated_at,
                        modules
                    ) VALUES (
                        :name,
                        :email,
                        :start_date,
                        :end_date,
                        :role,
                        :status,
                        :company_name,
                        NOW(),
                        NOW(),
                        :modules
                    )";

                    // Convert modules array to comma-separated string
                    $modules = isset($post['modules']) ? implode(',', $post['modules']) : '';

                    $result = Yii::$app->db->createCommand($sql)
                        ->bindValues([
                            ':name' => $post['name'],
                            ':email' => $post['company_email'],
                            ':start_date' => $post['start_date'],
                            ':end_date' => $post['end_date'],
                            ':role' => 'user',
                            ':status' => 1,
                            ':company_name' => $uniqueCompanyName,
                            ':modules' => $modules
                        ])
                        ->execute();

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Company user created successfully.');
                    return $this->redirect(['admin']);

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }

            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error creating user: ' . $e->getMessage());
            }
        }

        // Get modules for selection
        $modules = (new \yii\db\Query())
            ->select(['module_code', 'module_name'])
            ->from('module_list')
            ->all();

        return $this->render('create-company', [
            'model' => $model,
            'modules' => $modules,
        ]);
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


    // public function actionUploadScreenshot($ticketId)
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;

    //     if (Yii::$app->request->isPost) {
    //         $base64String = Yii::$app->request->post('image');

    //         // Remove the prefix from the base64 string
    //         $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
    //         $base64String = str_replace(' ', '+', $base64String); // Replace spaces with pluses

    //         // Decode the base64 string
    //         $imageData = base64_decode($base64String);

    //         // Find the ticket model by ID
    //         $ticket = Ticket::findOne($ticketId);
    //         if ($ticket) {
    //             $ticket->screenshot_base64 = $base64String; // Store the base64 string directly
    //             if ($ticket->save()) {
    //                 return ['status' => 'success', 'message' => 'Screenshot uploaded successfully!'];
    //             } else {
    //                 return ['status' => 'error', 'message' => 'Failed to save the screenshot.'];
    //             }
    //         } else {
    //             return ['status' => 'error', 'message' => 'Ticket not found.'];
    //         }
    //     }

    //     return ['status' => 'error', 'message' => 'Invalid request.'];
    // }


    public function actionIndex()
    {
        // If user is not logged in, redirect to login
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        return $this->render('index');
    }



// public function actionSignup($token = null)
// {
//     if (!$token) {
//         throw new NotFoundHttpException('Invalid invitation token.');
//     }

//     $invitation = Invitation::findOne(['token' => $token]);
//     if (!$invitation) {
//         throw new NotFoundHttpException('Invalid invitation token.');
//     }

//     $model = new SignupForm();
//     $model->company_email = $invitation->company_email;
//     $model->role = $invitation->role;

//     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//         $transaction = Yii::$app->db->beginTransaction();
//         try {
//             $user = new User();
//             $user->name = $model->name;
//             $user->company_email = $model->company_email;
//             $user->company_name = $model->company_name;
//             $user->setPassword($model->password);
//             $user->generateAuthKey();
//             $user->role = $model->role;

//             if ($user->save()) {
                
//                 $transaction->commit();
//                 Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
//                 return $this->goHome();
//             } else {
//                 $transaction->rollBack();
//                 Yii::$app->session->setFlash('error', 'There was an error saving your account: ' . json_encode($user->errors));
//             }
//         } catch (\Exception $e) {
//             $transaction->rollBack();
//             Yii::$app->session->setFlash('error', 'An error occurred during registration: ' . $e->getMessage());
//         }
//     }

//     return $this->render('signup', [
//         'model' => $model,
//     ]);
// }

    

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



    public function actionVerifyEmail($token, $email)
{
    // Allow access to this action without authentication
    $user = User::findOne(['company_email' => $email]);
    
    if (!$user) {
        Yii::$app->session->setFlash('error', 'User not found.');
        return $this->redirect(['site/login']);
    }

    if (!$user->password_reset_token || $user->password_reset_token !== $token) {
        Yii::$app->session->setFlash('error', 'Invalid verification token.');
        return $this->redirect(['site/login']);
    }

    // Check if token is expired (24 hours)
    $timestamp = (int) substr($token, strrpos($token, '_') + 1);
    if ($timestamp + 86400 < time()) {
        Yii::$app->session->setFlash('error', 'Verification link has expired.');
        return $this->redirect(['site/login']);
    }

    $model = new ChangeInitialPasswordForm();
    $model->email = $email;
    $model->token = $token;

    if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
        // Update user status and verification
        $user->status = User::STATUS_ACTIVE;
        $user->password_reset_token = null;
        if ($user->save(false)) {
            // Log in the user automatically
            Yii::$app->user->login($user);
            
            Yii::$app->session->setFlash('success', 'Password set successfully. Welcome to ' . Yii::$app->name);
            return $this->goHome(); // Use goHome() instead of redirect
        }
    }

    return $this->render('change-initial-password', [
        'model' => $model,
    ]);
}

public function actionChangeInitialPassword($token, $email)
{
    $user = User::findOne([
        'password_reset_token' => $token,
        'company_email' => urldecode($email)  // URL decode the email
    ]);

    if (!$user) {
        Yii::error("Invalid reset attempt - Token: $token, Email: $email");
        Yii::$app->session->setFlash('error', 'Invalid password reset token or email.');
        return $this->redirect(['site/login']);
    }

    // Check if token is expired (24 hours)
    $timestamp = (int) substr(strrchr($token, '_'), 1);
    if ($timestamp + 86400 < time()) {
        Yii::$app->session->setFlash('error', 'The password reset token has expired.');
        return $this->redirect(['site/login']);
    }

    $model = new ChangePasswordForm();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $user->setPassword($model->password);
        $user->password_reset_token = null; // Clear the reset token
        if ($user->save(false)) {
            Yii::$app->session->setFlash('success', 'New password has been saved.');
            return $this->redirect(['site/login']);
        }
    }

    return $this->render('change-initial-password', [
        'model' => $model,
    ]);
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
             return $this->redirect(['site/index']);
         }

         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             // Check if this is first login
             if (Yii::$app->user->identity->first_login == 1) {
                 return $this->redirect(['site/first-login']);
             }
             return $this->goBack();
         }

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
    $counts = Ticket::find()
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


// public function actionAdmin()
// {
//     if (Yii::$app->user->identity->role == 1) {
//         return $this->render('admin');
//     }
//     return $this->redirect('/');
// }


public function actionCreateUser()
{
    $model = new User();
    $companies = Company::find()->all(); // Fetch all companies

    if ($model->load(Yii::$app->request->post())) {
        // Fetch the selected company_name from the form
        $selectedCompanyName = $model->company_name;

        // Find the corresponding company record
        $company = Company::findOne(['company_name' => $selectedCompanyName]);

        if ($company) {
            // Set the company_email in the User model
            $model->company_email = $company->company_email;
        }

        // Save the User model
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'User created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('create-user', [
        'model' => $model,
        'companies' => $companies,
    ]);
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
    try {
        // Get pending renewals from tickets table
        $pendingRenewals = Ticket::find()
            ->where(['renewal_status' => 'pending'])
            ->with(['company', 'user']) // Assuming you have these relations
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        Yii::debug("Found " . count($pendingRenewals) . " pending renewals");
    } catch (\Exception $e) {
        Yii::error("Error fetching pending renewals: " . $e->getMessage());
        $pendingRenewals = [];
    }

    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => Ticket::find(),
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'defaultOrder' => ['id' => SORT_DESC]
        ]
    ]);
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

    // Your existing ticket query code
    $query = Ticket::find();
    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => 10,
        ],
    ]);

    return $this->render('admin', [
        'pendingRenewals' => $pendingRenewals,
        'dataProvider' => $dataProvider,
    ]);
}

 

public function actionRequestPasswordReset()
{
    $model = new PasswordResetRequestForm();

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Find the user by company_email
        $user = User::findOne(['company_email' => $model->email]);

        if ($user) {
            // Generate a password reset token
            $user->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
            if ($user->save()) {
                // Send email with the verification link
                $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
                    'site/change-initial-password',
                    'token' => $user->password_reset_token,
                    'email' => $user->company_email
                ]);

                // Send the email
                Yii::$app->mailer->compose()
                    ->setFrom('your-email@example.com')
                    ->setTo($user->company_email)
                    ->setSubject('Password Reset Request')
                    ->setTextBody("Click the link to reset your password: $verificationLink")
                    ->send();

                Yii::$app->session->setFlash('success', 'Check your email for the password reset link.');
                return $this->redirect(['site/login']);
            } else {
                Yii::error("Failed to save user: " . print_r($user->errors, true));
                Yii::$app->session->setFlash('error', 'Failed to generate password reset token.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'No user found with that email address.');
        }
    }

    return $this->render('requestPasswordReset', [
        'model' => $model,
    ]);
}

public function actionResetPassword($token)
{
    try {
        $model = new ResetPasswordForm($token);
    } catch (InvalidArgumentException $e) {
        throw new BadRequestHttpException($e->getMessage());
    }

    if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
        Yii::$app->session->setFlash('success', 'New password saved.');
        return $this->goHome();
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

// public function actionDashboard()
// {
//     if (Yii::$app->user->identity->role !== 'admin') {
//         Yii::$app->session->setFlash('error', 'Access denied.');
//         return $this->redirect(['site/index']);
//     }

//     // Get developer statistics
//     $developerStats = User::find()
//         ->select([
//             'user.id',
//             'user.name',
//             'COUNT(CASE WHEN ticket.status != "closed" THEN 1 END) as active_tickets',
//             'COUNT(CASE WHEN ticket.status = "closed" THEN 1 END) as completed_tickets',
//             'COUNT(ticket.id) as total_tickets'
//         ])
//         ->leftJoin('ticket', 'ticket.assigned_to = user.id')
//         ->where(['user.role' => 'developer'])
//         ->groupBy(['user.id', 'user.name'])
//         ->asArray()
//         ->all();

//     // Get ticket statistics
//     $ticketStats = [
//         'total' => Ticket::find()->count(),
//         'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
//         'assigned' => Ticket::find()->where(['status' => 'assigned'])->count(),
//         'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
//     ];

//     // Prepare data for the doughnut chart
//     $ticketStatusData = [
//         'Pending' => $ticketStats['pending'],
//         'Assigned' => $ticketStats['assigned'],
//         'Closed' => $ticketStats['closed'],
//     ];

//     // Get recent tickets and activity
//     $recentTickets = Ticket::find()
//         ->orderBy(['created_at' => SORT_DESC])
//         ->limit(5)
//         ->all();

//     // Get recent activity with developer info and escalation comments
//     $recentActivity = (new Query())
//         ->select([
//             'ticket.id AS ticket_id',
//             'ticket.status',
//             'ticket.created_at AS timestamp',
//             'assigned_user.name AS developer',
//             'escalated_user.name AS escalated_to',
//             'ticket.escalation_comment'
//         ])
//         ->from('ticket')
//         ->leftJoin('user assigned_user', 'ticket.assigned_to = assigned_user.id')
//         ->leftJoin('user escalated_user', 'ticket.escalated_to = escalated_user.id')
//         ->orderBy(['ticket.created_at' => SORT_DESC])
//         ->limit(10)
//         ->all();

//     // Get all users for status management
//     $users = User::find()->all();

//     // Get contract renewals with related data
//     $renewals = ContractRenewal::find()
//         ->with(['company', 'requestedBy'])
//         ->orderBy(['created_at' => SORT_DESC])
//         ->all();

//     // Get renewal statistics
//     $renewalStats = [
//         'total' => ContractRenewal::find()->count(),
//         'pending' => ContractRenewal::find()->where(['renewal_status' => 'pending'])->count(),
//         'approved' => ContractRenewal::find()->where(['renewal_status' => 'approved'])->count(),
//         'rejected' => ContractRenewal::find()->where(['renewal_status' => 'rejected'])->count(),
//     ];

//     return $this->render('dashboard', [
//         'developerStats' => $developerStats,
//         'ticketStats' => $ticketStats,
//         'recentTickets' => $recentTickets,
//         'recentActivity' => $recentActivity,
//         'ticketStatusData' => $ticketStatusData,
//         'users' => $users,
//         'renewals' => $renewals,
//         'renewalStats' => $renewalStats,
//         'isSuperAdmin' => true
//     ]);
// }

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



// public function actionCreateUser()
// {
//     $model = new User();
    
//     // Fetch specific fields from the Company table
//     $companies = Company::find()
//         ->select(['id', 'name', 'company_email', 'company_name', 'modules', 'start_date', 'end_date', 'role'])
//         ->all();
    
//     // Create a list of companies for the dropdown
//     $companyList = \yii\helpers\ArrayHelper::map($companies, 'company_name', 'company_name');

//     if (Yii::$app->request->isPost) {
//         // Debug entire POST data
//         Yii::debug("FULL POST DATA: " . print_r($_POST, true));
        
//         $postData = Yii::$app->request->post('User');
//         Yii::debug("User POST data: " . print_r($postData, true));
        
//         // Debug selected company name
//         $selectedCompanyName = $postData['company_name'] ?? null;
//         Yii::debug("Selected Company Name from POST: " . $selectedCompanyName);
        
//         // Fetch company data from the company table
//         $companyData = Company::findOne(['company_name' => $selectedCompanyName]);
//         if ($companyData) {
//             Yii::debug("Fetched Company Data: " . print_r($companyData, true));
//             $model->company_email = $companyData->company_email; // Set the company email from the company table
//             $model->role = $companyData->role; // Set the user role from the company table
//             $model->modules = $companyData->modules; // Set modules from the company data
//         } else {
//             Yii::$app->session->setFlash('error', 'Selected company does not exist.');
//             return $this->render('create-user', [
//                 'model' => $model,
//                 'companyList' => $companyList,
//             ]);
//         }

//         // Load model data after setting company data
//         $model->load(Yii::$app->request->post());
//         Yii::debug("Model after load: " . print_r($model->attributes, true));
        
//         // Prepare data to be pushed to the database
//         $dataToBePushed = [
//             'name' => $model->name,
//             'company_name' => $model->company_name,
//             'company_email' => $model->company_email,
//             'role' => $model->role,
//             'modules' => $model->modules,
//         ];
        
//         // Log the data that will be saved to the database
//         Yii::debug("Data to be saved: " . print_r($dataToBePushed, true));
        
//         // Validate and debug any errors
//         if (!$model->validate()) {
//             Yii::debug("Validation errors: " . print_r($model->errors, true));
//             Yii::$app->session->setFlash('error', 'Validation failed: ' . json_encode($model->errors));
//             return $this->render('create-user', [
//                 'model' => $model,
//                 'companyList' => $companyList,
//             ]);
//         }

//         // Generate temporary password
//         $tempPassword = Yii::$app->security->generateRandomString(8);
        
//         // Set user attributes
//         $model->setPassword($tempPassword);
//         $model->generateAuthKey();
//         $model->status = User::STATUS_ACTIVE;
        
//         // Begin transaction
//         $transaction = Yii::$app->db->beginTransaction();
//         try {
//             if ($model->save(false)) {  // false to skip validation since we already validated
//                 // Create associated profile
//                 $profile = new UserProfile();
//                 $profile->user_id = $model->id;
//                 $profile->entry_date = date('Y-m-d');
                
//                 if (!$profile->save()) {
//                     throw new \Exception("Failed to save profile: " . json_encode($profile->errors));
//                 }
                
//                 // Generate password reset token after successful save
//                 $model->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
//                 $model->save(false);
                
//                 // Send email with credentials
//                 $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
//                     'site/change-initial-password',
//                     'token' => $model->password_reset_token,
//                     'email' => urlencode($model->company_email)  // URL encode the email
//                 ]);

//                 $emailSent = Yii::$app->mailer->compose()
//                     ->setTo($model->company_email)
//                     ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
//                     ->setSubject('Account Creation - ' . Yii::$app->name)
//                     ->setTextBody("
//                         Your account has been created.\n
//                         Login Name: {$model->name}\n
//                         Temporary Password: {$tempPassword}\n
//                         Please click the following link to set your password:\n
//                         {$verificationLink}
//                     ")
//                     ->send();

//                 if (!$emailSent) {
//                     Yii::warning("Email could not be sent to {$model->company_email}");
//                 }

//                 $transaction->commit();
//                 Yii::$app->session->setFlash('success', 'User account created successfully. Login credentials have been sent to ' . $model->company_email);
//                 return $this->redirect(['index']);
//             }
//         } catch (\Exception $e) {
//             $transaction->rollBack();
//             Yii::error('User creation failed: ' . $e->getMessage());
//             Yii::$app->session->setFlash('error', 'Failed to create user account: ' . $e->getMessage());
//         }
//     }

//     return $this->render('create-user', [
//         'model' => $model,
//         'companyList' => $companyList,
//     ]);
// }

/**
 * Ajax action to get company details
 */
public function actionGetCompanyDetails()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isAjax) {
        return [
            'success' => false,
            'message' => 'Invalid request method'
        ];
    }

    $companyName = Yii::$app->request->post('companyName');
    
    // Debug log
    Yii::debug('Received company name: ' . $companyName);
    
    if (!$companyName) {
        return [
            'success' => false,
            'message' => 'Company name is required'
        ];
    }

    // Fetch company details with debug logging
    $company = (new \yii\db\Query())
        ->select(['company_name', 'company_email'])
        ->from('company')
        ->where(['company_name' => $companyName])
        ->one();

    // Debug log
    Yii::debug('Query result: ' . print_r($company, true));

    if ($company) {
        return [
            'success' => true,
            'data' => $company
        ];
    }

    return [
        'success' => false,
        'message' => 'Company not found'
    ];
}
 

public function actionGetCompanyInfo($id)
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $company = Company::findOne($id);
    if ($company) {
        return [
            'company_email' => $company->company_email,
            'modules' => explode(',', $company->modules),
        ];
    }
    
    return ['error' => 'Company not found'];
}


public function actionFirstLogin()
{
    // Prevent authenticated users from accessing this page
    if (!Yii::$app->user->isGuest) {
        return $this->goHome();
    }

    $email = Yii::$app->request->get('email');
    
    if (!$email) {
        throw new \yii\web\BadRequestHttpException('Email parameter is required.');
    }

    // Find user by email
    $user = User::findOne(['company_email' => $email]);
    
    if (!$user) {
        throw new \yii\web\NotFoundHttpException('User not found.');
    }

    $model = new ChangePasswordForm($email);
    
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Update password
        $user->password_hash = Yii::$app->security->generatePasswordHash($model->newPassword);
        $user->first_login = 0;
        $user->updated_at = time();
        
        if ($user->save()) {
            Yii::$app->session->setFlash('success', 'Password has been set successfully. Please login with your new password.');
            return $this->redirect(['site/login']);
        }
    }

    return $this->render('first-login', [
        'model' => $model,
        'email' => $email
    ]);
}

// public function actionChangePassword()
// {
//     if (Yii::$app->user->isGuest) {
//         return $this->redirect(['site/login']);
//     }

//     $user = Yii::$app->user->identity;
//     $model = new ChangePasswordForm();

//     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//         $user->password_hash = Yii::$app->security->generatePasswordHash($model->newPassword);
//         $user->first_login = 0; // Mark as password changed
//         
//         if ($user->save()) {
//             Yii::$app->session->setFlash('success', 'Password changed successfully.');
//             return $this->redirect(['site/index']);
//         }
//     }

//     return $this->render('change-password', [
//         'model' => $model
//     ]);
// }

public function actionSetPassword($company_email)
{
    $model = new SetPasswordForm();
    $model->company_email = $company_email;
    
    if ($model->load(Yii::$app->request->post())) {
        if ($model->validate()) {
            if ($model->changePassword()) {
                Yii::$app->session->setFlash('success', 'Password changed successfully. You can now login.');
                return $this->redirect(['site/login']);
            }
        }
    }

    return $this->render('set-password', [
        'model' => $model
    ]);
}

public function actionChangePassword()
{
    $email = Yii::$app->request->get('email');
    
    if (!$email) {
        throw new \yii\web\BadRequestHttpException('Email parameter is required.');
    }

    // Find user by email
    $user = User::findOne(['company_email' => $email]);
    
    if (!$user) {
        throw new \yii\web\NotFoundHttpException('User not found.');
    }

    // Pass email to the model constructor
    $model = new ChangePasswordForm($email);
    
    if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
        Yii::$app->session->setFlash('success', 'Password has been changed successfully. Please login with your new password.');
        return $this->redirect(['site/login']);
    }

    return $this->render('change-password', [
        'model' => $model,
        'email' => $email
    ]);
}

public function actionToggleUserStatus()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (Yii::$app->user->identity->role !== 'admin') {
        return [
            'success' => false,
            'message' => 'Access denied'
        ];
    }

    $id = Yii::$app->request->post('id');
    $user = User::findOne($id);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    try {
        // Toggle between 10 (active) and 0 (inactive)
        $user->status = ($user->status == 10) ? 0 : 10;
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'User status updated successfully',
                'newStatus' => $user->status
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update status'
        ];
    } catch (\Exception $e) {
        Yii::error('Error updating user status: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error occurred while updating status'
        ];
    }
}



public function actionDashboard()
{
    // Debugging: Log the user role
    Yii::info('User role: ' . Yii::$app->user->identity->role, __METHOD__);

    // Check admin, super_admin, and new role access
    if (Yii::$app->user->identity->role !== 'admin' && 
        Yii::$app->user->identity->role !== 'super_admin' && 
        Yii::$app->user->identity->role !== 1 && 
        Yii::$app->user->identity->role !== 4) {
        Yii::$app->session->setFlash('error', 'Access denied.');
        return $this->redirect(['site/admin']);
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

    // Get recent tickets
    $recentTickets = Ticket::find()
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    // Get recent activity with developer info
    $recentActivity = (new Query())
        ->select([
            'ticket.id AS ticket_id',
            'ticket.status',
            'ticket.created_at AS timestamp',
            'assigned_user.name AS developer',
            'escalated_user.name AS escalated_to',
            'ticket.escalation_comment'
        ])
        ->from('ticket')
        ->leftJoin('user assigned_user', 'ticket.assigned_to = assigned_user.id')
        ->leftJoin('user escalated_user', 'ticket.escalated_to = escalated_user.id')
        ->orderBy(['ticket.created_at' => SORT_DESC])
        ->limit(10)
        ->all();

    // Get all users for status management
    $users = User::find()->all();

    // Get contract renewals with related data
    $renewals = ContractRenewal::find()
        ->with(['company', 'requestedBy'])
        ->orderBy(['created_at' => SORT_DESC])
        ->all();

    // Get renewal statistics
    $renewalStats = [
        'total' => ContractRenewal::find()->count(),
        'pending' => ContractRenewal::find()->where(['renewal_status' => 'pending'])->count(),
        'approved' => ContractRenewal::find()->where(['renewal_status' => 'approved'])->count(),
        'rejected' => ContractRenewal::find()->where(['renewal_status' => 'rejected'])->count(),
    ];

    return $this->render('dashboard', [
        'developerStats' => $developerStats,
        'ticketStats' => $ticketStats,
        'recentTickets' => $recentTickets,
        'recentActivity' => $recentActivity,
        'ticketStatusData' => $ticketStatusData,
        'users' => $users,
        'renewals' => $renewals,
        'renewalStats' => $renewalStats,
        'isSuperAdmin' => true
    ]);
}

public function actionToggleStatus()
{
    // Clear any existing output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    Yii::$app->response->format = Response::FORMAT_JSON;
    Yii::$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
    
    try {
        $id = Yii::$app->request->post('id');
        
        if (!$id) {
            return $this->asJson([
                'success' => false,
                'message' => 'User ID is required'
            ]);
        }

        $user = User::findOne($id);
        if (!$user) {
            return $this->asJson([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        $user->status = ($user->status == 10) ? 0 : 10;
        
        if ($user->save(false)) {
            $statusText = $user->status == 10 ? 'activated' : 'deactivated';
            return $this->asJson([
                'success' => true,
                'message' => "User successfully {$statusText}",
                'newStatus' => $user->status,
                'userId' => $user->id
            ]);
        }

        return $this->asJson([
            'success' => false,
            'message' => 'Failed to update user status'
        ]);

    } catch (\Exception $e) {
        Yii::error('Error toggling user status: ' . $e->getMessage());
        return $this->asJson([
            'success' => false,
            'message' => 'An error occurred while updating user status'
        ]);
        
    }
}

public function actionGetCompanyByEmail($email)
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $company = \app\models\Company::findOne(['company_email' => $email]);
    
    if (!$company) {
        return ['error' => true, 'message' => 'Company not found'];
    }

    return [
        'company_name' => $company->company_name,
        'modules' => explode(',', $company->modules), // Assuming modules are stored as comma-separated string
        'error' => false
    ];
}

public function actionSetInitialPassword($token, $email, $temp_password)
{
    // Find user by email
    $user = User::findOne(['company_email' => $email]);
    
    if (!$user || !$user->validatePasswordResetToken($token)) {
        Yii::$app->session->setFlash('error', 'Invalid or expired password reset link.');
        return $this->redirect(['site/login']);
    }

    // Verify temporary password
    if (!$user->validatePassword($temp_password)) {
        Yii::$app->session->setFlash('error', 'Invalid temporary password.');
        return $this->redirect(['site/login']);
    }

    $model = new SetPasswordForm();
    $model->company_email = $email;
    $model->temp_password = $temp_password;

    if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
        Yii::$app->session->setFlash('success', 'Password has been set successfully. You can now login.');
        return $this->redirect(['site/login']);
    }

    return $this->render('set-initial-password', [
        'model' => $model,
    ]);
}

public function actionCreateAdmin()
{
    $model = new Company();
    $model->role = 'admin';

    if ($model->load(Yii::$app->request->post())) {
        try {
            $post = Yii::$app->request->post('Company');
            
            // First check if name already exists
            $existingName = (new \yii\db\Query())
                ->from('company')
                ->where(['name' => $post['name']])
                ->exists();

            if ($existingName) {
                throw new \Exception('This name is already taken. Please choose a different name.');
            }

            // Generate unique company name with timestamp
            $uniqueCompanyName = 'iansoft-' . time();

            $sql = "INSERT INTO company (
                name, 
                company_email, 
                start_date, 
                end_date, 
                role, 
                status, 
                company_name,
                created_at, 
                updated_at
            ) VALUES (
                :name,
                :email,
                :start_date,
                :end_date,
                :role,
                :status,
                :company_name,
                NOW(),
                NOW()
            )";

            $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':name' => $post['name'],
                    ':email' => $post['company_email'],
                    ':start_date' => $post['start_date'],
                    ':end_date' => $post['end_date'],
                    ':role' => 'admin',
                    ':status' => 1,
                    ':company_name' => $uniqueCompanyName
                ])
                ->execute();

            if ($result) {
                Yii::$app->session->setFlash('success', 'Administrator created successfully.');
                return $this->redirect(['admin']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error creating administrator: ' . $e->getMessage());
        }
    }

    return $this->render('create-admin', [
        'model' => $model,
    ]);
}




public function actionCreateDeveloper()
{
    $model = new Company();
    $model->role = 'developer';

    if ($model->load(Yii::$app->request->post())) {
        try {
            $post = Yii::$app->request->post('Company');
            
            // First check if name already exists
            $existingName = (new \yii\db\Query())
                ->from('company')
                ->where(['name' => $post['name']])
                ->exists();

            if ($existingName) {
                throw new \Exception('This name is already taken. Please choose a different name.');
            }

            // Use the name directly as the contact person's name
            $contactPersonName = strtolower(trim($post['name'])); // Contact person's name

            // Check if company_name is provided; if not, set default
            $companyNameForDB = !empty($post['company_name']) ? strtolower(trim($post['company_name'])) : 'iansoft';

            // Concatenate the company name and contact person's name
            $finalCompanyName = $companyNameForDB . '-' . $contactPersonName;

            // Prepare SQL for insertion
            $sql = "INSERT INTO company (
                name, 
                company_email, 
                start_date, 
                end_date, 
                role, 
                status, 
                company_name,
                created_at, 
                updated_at
            ) VALUES (
                :name,
                :email,
                :start_date,
                :end_date,
                :role,
                :status,
                :company_name,
                NOW(),
                NOW()
            )";

            $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':name' => $contactPersonName, // Use the contact person's name
                    ':email' => $post['company_email'],
                    ':start_date' => $post['start_date'],
                    ':end_date' => $post['end_date'],
                    ':role' => 'developer',
                    ':status' => 1,
                    ':company_name' => $finalCompanyName // Use the concatenated company name
                ])
                ->execute();

            if ($result) {
                Yii::$app->session->setFlash('success', 'Developer created successfully.');
                return $this->redirect(['admin']);
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error creating developer: ' . $e->getMessage());
        }
    }

    return $this->render('create-developer', [
        'model' => $model,
    ]);
}

public function actionProfile($id)
{
    $user = User::findOne($id);
    if (!$user) {
        throw new NotFoundHttpException('The requested user does not exist.');
    }

    $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

    if ($profile->load(Yii::$app->request->post()) && $profile->save()) {
        Yii::$app->session->setFlash('success', 'Profile updated successfully.');
        return $this->refresh();
    }

    return $this->render('profile', [
        'user' => $user,
        'profile' => $profile,
    ]);
}

public function actionRenewContract($id)
{
    $company = Company::findOne($id);
    if (!$company) {
        throw new NotFoundHttpException('Company not found.');
    }

    if (Yii::$app->request->isPost) {
        $renewal = new ContractRenewal();
        $renewal->company_id = $company->id;
        $renewal->requested_by = Yii::$app->user->id;
        $renewal->extension_period = Yii::$app->request->post('extension_period');
        $renewal->notes = Yii::$app->request->post('notes');
        $renewal->current_end_date = $company->end_date;
        $renewal->renewal_status = 'pending';

        // Calculate new end date
        $currentEndDate = strtotime($company->end_date);
        $newEndDate = strtotime("+{$renewal->extension_period} months", $currentEndDate);
        $renewal->new_end_date = date('Y-m-d', $newEndDate);

        if ($renewal->save()) {
            Yii::$app->session->setFlash('success', 'Contract renewal request submitted successfully.');
            return $this->redirect(['profile', 'id' => Yii::$app->user->id]);
        }
    }

    return $this->render('renew-contract', [
        'company' => $company,
    ]);
}

public function actionApproveRenewal($id)
{
    $renewal = ContractRenewal::findOne($id);
    if (!$renewal) {
        Yii::$app->session->setFlash('error', 'Renewal request not found.');
        return $this->redirect(['admin']);
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
        // Update renewal status
        $renewal->renewal_status = 'approved';
        
        if (!$renewal->save()) {
            throw new \Exception('Failed to update renewal status: ' . json_encode($renewal->errors));
        }

        // Update company end date using direct DB update to bypass validation
        $affected = Yii::$app->db->createCommand()
            ->update('company', 
                ['end_date' => $renewal->new_end_date], 
                ['id' => $renewal->company_id])
            ->execute();

        if (!$affected) {
            throw new \Exception('Failed to update company end date');
        }

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Renewal request approved successfully.');
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error('Renewal approval error: ' . $e->getMessage());
        Yii::$app->session->setFlash('error', 'Failed to approve renewal: ' . $e->getMessage());
    }

    return $this->redirect(['admin']);
}

public function actionRejectRenewal($id)
{
    $renewal = ContractRenewal::findOne($id);
    if (!$renewal) {
        Yii::$app->session->setFlash('error', 'Renewal request not found.');
        return $this->redirect(['admin']);
    }

    $renewal->renewal_status = 'rejected';
    
    if ($renewal->save()) {
        Yii::$app->session->setFlash('success', 'Renewal request rejected successfully.');
    } else {
        Yii::$app->session->setFlash('error', 'Failed to reject renewal request.');
    }

    return $this->redirect(['admin']);
}

// Helper method to check if model has attribute
private function hasAttribute($model, $attribute)
{
    return $model->hasAttribute($attribute);
}

// Add helper method for sending approval email
private function sendRenewalApprovalEmail($company, $renewal)
{
    try {
        Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($company->email)
            ->setSubject('Contract Renewal Approved')
            ->setTextBody("
                Dear {$company->company_name},

                Your contract renewal request has been approved.
                Extension Period: {$renewal->extension_period} months
                New End Date: {$company->end_date}

                Thank you for your continued partnership.

                Best regards,
                " . Yii::$app->name)
            ->send();
    } catch (\Exception $e) {
        Yii::error('Failed to send renewal approval email: ' . $e->getMessage());
    }
}

}

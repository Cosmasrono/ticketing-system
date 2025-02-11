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
use app\models\TicketActivity;
use app\models\Contract;
// use app\models\Users;
 

class SiteController extends Controller

{
     
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'super-admin-signup'],
                'rules' => [
                    [
                        'actions' => ['signup', 'super-admin-signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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



public function actionCreateUser()
{
    $companies = Company::find()->all();
    $role = 3; // Assuming role 3 is what you want to set
    return $this->render('create-user', [
        'companies' => $companies,
        'role' => $role, // Pass the role to the view
    ]);
}


public function actionCreateCompany()
{
    $model = new Company();
    $model->role = 'user';
    
    // Get the currently logged-in user
    $user = Yii::$app->user->identity;

    if ($model->load(Yii::$app->request->post())) {
        try {
            $post = Yii::$app->request->post('Company');
            
            // Check if company name already exists
            $existingCompany = Company::findOne(['company_name' => $post['company_name']]);
            if ($existingCompany) {
                Yii::$app->session->setFlash('error', 'This company name is already taken. Please choose a different name.');
                return $this->render('create-company', [
                    'model' => $model,
                    'user' => $user, // Pass user variable
                ]);
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Convert modules array to comma-separated string
                $modules = isset($post['modules']) ? implode(',', $post['modules']) : '';

                // Insert into company table
                $result = Yii::$app->db->createCommand()->insert('company', [
                    'name' => $post['name'],
                    'company_name' => $post['company_name'],
                    'company_email' => $post['company_email'],
                    'start_date' => $post['start_date'],
                    'end_date' => $post['end_date'],
                    'role' => 'user',
                    'status' => 1,
                    'modules' => $modules,
                    'created_at' => new \yii\db\Expression('NOW()'),
                    'updated_at' => new \yii\db\Expression('NOW()')
                ])->execute();

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Company created successfully.');
                return $this->redirect(['admin']);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Database Error: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Database Error: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Yii::error('Error creating company: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error creating company: ' . $e->getMessage());
        }
    }

    // Get all companies with their modules and associated client names
    $clientCompanies = (new \yii\db\Query())
        ->select([
            'c.company_name', 
            'c.company_email', 
            'c.module',
            'cl.name'
        ])
        ->from(['c' => 'client'])
        ->leftJoin(['cl' => 'client'], 'c.company_name = cl.company_name')
        ->all();

    return $this->render('create-company', [
        'model' => $model,
        'clientCompanies' => $clientCompanies,
        'user' => $user, // Pass user variable
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
        // If user is guest, show landing page
        if (Yii::$app->user->isGuest) {
            return $this->render('index-guest');
        }

        // Otherwise show the regular dashboard
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



public function actionSignup()
{
    $model = new SignupForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create the company
            $company = new Company();
            $company->name = $model->company_name;
            $company->company_email = $model->company_email;
            $company->created_at = time();
            $company->updated_at = time();
            
            if ($company->save()) {
                Yii::info("Company created with ID: " . $company->id);
                
                // Create the user with proper password hashing
                $user = new User();
                $user->name = $model->company_name; // Using company name as user name
                $user->company_name = $model->company_name;
                $user->company_email = $model->company_email;
                
                // Properly hash the password
                $user->setPassword($model->password); // Use the setPassword method from User model
                
                $user->auth_key = Yii::$app->security->generateRandomString();
                $user->verification_token = Yii::$app->security->generateRandomString();
                $user->email_verified = false;
                $user->role = User::ROLE_SUPERADMIN;
                $user->status = User::STATUS_INACTIVE; // Set to inactive until email verification
                $user->company_id = (int)$company->id;
                $user->created_at = time();
                $user->updated_at = time();
                
                Yii::info("Attempting to save user with company_id: " . $user->company_id);
                
                if ($user->save()) {
                    // Send verification email
                    $this->sendVerificationEmail($user);
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Registration successful. Please check your email to verify your account.');
                    return $this->redirect(['site/login']);
                } else {
                    Yii::error('User validation errors: ' . print_r($user->errors, true));
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Error creating user account: ' . json_encode($user->errors));
                }
            } else {
                Yii::error('Company validation errors: ' . print_r($company->errors, true));
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error creating company: ' . json_encode($company->errors));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Exception during signup: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'An error occurred during registration: ' . $e->getMessage());
        }
    }
    
    return $this->render('signup', [
        'model' => $model
    ]);
}

private function sendVerificationEmail($user)
{
    try {
        Yii::$app->mailer->compose()
            ->setTo($user->company_email)
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setSubject('Account verification for ' . Yii::$app->name)
            ->setTextBody("Click this link to verify your email: " . 
                Yii::$app->urlManager->createAbsoluteUrl([
                    'site/verify-email',
                    'token' => $user->verification_token
                ]))
            ->send();
            
        return true;
    } catch (\Exception $e) {
        Yii::error('Failed to send verification email: ' . $e->getMessage());
        return false;
    }
}

// public function sendConfirmationEmail($user)
// {
//     try {
//         Yii::$app->mailer->compose('confirmEmail', ['user' => $user])
//             ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
//             ->setTo($user->email)
//             ->setSubject('Account Registration Confirmation')
//             ->send();
//     } catch (\Exception $e) {
//         Yii::error('Failed to send confirmation email: ' . $e->getMessage());
//     }
// }

// public function actionConfirmEmail($token)
// {
//     try {
//         $user = User::findByConfirmationToken($token);
//         if ($user === null) {
//             throw new \Exception('Invalid confirmation token.');
//         }

//         $user->status = User::STATUS_ACTIVE;
//         $user->removeConfirmationToken();
        
//         if ($user->save()) {
//             Yii::$app->session->setFlash('success', 'Your email has been confirmed. You can now login.');
//         } else {
//             Yii::$app->session->setFlash('error', 'Error confirming email.');
//         }
//     } catch (\Exception $e) {
//         Yii::$app->session->setFlash('error', $e->getMessage());
//     }

//     return $this->goHome();
// }

// protected function sendVerificationEmail($user, $verificationLink)
// {
//     try {
//         Yii::info("Attempting to send verification email to: " . $user->company_email);
//         Yii::info("Verification link: " . $verificationLink);

//         $result = Yii::$app->mailer->compose('emailVerify-html', ['user' => $user, 'verificationLink' => $verificationLink])
//             ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
//             ->setTo($user->company_email)
//             ->setSubject('Verify your company email')
//             ->send();

//         if ($result) {
//             Yii::info("Verification email sent successfully to: " . $user->company_email);
//             return ['success' => true, 'message' => 'Verification email sent successfully.'];
//         } else {
//             Yii::error("Failed to send email to: " . $user->company_email);
//             return ['success' => false, 'message' => 'Failed to send verification email. Please try again later.'];
//         }
//     } catch (\Exception $e) {
//         Yii::error("Exception when sending email to " . $user->company_email . ": " . $e->getMessage());
//         return ['success' => false, 'message' => 'An error occurred while sending the verification email: ' . $e->getMessage()];
//     }
// }



//     public function actionVerifyEmail($token, $email)
// {
//     // Allow access to this action without authentication
//     $user = User::findOne(['company_email' => $email]);
    
//     if (!$user) {
//         Yii::$app->session->setFlash('error', 'User not found.');
//         return $this->redirect(['site/login']);
//     }

//     if (!$user->password_reset_token || $user->password_reset_token !== $token) {
//         Yii::$app->session->setFlash('error', 'Invalid verification token.');
//         return $this->redirect(['site/login']);
//     }

//     // Check if token is expired (24 hours)
//     $timestamp = (int) substr($token, strrpos($token, '_') + 1);
//     if ($timestamp + 86400 < time()) {
//         Yii::$app->session->setFlash('error', 'Verification link has expired.');
//         return $this->redirect(['site/login']);
//     }

//     $model = new ChangeInitialPasswordForm();
//     $model->email = $email;
//     $model->token = $token;

//     if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
//         // Update user status and verification
//         $user->status = User::STATUS_ACTIVE;
//         $user->password_reset_token = null;
//         if ($user->save(false)) {
//             // Log in the user automatically
//             Yii::$app->user->login($user);
            
//             Yii::$app->session->setFlash('success', 'Password set successfully. Welcome to ' . Yii::$app->name);
//             return $this->goHome(); // Use goHome() instead of redirect
//         }
//     }

//     return $this->render('change-initial-password', [
//         'model' => $model,
//     ]);
// }

// public function actionChangeInitialPassword($token, $email)
// {
//     $user = User::findOne([
//         'password_reset_token' => $token,
//         'company_email' => urldecode($email)  // URL decode the email
//     ]);

//     if (!$user) {
//         Yii::error("Invalid reset attempt - Token: $token, Email: $email");
//         Yii::$app->session->setFlash('error', 'Invalid password reset token or email.');
//         return $this->redirect(['site/login']);
//     }

//     // Check if token is expired (24 hours)
//     $timestamp = (int) substr(strrchr($token, '_'), 1);
//     if ($timestamp + 86400 < time()) {
//         Yii::$app->session->setFlash('error', 'The password reset token has expired.');
//         return $this->redirect(['site/login']);
//     }

//     $model = new ChangePasswordForm();
//     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//         $user->setPassword($model->password);
//         $user->password_reset_token = null; // Clear the reset token
//         if ($user->save(false)) {
//             Yii::$app->session->setFlash('success', 'New password has been saved.');
//             return $this->redirect(['site/login']);
//         }
//     }

//     return $this->render('change-initial-password', [
//         'model' => $model,
//     ]);
// }
    
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
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Find the user
            $user = User::findOne(['company_email' => $model->company_email]);
            
            if ($user) {
                // Check if the user is active
                if ($user->status != User::STATUS_ACTIVE) {
                    Yii::$app->session->setFlash('error', 'Your account is not active.');
                    return $this->render('login', ['model' => $model]);
                }

                // Proceed with login
                if ($model->login()) {
                    return $this->goBack();
                }
            }
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


// public function actionCreateUser()
// {
//     $model = new User();
//     $companies = Company::find()->all(); // Fetch all companies

//     if ($model->load(Yii::$app->request->post())) {
//         // Fetch the selected company_name from the form
//         $selectedCompanyName = $model->company_name;

//         // Find the corresponding company record
//         $company = Company::findOne(['company_name' => $selectedCompanyName]);

//         if ($company) {
//             // Set the company_email in the User model
//             $model->company_email = $company->company_email;
//         }

//         // Save the User model
//         if ($model->save()) {
//             Yii::$app->session->setFlash('success', 'User created successfully.');
//             return $this->redirect(['view', 'id' => $model->id]);
//         }
//     }

//     return $this->render('create-user', [
//         'model' => $model,
//         'companies' => $companies,
//     ]);
// }




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
    // Check if the user is a guest or does not have the admin or superadmin role
    if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->identity->role, ['admin', 'superadmin', 1, 4])) {
        throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page.');
    }

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

 

// public function actionRequestPasswordReset()
// {
//     $model = new PasswordResetRequestForm();

//     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//         $user = User::findOne([
//             'company_email' => $model->company_email, // Changed from email to company_email
//             'status' => 1
//         ]);

//         if ($user) {
//             // Proceed with password reset logic
//             if ($model->sendEmail()) {
//                 Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
//                 return $this->goHome();
//             }
//         } else {
//             Yii::$app->session->setFlash('error', 'No user found with this email address.');
//         }
//     }

//     return $this->render('requestPasswordReset', [
//         'model' => $model,
//     ]);
// }
public function actionRequestPasswordReset()
{
    $model = new PasswordResetRequestForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
    }

    return $this->render('requestPasswordResetToken', [
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
    $model = new PasswordResetRequestForm(); // Assuming you have a model for this

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
            return $this->goHome();
        } else {
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }
    }

    return $this->render('forgot_password', [
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
//         
//         // Log the data that will be saved to the database
//         Yii::debug("Data to be saved: " . print_r($dataToBePushed, true));
//         
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


 

// public function actionCreateUserForCompany($company_id)
// {
//     // Verify user permissions first
//     if (Yii::$app->user->isGuest) {
//         Yii::$app->session->setFlash('error', 'Please login to create user accounts.');
//         return $this->redirect(['site/login']);
//     }

//     $company = Company::findOne($company_id);
    
//     if (!$company) {
//         Yii::error("Company not found with ID: $company_id");
//         Yii::$app->session->setFlash('error', 'Company not found.');
//         return $this->redirect(['index']);
//     }

//     // Check if there's already an active user for this company
//     $existingUser = User::find()
//         ->where(['company_name' => $company->company_name])
//         ->andWhere(['status' => User::STATUS_ACTIVE])
//         ->one();

//     if ($existingUser) {
//         Yii::$app->session->setFlash('error', 'An active user already exists for this company.');
//         return $this->redirect(['index']);
//     }

//     // Create new User model instance
//     $user = new User();
    
//     try {
//         $transaction = Yii::$app->db->beginTransaction();

//         // Generate tokens and password
//         $clearPassword = Yii::$app->security->generateRandomString(8);
//         $token = Yii::$app->security->generateRandomString(32);

//         // Get company name parts for the user's name
//         $nameParts = explode('-', $company->company_name);
//         $userName = ucfirst(end($nameParts));

//         // Set user attributes
//         $user->name = $userName;
//         $user->company_name = $company->company_name;
//         $user->company_email = $company->company_email;
//         $user->setPassword($clearPassword);
//         $user->generateAuthKey();
//         $user->password_reset_token = $token;
//         $user->token_created_at = time();
//         $user->status = User::STATUS_UNVERIFIED;
//         $user->role = User::ROLE_USER; // Default to user role
//         $user->created_at = time();
//         $user->updated_at = time();
//         $user->modules = is_array($company->modules) ? implode(', ', $company->modules) : $company->modules;

//         // Set verification token
//         $user->verification_token = $user->generateVerificationToken();

//         // Validate and save the user
//         if (!$user->validate()) {
//             throw new \Exception('User validation failed: ' . json_encode($user->getErrors()));
//         }

//         if (!$user->save(false)) {
//             throw new \Exception('Failed to save user');
//         }

//         // Create reset URL
//         $resetUrl = Yii::$app->urlManager->createAbsoluteUrl([
//             'site/set-initial-password',
//             'token' => $token
//         ]);

//         // Send email
//         $emailSent = Yii::$app->mailer->compose('@app/views/site/_email_credentials', [
//             'company' => $company,
//             'user' => $user,
//             'password' => $clearPassword,
//             'token' => $token,
//             'resetUrl' => $resetUrl
//         ])
//         ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
//         ->setTo($company->company_email)
//         ->setSubject('Welcome to ' . Yii::$app->name . ' - Set Your Password')
//         ->send();

//         if (!$emailSent) {
//             throw new \Exception('Failed to send email');
//         }

//         $transaction->commit();
//         Yii::$app->session->setFlash('success', 
//             'User account created successfully. Instructions sent to ' . $company->company_email
//         );

//     } catch (\Exception $e) {
//         if (isset($transaction)) {
//             $transaction->rollBack();
//         }
//         Yii::error("Error in user creation: " . $e->getMessage());
//         Yii::error("Stack trace: " . $e->getTraceAsString());
//         Yii::$app->session->setFlash('error', 'Error creating user: ' . $e->getMessage());
//     }

//     return $this->redirect(['index']);
// }

// public function actionCreateUserForCompany()
// {
//     try {
//         // Log all request data
//         Yii::debug('REQUEST_METHOD: ' . Yii::$app->request->method);
//         Yii::debug('POST data: ' . print_r(Yii::$app->request->post(), true));
        
//         // Get and validate company_id
//         $company_id = Yii::$app->request->post('company_id');
        
//         if (empty($company_id) || $company_id === '0') {
//             throw new \Exception('Invalid company ID. Please try again.');
//         }

//         // Validate company exists and is active
//         $company = Company::find()
//             ->where(['id' => $company_id])
//             ->andWhere(['status' => 1])
//             ->one();
        
//         if (!$company) {
//             throw new \Exception("Company not found or inactive. ID: $company_id");
//         }

//         // Validate company email
//         $company_email = Yii::$app->request->post('company_email');
//         if (empty($company_email) || $company_email !== $company->company_email) {
//             throw new \Exception('Invalid company email.');
//         }

//         // Check for existing user
//         $existingUser = User::find()
//             ->where(['company_name' => $company->company_name])
//             ->andWhere(['status' => User::STATUS_ACTIVE])
//             ->one();

//         if ($existingUser) {
//             throw new \Exception('An active user already exists for this company.');
//         }

//         $transaction = Yii::$app->db->beginTransaction();

//         try {
//             // Rest of your existing code...
            
//         } catch (\Exception $e) {
//             $transaction->rollBack();
//             throw $e;
//         }

//     } catch (\Exception $e) {
//         Yii::error("Error creating user: " . $e->getMessage());
//         Yii::error("Debug info:");
//         Yii::error("- POST data: " . print_r(Yii::$app->request->post(), true));
        
//         Yii::$app->session->setFlash('error', 'Error creating user: ' . $e->getMessage());
//         return $this->redirect(['create-user']);
//     }
// }



 
public function actionCreateUserForCompany($company_id)
{
    $company = Company::findOne($company_id);
    
    if (!$company) {
        Yii::error("Company not found with ID: $company_id");
        Yii::$app->session->setFlash('error', 'Company not found.');
        return $this->redirect(['index']);
    }

    // Check if there's already an user with this email
    $existingUser = User::find()
        ->where(['company_email' => $company->company_email])
        ->andWhere(['status' => [User::STATUS_ACTIVE, User::STATUS_UNVERIFIED]])
        ->one();

    if ($existingUser) {
        $status = $existingUser->status === User::STATUS_ACTIVE ? 'active' : 'pending verification';
        Yii::$app->session->setFlash('error', 
            "A user with this email ({$company->company_email}) already exists and is {$status}."
        );
        return $this->redirect(['index']);
    }

            try {
                $connection = Yii::$app->db;
                $transaction = $connection->beginTransaction();

        // Generate a simple token without timestamp
                $clearPassword = Yii::$app->security->generateRandomString(8);
                $token = Yii::$app->security->generateRandomString(32);

        // Get company name parts for the user's name
        $nameParts = explode('-', $company->company_name);
        $userName = ucfirst(end($nameParts));

        // Map role properly
        $role = $company->role;
        if ($role === 'superadmin') {
            $roleValue = 4;
        } elseif ($role === 'admin') {
            $roleValue = 1;
        } elseif ($role === 'developer') {
            $roleValue = 3;
        } else {
            $roleValue = 2; // default role for user
        }

        // Prepare user data
        $userData = [
            'company_id' => $company->id,
            'name' => $userName,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'password_hash' => Yii::$app->security->generatePasswordHash($clearPassword),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_reset_token' => $token,
            'token_created_at' => time(),
            'status' => User::STATUS_UNVERIFIED,
            'role' => $roleValue,
            'created_at' => time(),
            'updated_at' => time(),
            'modules' => is_array($company->modules) ? implode(', ', $company->modules) : $company->modules,
        ];

        // Debug log
        Yii::debug("Creating user with data: " . json_encode($userData));

        // Insert using Query Builder
        $success = $connection->createCommand()->insert('users', $userData)->execute();

        if (!$success) {
            throw new \Exception('Failed to insert user data');
        }

        // Rest of your email sending code...
        $userId = $connection->getLastInsertID();
        
        // Verify the user was created
        $createdUser = User::findOne($userId);
        if (!$createdUser) {
            throw new \Exception('User was not found after creation');
        }

        // Create reset URL
        $resetUrl = Yii::$app->urlManager->createAbsoluteUrl([
            '/site/set-initial-password',
            'token' => $token
        ]);

        $emailSent = Yii::$app->mailer->compose('@app/views/site/_email_credentials', [
            'company' => $company,
            'password' => $clearPassword,
            'token' => $token,
            'resetUrl' => $resetUrl
        ])
        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
        ->setTo($company->company_email)
        ->setSubject('Welcome to ' . Yii::$app->name . ' - Set Your Password')
        ->send();

        if ($emailSent) {
            $transaction->commit();
            Yii::$app->session->setFlash('success', 
                'User account created and instructions sent to ' . $company->company_email 
            );
        } else {
            throw new \Exception('Failed to send email');
        }

    } catch (\Exception $e) {
        if (isset($transaction)) {
            $transaction->rollBack();
        }
        Yii::error("Error in user creation: " . $e->getMessage());
        Yii::error("Stack trace: " . $e->getTraceAsString());
        Yii::$app->session->setFlash('error', 'Error creating user: ' . $e->getMessage());
    }

    return $this->redirect(['index']);
}

public function actionSetInitialPassword($token = null)
{
    if (empty($token)) {
        Yii::error("No token provided in request");
        Yii::$app->session->setFlash('error', 'Password reset token is required.');
        return $this->redirect(['site/login']);
    }

    try {
        Yii::debug("Token received: " . $token);
        Yii::debug("Current time: " . time());

        // Debug token parts
        $parts = explode('_', $token);
        if (count($parts) === 2) {
            $timestamp = (int) $parts[1];
            $expire = Yii::$app->params['user.passwordResetTokenExpire'];
            Yii::debug("Token timestamp: " . $timestamp);
            Yii::debug("Expiration period: " . $expire);
            Yii::debug("Will expire at: " . ($timestamp + $expire));
        }

        $model = new ResetPasswordForm($token);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->resetPassword()) {
                    Yii::$app->session->setFlash('success', 'Your new password has been saved.');
                    return $this->redirect(['site/login']);
                }
            } else {
                Yii::error("Validation errors: " . json_encode($model->getErrors()));
            }
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);

    } catch (\Exception $e) {
        Yii::error("Password reset failed: " . $e->getMessage());
        Yii::$app->session->setFlash('error', 'Unable to reset password. Please request a new reset link.');
        return $this->redirect(['site/login']);
    }
}


private function isTokenValid($user)
{
    if (!$user->created_at) {
        return false;
    }
    
    // Token expires after 24 hours
    return (time() - $user->created_at) <= 86400;
}

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




// public function actionCreateUserForCompany($company_id)
// {
//     // Find the company
//     $company = Company::findOne($company_id);
//     if (!$company) {
//         throw new NotFoundHttpException('Company not found.');
//     }

//     $user = new User();
//     $user->role = 'user'; // Set the role for the user

//     if ($user->load(Yii::$app->request->post())) {
//         try {
//             // Start transaction
//             $transaction = Yii::$app->db->beginTransaction();

//             try {
//                 // Generate a random password
//                 $password = Yii::$app->security->generateRandomString(8);
//                 $user->setPassword($password); // Set the password
//                 $user->company_id = $company_id; // Associate user with the company
//                 $user->status = User::STATUS_ACTIVE; // Set user status

//                 // Set the email property
//                 $user->email = Yii::$app->request->post('User')['email']; // Ensure this is set correctly

//                 // Generate username based on company name
//                 $user->username = $this->generateUsername($company->company_name); // Ensure this method exists

//                 // Save the user
//                 if ($user->save()) {
//                     // Commit the transaction
//                     $transaction->commit();
//                     Yii::$app->session->setFlash('success', 'User created successfully.');
//                     return $this->redirect(['index']);
//                 } else {
//                     throw new \Exception('Failed to save user: ' . implode(', ', $user->getFirstErrors()));
//                 }

//             } catch (\Exception $e) {
//                 $transaction->rollBack();
//                 throw $e; // Rethrow the exception to handle it in the outer catch
//             }

//         } catch (\Exception $e) {
//             Yii::$app->session->setFlash('error', 'Error creating user: ' . $e->getMessage());
//         }
//     }

//     // Fetch all companies to pass to the view
//     $companies = Company::find()->all(); // Fetch all companies

//     return $this->render('create-user', [
//         'model' => $user,
//         'companies' => $companies, // Pass the companies variable to the view
//     ]);
// }
//     private function generateUsername($company_name)
//     {
//         // Create username from company name and random number
//         $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $company_name));
//         $random = rand(100, 999);
//         return $base . $random;
//     }

//     private function sendCredentialsEmail($email, $username, $password)
//     {
//         return Yii::$app->mailer->compose()
//             ->setTo($email)
//             ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
//             ->setSubject('Your Account Credentials')
//             ->setTextBody("
//                 Hello,

//                 Your account has been created. Here are your login credentials:
//                 Username: {$username}
//                 Password: {$password}

//                 Please change your password after first login.

//                 Regards,
//                 " . Yii::$app->name)
//             ->send();
//     }


public function actionDashboard()
{
    // Handle contract renewal status changes
    if (Yii::$app->request->isPost) {
        $id = Yii::$app->request->get('id');
        $action = Yii::$app->request->get('action');
        
        if ($id && $action) {
            $model = ContractRenewal::findOne($id);
            if ($model && $model->renewal_status === 'pending') {
                $model->renewal_status = ($action === 'approve') ? 'approved' : 'rejected';
                
                // If approved, update the company's end date
                if ($action === 'approve') {
                    // Find the company
                    $company = \app\models\Company::findOne($model->company_id);
                    if ($company) {
                        // Calculate new end date based on extension period
                        $currentEndDate = new \DateTime($model->current_end_date);
                        $currentEndDate->modify("+{$model->extension_period} months");
                        
                        // Update company end date using updateAttributes to skip validation
                        $success = $company->updateAttributes([
                            'end_date' => $currentEndDate->format('Y-m-d')
                        ]);
                        
                        if (!$success) {
                            Yii::$app->session->setFlash('error', 'Failed to update company end date.');
                            return $this->redirect(['dashboard']);
                        }
                    }
                }
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 
                        $action === 'approve' 
                            ? 'Contract renewal approved and company end date updated.' 
                            : 'Contract renewal rejected.'
                    );
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to update contract renewal status.');
                }
            }
        }
    }

    // Fetch counts for various contract statuses
    $activeContractsCount = Contract::find()->where(['status' => 'active'])->count();
    $expiringCount = Contract::find()->where(['<', 'end_date', date('Y-m-d', strtotime('+30 days'))])->count();
    $renewedCount = ContractRenewal::find()->where(['MONTH(renewed_at)' => date('n')])->count(); // Count renewals this month
    $expiredCount = Contract::find()->where(['status' => 'expired'])->count();

    // Fetch all contracts
    $contracts = Contract::find()->where(['status' => 'active'])->with('client')->all(); // Assuming 'client' is a relation in your Contract model

    // Fetch all contract renewals
    $contractRenewals = ContractRenewal::find()->all(); // Fetch all contract renewals

    // Fetch total users count
    $totalUsers = User::find()->count(); // Assuming you have a User model

    // Fetch active users count
    $activeCount = User::find()->where(['status' => 'active'])->count(); // Assuming 'status' is a column in your users table

    // Fetch inactive users count
    $inactiveCount = User::find()->where(['status' => 'inactive'])->count(); // Assuming 'status' is a column in your users table

    // Fetch all users
    $allUsers = User::find()->all(); // Retrieve all users

    // Fetch tickets
    $tickets = Ticket::find()->all(); // Assuming you have a Ticket model

    // Fetch client count
    $clientCount = Client::find()->count(); // Assuming you have a Client model

    // Fetch clients
    $clients = Client::find()->all(); // Assuming you have a Client model

    // Calculate ticket status data
    $ticketStatusData = Ticket::find()
        ->select(['status', 'COUNT(*) as count'])
        ->groupBy('status')
        ->indexBy('status')
        ->column(); // This will give you an associative array of status counts

    // Calculate response time data
    $responseTimes = Ticket::find()->select('response_time')->column(); // Replace with actual field
    $responseTimeLabels = []; // Define your labels based on your data
    $responseTimeData = []; // Prepare your data for the chart

    // Example: Assuming you want to categorize response times into ranges
    foreach ($responseTimes as $time) {
        // Categorize response times and populate labels and data
        if ($time < 1) {
            $responseTimeLabels[] = 'Less than 1 hour';
            $responseTimeData[0] = ($responseTimeData[0] ?? 0) + 1;
        } elseif ($time < 2) {
            $responseTimeLabels[] = '1-2 hours';
            $responseTimeData[1] = ($responseTimeData[1] ?? 0) + 1;
        } else {
            $responseTimeLabels[] = 'More than 2 hours';
            $responseTimeData[2] = ($responseTimeData[2] ?? 0) + 1;
        }
    }

    // Render the dashboard view with the fetched data
    return $this->render('dashboard', [
        'activeContractsCount' => $activeContractsCount,
        'expiringCount' => $expiringCount,
        'renewedCount' => $renewedCount,
        'expiredCount' => $expiredCount,
        'contracts' => $contracts, // Pass the contracts to the view
        'contractRenewals' => $contractRenewals, // Pass the contract renewals to the view
        'totalUsers' => $totalUsers, // Pass the total users count to the view
        'activeCount' => $activeCount, // Pass the active users count to the view
        'inactiveCount' => $inactiveCount, // Pass the inactive users count to the view
        'allUsers' => $allUsers, // Pass all users to the view
        'tickets' => $tickets, // Pass tickets to the view
        'clientCount' => $clientCount, // Pass client count to the view
        'ticketStatusData' => $ticketStatusData, // Pass ticket status data to the view
        'responseTimeLabels' => $responseTimeLabels, // Pass response time labels to the view
        'responseTimeData' => $responseTimeData,
        'clients' => $clients, // Pass response time data to the view
    ]);
}

private function getRoleName($roleId)
{
    $roles = [
        1 => 'User',
        2 => 'Manager',
        3 => 'Admin',
        4 => 'Super Admin'
    ];
    return $roles[$roleId] ?? 'Unknown Role';
}

// Keep the toggle status action for user activation
public function actionToggleStatus()
{
    if (!Yii::$app->request->isAjax) {
        return;
    }
    
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $id = Yii::$app->request->post('id');
    $newStatus = (int)Yii::$app->request->post('status');
    
    $user = User::findOne($id);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    // Prevent status change for role 4 users
    if ($user->role == 4) {
        return [
            'success' => false,
            'message' => 'Cannot modify status of protected users'
        ];
    }

    $user->status = $newStatus;
    if ($user->save()) {
        return [
            'success' => true,
            'message' => $newStatus == 10 ? 'User activated' : 'User deactivated',
            'newStatus' => $user->status
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Failed to update status: ' . json_encode($user->errors)
    ];
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
            
            // Debug post data
            Yii::debug('POST data received: ' . print_r($post, true));
            
            // Validate required fields
            $requiredFields = ['name', 'company_name', 'company_email', 'start_date', 'end_date'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($post[$field])) {
                    $missingFields[] = ucfirst(str_replace('_', ' ', $field));
                }
            }
            
            if (!empty($missingFields)) {
                throw new \Exception('The following fields are required: ' . implode(', ', $missingFields));
            }

            // Process the names
            $developerName = strtolower(trim($post['name']));
            $companyName = strtolower(trim($post['company_name']));

            // First check if company_name already exists
            $existingName = (new \yii\db\Query())
                ->from('company')
                ->where(['company_name' => $companyName])
                ->exists();

            if ($existingName) {
                throw new \Exception('This company name is already taken. Please choose a different name.');
            }

            // Prepare SQL for insertion with nullable fields
            $sql = "INSERT INTO company (
                name,
                company_name, 
                company_email, 
                start_date, 
                end_date, 
                role,
                status,
                company_type,
                subscription_level,
                created_at, 
                updated_at
            ) VALUES (
                :name,
                :company_name,
                :email,
                :start_date,
                :end_date,
                :role,
                :status,
                :company_type,
                'basic',  -- Set a default value instead of NULL
                GETDATE(),
                GETDATE()
            )";

            $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':name' => $developerName,
                    ':company_name' => $companyName,
                    ':email' => $post['company_email'],
                    ':start_date' => $post['start_date'],
                    ':end_date' => $post['end_date'],
                    ':role' => 'developer',
                    ':status' => 1,
                    ':company_type' => 'default'
                ])
                ->execute();

            if ($result) {
                Yii::$app->session->setFlash('success', 'Developer created successfully.');
                return $this->redirect(['admin']);
            }
        } catch (\Exception $e) {
            Yii::error('Error creating developer: ' . $e->getMessage());
            Yii::error('Stack trace: ' . $e->getTraceAsString());
            Yii::error('POST data: ' . print_r($post ?? [], true));
            Yii::$app->session->setFlash('error', 'Error creating developer: ' . $e->getMessage());
        }
    }

    return $this->render('create-developer', [
        'model' => $model,
    ]);
}


// public function actionProfile($id)
// {
//     $role = Yii::$app->user->identity->role;
    
//     if ($role == 4) {
//         // For developers (role 4), fetch from users table
//         $user = User::findOne($id);
//         if ($user === null) {
//             throw new NotFoundHttpException('The requested user does not exist.');
//         }
        
//         // Temporarily set empty arrays until we confirm table structures
//         $contracts = [];
//         $tickets = [];
//         $renewals = [];

//         return $this->render('profile', [
//             'user' => $user,
//             'companyEmail' => $user->company_email,
//             'contracts' => $contracts,
//             'tickets' => $tickets,
//             'renewals' => $renewals
//         ]);
            
//     } else {
//         // For roles 1,2,3, use admin profile view
//         $user = User::findOne($id);
//         if ($user === null) {
//             throw new NotFoundHttpException('User not found.');
//         }
        
//         // Get associated data for admin
//         $users = User::find()
//             ->where(['company_name' => $user->company_name])
//             ->all();
            
//         // Temporarily set empty arrays until we confirm table structures
//         $contracts = [];
//         $tickets = [];
//         $renewals = [];

//         return $this->render('admin-profile', [
//             'user' => $user,
//             'users' => $users,
//             'companyEmail' => $user->company_email,
//             'contracts' => $contracts,
//             'tickets' => $tickets,
//             'renewals' => $renewals
//         ]);
//     }
// }




// public function actionRenewContract($id)
// {
//     $company = Company::findOne($id);
//     if (!$company) {
//         throw new NotFoundHttpException('Company not found.');
//     }

//     $renewal = new ContractRenewal();
//     if ($renewal->load(Yii::$app->request->post())) {
//         // Set the company ID and requested by user ID
//         $renewal->company_id = $company->id; // Set the company ID
//         $renewal->requested_by = Yii::$app->user->id; // Assuming you have user ID
//         $renewal->current_end_date = $company->end_date; // Set the current end date

//         // Log the attributes being saved
//         Yii::info('Saving ContractRenewal with data: ' . json_encode($renewal->attributes));

//         if ($renewal->save()) {
//             Yii::$app->session->setFlash('success', 'Contract renewal request submitted successfully.');
//             return $this->redirect(['profile', 'id' => Yii::$app->user->id]);
//         } else {
//             Yii::error('Failed to save renewal: ' . json_encode($renewal->errors));
//             Yii::$app->session->setFlash('error', 'Failed to submit contract renewal.');
//         }
//     }

//     return $this->render('renew-contract', [
//         'company' => $company,
//         'renewal' => $renewal,
//     ]);
// }


public function actionRenewContract($id = null)
{
    // Get company based on logged-in user
    $userId = Yii::$app->user->id;
    $company = Company::find()
        ->where(['id' => $id])
        // ->orWhere(['id' => $userId])
        ->one();

    if (!$company) {
        Yii::$app->session->setFlash('error', 'Company not found.');
        return $this->redirect(['user/profile', 'id' => $userId]);
    }

    // Log company data for debugging
    Yii::debug("Company Data Found: " . print_r([
        'id' => $company->id,
        'name' => $company->company_name,
        'email' => $company->company_email,
        'start_date' => $company->start_date,
        'end_date' => $company->end_date,
    ], true));

    $model = new ContractRenewal();
    $model->company_id = $company->id;
    $model->requested_by = $userId;
    $model->current_end_date = $company->end_date;
    $model->renewal_status = 'pending';

    if ($model->load(Yii::$app->request->post())) {
        try {
            if (!empty($company->end_date)) {
                $currentEndDate = new \DateTime($company->end_date);
                $newEndDate = clone $currentEndDate;
                $newEndDate->modify("+{$model->extension_period} months");
                $model->new_end_date = $newEndDate->format('Y-m-d');
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Contract renewal request has been submitted successfully and is pending approval.');
                    return $this->redirect(['user/profile', 'id' => $userId]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error saving renewal request: ' . implode(', ', $model->getFirstErrors()));
                }
            } else {
                Yii::$app->session->setFlash('error', 'Contract end date is not set. Please contact administrator.');
            }
        } catch (\Exception $e) {
            Yii::error("Error processing renewal dates: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error processing renewal dates.');
        }
    }

    return $this->render('renew-contract', [
        'model' => $model,
        'company' => $company,
    ]);
}

// Add this action for admin approval
public function actionApproveRenewal($id)
{
    if (!Yii::$app->user->can('admin')) {
        throw new ForbiddenHttpException('You are not authorized to perform this action.');
    }

    $renewal = ContractRenewal::findOne($id);
    if (!$renewal) {
        throw new NotFoundHttpException('Renewal request not found.');
    }

    $renewal->renewal_status = 'approved';
    $renewal->renewed_at = date('Y-m-d H:i:s');

    if ($renewal->save()) {
        // Update company end date
        $company = Company::findOne($renewal->company_id);
        if ($company) {
            $company->end_date = $renewal->new_end_date;
            if ($company->save()) {
                Yii::$app->session->setFlash('success', 'Renewal request approved and contract updated successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Renewal approved but failed to update company contract date.');
            }
        }
    } else {
        Yii::$app->session->setFlash('error', 'Failed to approve renewal request.');
    }

    return $this->redirect(['admin/renewals']); // Adjust this to your admin dashboard route
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

public function actionActivateUser($id)
{
    $user = User::findOne($id);
    
    if (!$user) {
        Yii::$app->session->setFlash('error', 'User not found.');
        return $this->redirect(['index']);
    }

    if ($user->status === User::STATUS_ACTIVE) {
        Yii::$app->session->setFlash('error', 'User is already active.');
        return $this->redirect(['index']);
    }

    try {
        if ($user->activate()) {
            Yii::$app->session->setFlash('success', 'Activation email sent to ' . $user->company_email);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to send activation email.');
        }
    } catch (\Exception $e) {
        Yii::error('Failed to activate user: ' . $e->getMessage());
        Yii::$app->session->setFlash('error', 'Failed to activate user: ' . $e->getMessage());
    }

    return $this->redirect(['index']);
}

 

 

public function actionCreateSuperUser()
{
    $user = new User();
    
    // Set superuser attributes
    $user->setAttributes([
        'name' => 'Super Admin',
        'company_name' => 'System Admin',
        'company_email' => 'ccosmas001@gmail.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('admin123'), // You can change this password
        'created_at' => time(),
        'updated_at' => time(),
        'auth_key' => Yii::$app->security->generateRandomString(),
        'role' => 'admin',  // Set as admin role
        'is_verified' => 1,
        'status' => 10,     // Active status
        'first_login' => 0, // Not first login
        'modules' => 'ALL', // Access to all modules
        'is_password_reset' => 0
    ]);
    
    // Debug output
    echo "<pre>";
    echo "Attempting to create superuser:\n";
    print_r($user->attributes);
    
    if ($user->save()) {
        echo "\nSuperuser created successfully with ID: " . $user->id;
        echo "\nEmail: ccosmas001@gmail.com";
        echo "\nPassword: admin123";
    } else {
        echo "\nErrors creating superuser:\n";
        print_r($user->getErrors());
    }
    
    die();
}

public function actionResetFirstPassword()
{
    if (Yii::$app->user->isGuest || Yii::$app->user->identity->first_login != 1) {
        return $this->goHome();
    }

    $model = new ResetPasswordForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $user = Yii::$app->user->identity;
        $user->setPassword($model->new_password);
        $user->first_login = 0;
        
        if ($user->save(false)) {
            Yii::$app->session->setFlash('success', 'Password has been changed successfully.');
            return $this->redirect(['site/index']);
        }
    }

    return $this->render('reset-first-password', [
        'model' => $model
    ]);
}

public function actionAddClient()
{
    $model = new Client();

    if ($model->load(Yii::$app->request->post())) {
        try {
            $post = Yii::$app->request->post('Client');
            
            // Get the selected modules and convert to string
            $modules = isset($post['modules']) ? implode(', ', $post['modules']) : '';

            // Convert GETDATE() to Unix timestamp using DATEDIFF
            $timestampExpression = new \yii\db\Expression('DATEDIFF(SECOND, \'1970-01-01\', GETDATE())');

            // Direct insert into client table - now including the name field
            $result = Yii::$app->db->createCommand()->insert('client', [
                'name' => $post['name'] ?? $post['company_name'], // Use name if provided, otherwise use company_name
                'company_name' => $post['company_name'],
                'company_email' => $post['company_email'],
                'module' => $modules,
                'created_at' => $timestampExpression,
                'updated_at' => $timestampExpression
            ])->execute();

            if ($result) {
                Yii::$app->session->setFlash('success', 'Client added successfully to client table.');
                return $this->redirect(['admin']);
            }

        } catch (\Exception $e) {
            Yii::error("Error adding client: " . $e->getMessage());
            Yii::error("Posted data: " . json_encode($post));
            Yii::$app->session->setFlash('error', 'Error adding client: ' . $e->getMessage());
        }
    }

    return $this->render('add-client', [
        'model' => $model,
    ]);
}

// public function actionAddClientDirect()
// {
//     $model = new Client(); // Create a new instance of the Client model

//     // Set the attributes directly
//     $model->company_id = 1; // Set the company ID (replace with the actual ID)
//     $model->company_name = 'New Company Name'; // Set the company name
//     $model->company_email = 'newcompany@example.com'; // Set the company email
//     $model->created_at = date('Y-m-d H:i:s'); // Set the created_at timestamp
//     $model->updated_at = date('Y-m-d H:i:s'); // Set the updated_at timestamp

//     // Save the model to the database
//     if ($model->save()) {
//         // Redirect or return a success message
//         return $this->redirect(['dashboard']); // Redirect to the dashboard after saving
//     } else {
//         // Handle the error
//         Yii::$app->session->setFlash('error', 'Failed to add client: ' . implode(', ', $model->getFirstErrors()));
//         return $this->redirect(['dashboard']); // Redirect to the dashboard or another page
//     }
// }

public function actionActiveContracts()
{
    // Fetch active contracts from the contract_renewal table
    $activeContracts = ContractRenewal::find()
        ->where(['status' => 'active']) // Assuming 'status' is the column that indicates active contracts
        ->all();

    return $this->render('active-contracts', [
        'activeContracts' => $activeContracts,
    ]);
}

public function beforeAction($action)
{
    if (!parent::beforeAction($action)) {
        return false;
    }

    // Only check if user is logged in
    if (!Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;
        
        // If user status is inactive (0), force logout
        if ($user->status == 0) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact administrator.');
            Yii::$app->response->redirect(['/site/login'])->send();
            return false;
        }
    }

    return true;
}

protected function checkContractStatus()
{
    $user = Yii::$app->user->identity;
 
    // Check if user status is inactive
    if ($user->status == 0) {
        Yii::$app->user->logout();
        Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact administrator.');
        return Yii::$app->response->redirect(['/site/login']);
    }

    // Get company end date
    $company = Company::findOne(['company_name' => $user->company_name]);
 
    if (!$company || !$company->end_date) {
        Yii::info("No company or end date found for user {$user->id}");
        return;
    }

    try {
        $today = new DateTime('now');
        $endDate = new DateTime($company->end_date);
        
        // Log the comparison values
        Yii::info("Contract check - Today: " . $today->format('Y-m-d') . ", End Date: " . $endDate->format('Y-m-d'));
        
        // Only deactivate if end date is in the past (today is after end date)
        if ($today->format('Y-m-d') > $endDate->format('Y-m-d')) {
            Yii::info("Contract expired for user {$user->id} - Company: {$company->company_name}");
            // Deactivate user
            Yii::$app->db->createCommand()
                ->update('users', ['status' => 0], ['id' => $user->id])
                ->execute();
 
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'Your account has been deactivated due to contract expiration.');
            return Yii::$app->response->redirect(['/site/login']);
        }
    } catch (\Exception $e) {
        Yii::error("Date comparison error: " . $e->getMessage());
    }
}
 
public function actionExtendContract()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isAjax) {
        return ['success' => false, 'message' => 'Invalid request method'];
    }

    $userId = Yii::$app->request->post('userId');
    $companyName = Yii::$app->request->post('companyName');
    $extensionPeriod = (int)Yii::$app->request->post('extensionPeriod');

    // Start transaction
    $transaction = Yii::$app->db->beginTransaction();

    try {
        // Find the company
        $company = \app\models\Company::findOne(['company_name' => $companyName]);
        if (!$company) {
            throw new \Exception('Company not found');
        }

        // Calculate new end date
        $currentEndDate = new \DateTime($company->end_date);
        $currentEndDate->modify("+{$extensionPeriod} months");
        $newEndDate = $currentEndDate->format('Y-m-d');

        // Update company end date
        $company->end_date = $newEndDate;
        if (!$company->save()) {
            throw new \Exception('Failed to update company contract');
        }

        // Create contract renewal record
        $renewal = new \app\models\ContractRenewal([
            'company_id' => $company->id,
            'requested_by' => Yii::$app->user->id,
            'extension_period' => $extensionPeriod,
            'current_end_date' => $company->end_date,
            'new_end_date' => $newEndDate,
            'renewal_status' => 'approved',
            'created_at' => new \yii\db\Expression('NOW()'),
        ]);

        if (!$renewal->save()) {
            throw new \Exception('Failed to create renewal record');
        }

        $transaction->commit();
        
        return [
            'success' => true,
            'message' => "Contract extended successfully until " . Yii::$app->formatter->asDate($newEndDate)
        ];

    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Contract extension error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

public function actionGetCompanyExpiry()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $companyName = Yii::$app->request->get('companyName');
    $company = \app\models\Company::findOne(['company_name' => $companyName]);
    
    if ($company) {
        return [
            'success' => true,
            'expiryDate' => Yii::$app->formatter->asDate($company->end_date)
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Company not found'
    ];
}


// public function actionApprove($id)
// {
//     Yii::info("Approve action called with ID: $id"); // Log the ID

//     // Find the contract renewal by ID
//     $contractRenewal = ContractRenewal::findOne($id);
    
//     if ($contractRenewal === null) {
//         Yii::info("Contract renewal not found for ID: $id"); // Log if not found
//         throw new \yii\web\NotFoundHttpException("The requested contract renewal does not exist.");
//     }

//     // Check if the status is already approved
//     if ($contractRenewal->status === 'approved') {
//         Yii::$app->session->setFlash('error', 'This contract renewal has already been approved.');
//         return $this->redirect(['site/dashboard']);
//     }

//     // Find the associated contract
//     $contract = Contract::findOne($contractRenewal->contract_id); // Assuming contract_id is a field in ContractRenewal

//     if ($contract === null) {
//         throw new \yii\web\NotFoundHttpException("The associated contract does not exist.");
//     }

//     // Extend the end date of the contract
//     $newEndDate = date('Y-m-d', strtotime($contract->end_date . ' + ' . $contractRenewal->extension_period . ' days')); // Assuming extension_period is in days
//     $contract->end_date = $newEndDate; // Update the end date

//     // Save the updated contract
//     if ($contract->save()) {
//         // Update the status of the contract renewal
//         $contractRenewal->status = 'approved'; // Update status to approved
//         $contractRenewal->save(); // Save the renewal status

//         Yii::$app->session->setFlash('success', 'Contract renewal approved and end date extended successfully.');
//     } else {
//         Yii::$app->session->setFlash('error', 'Failed to approve contract renewal and extend end date.');
//     }

//     return $this->redirect(['site/dashboard']); // Redirect to the dashboard or another page
// }
 

// public function actionUpdateStatus($id)
// {
//     Yii::info("Update status action called with ID: $id"); // Log the ID

//     // Find the contract renewal by ID
//     $contractRenewal = ContractRenewal::findOne($id);
    
//     if ($contractRenewal === null) {
//         Yii::info("Contract renewal not found for ID: $id"); // Log if not found
//         return $this->asJson(['success' => false, 'message' => 'The requested contract renewal does not exist.']);
//     }

//     // Check if the status is already approved
//     if ($contractRenewal->status === 'approved') {
//         return $this->asJson(['success' => false, 'message' => 'This contract renewal has already been approved.']);
//     }

//     // Update the status of the contract renewal
//     $contractRenewal->status = 'approved'; // Update status to approved

//     if ($contractRenewal->save()) {
//         Yii::$app->session->setFlash('success', 'Contract renewal approved successfully.');
//         return $this->asJson(['success' => true, 'message' => 'Contract renewal approved successfully.']);
//     } else {
//         return $this->asJson(['success' => false, 'message' => 'Failed to approve contract renewal.']);
//     }
// }

public function actionUpdateRenewalStatus()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isAjax) {
        return ['success' => false, 'message' => 'Invalid request'];
    }

    $id = Yii::$app->request->post('id');
    $status = Yii::$app->request->post('status');

    if (!$id || !$status) {
        return ['success' => false, 'message' => 'Missing required parameters'];
    }

    $renewal = ContractRenewal::findOne($id);
    if (!$renewal) {
        return ['success' => false, 'message' => 'Renewal request not found'];
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
        // Update renewal status
        $renewal->renewal_status = $status;
        
        // If approved, update company end date
        if ($status === 'approved') {
            $company = Company::findOne($renewal->company_id);
            if ($company) {
                // Update company end date using updateAttributes to skip validation
                $result = $company->updateAttributes([
                    'end_date' => $renewal->new_end_date
                ]);
                
                if (!$result) {
                    throw new \Exception('Failed to update company end date');
                }
            }
        }

        // Update renewal status using updateAttributes
        $result = $renewal->updateAttributes([
            'renewal_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$result) {
            throw new \Exception('Failed to update renewal status');
        }

        $transaction->commit();

        // Send notification email
        try {
            $renewal->sendRenewalNotification($company);
        } catch (\Exception $e) {
            Yii::error('Failed to send notification email: ' . $e->getMessage());
            // Don't throw the exception as the main operation succeeded
        }
        
        return [
            'success' => true,
            'message' => $status === 'approved' ? 
                'Renewal approved and company contract extended' : 
                'Renewal request rejected'
        ];

    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error('Contract renewal error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

public function actionSuperAdminSignup() {
    $model = new SignupForm();

    if ($model->load(Yii::$app->request->post())) {
        try {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();

            try {
                // Check if email exists
                $existingUser = $connection->createCommand("
                    SELECT company_email FROM users 
                    WHERE company_email = :email
                ", [':email' => $model->company_email])->queryOne();

                if ($existingUser) {
                    throw new \Exception('This email is already registered.');
                }

                // Generate verification token and password hash
                $verificationToken = Yii::$app->security->generateRandomString(32);
                $passwordHash = Yii::$app->security->generatePasswordHash($model->password);
                $authKey = Yii::$app->security->generateRandomString();
                $timestamp = time();

                // Direct SQL insert for user
                $sql = "INSERT INTO users (
                    name,
                    company_name,
                    company_email,
                    password_hash,
                    auth_key,
                    verification_token,
                    email_verified,
                    role,
                    status,
                    company_id,
                    created_at,
                    updated_at
                ) VALUES (
                    :name,
                    :company_name,
                    :company_email,
                    :password_hash,
                    :auth_key,
                    :verification_token,
                    0,
                    :role,
                    :status,
                    :company_id,
                    :created_at,
                    :updated_at
                )";

                $result = $connection->createCommand($sql)
                    ->bindValues([
                        ':name' => $model->name,
                        ':company_name' => $model->company_name,
                        ':company_email' => $model->company_email,
                        ':password_hash' => $passwordHash,
                        ':auth_key' => $authKey,
                        ':verification_token' => $verificationToken,
                        ':role' => User::ROLE_SUPER_ADMIN, // Using the constant from User model
                        ':status' => User::STATUS_INACTIVE,
                        ':company_id' => time(), // Using timestamp as company_id
                        ':created_at' => $timestamp,
                        ':updated_at' => $timestamp
                    ])
                    ->execute();

                if ($result) {
                    // Get the new user's ID
                    $userId = $connection->getLastInsertID();

                    // Send verification email
                    $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
                        'site/verify-email',
                        'token' => $verificationToken
                    ]);

                    $emailSent = Yii::$app->mailer->compose()
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                        ->setTo($model->company_email)
                        ->setSubject('Email Verification - ' . Yii::$app->name)
                        ->setHtmlBody("
                            <p>Hello {$model->name},</p>
                            <p>Please click the following link to verify your email address:</p>
                            <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
                            <p>If you didn't create an account, you can ignore this email.</p>
                        ")
                        ->send();

                    if (!$emailSent) {
                        throw new \Exception('Failed to send verification email.');
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your email for verification instructions.');
                    return $this->refresh();
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Yii::error('Registration error: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    return $this->render('super-admin-signup', [
        'model' => $model,
    ]);
}

// protected function sendVerificationEmail($user) {
//     try {
//         // Double check the token exists
//         if (empty($user->verification_token)) {
//             $user->verification_token = Yii::$app->security->generateRandomString(32);
//             $user->save(false);
//         }

//         $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
//             'site/verify-email',
//             'token' => $user->verification_token
//         ]);

//         // Log the verification link for debugging
//         Yii::debug("Verification link generated: $verificationLink");

//         return Yii::$app->mailer->compose()
//             ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
//             ->setTo($user->company_email)
//             ->setSubject('Email Verification - ' . Yii::$app->name)
//             ->setHtmlBody("
//                 <p>Hello {$user->name},</p>
//                 <p>Please click the following link to verify your email address:</p>
//                 <p><a href='{$verificationLink}'>{$verificationLink}</a></p>
//                 <p>If you didn't create an account, you can ignore this email.</p>
//             ")
//             ->send();

//     } catch (\Exception $e) {
//         Yii::error('Error sending verification email: ' . $e->getMessage());
//         return false;
//     }
// }

public function actionVerifyEmail($token) {
    try {
        Yii::debug("Verification attempt with token: $token");
        
        if (empty($token)) {
            throw new \Exception('Empty verification token');
        }

        $user = User::findOne(['verification_token' => $token]);
        
        if (!$user) {
            Yii::error("No user found with token: $token");
            throw new \Exception('Invalid verification token');
        }

        $user->email_verified = true;
        $user->status = User::STATUS_ACTIVE;
        // Keep the verification_token for now (optional)
        // $user->verification_token = null;

        if ($user->save(false)) {
            Yii::$app->session->setFlash('success', 'Your email has been verified successfully. You can now log in.');
            return $this->redirect(['site/login']);
        } else {
            Yii::error('Failed to save user after verification: ' . json_encode($user->errors));
            throw new \Exception('Failed to verify email');
        }

    } catch (\Exception $e) {
        Yii::error('Email verification error: ' . $e->getMessage());
        Yii::$app->session->setFlash('error', 'Email verification failed: ' . $e->getMessage());
        return $this->redirect(['site/login']);
    }
}


public function actionRequestNewToken($email = null)
{
    if ($email === null) {
        $email = Yii::$app->request->get('email');
    }

    if (empty($email)) {
        Yii::$app->session->setFlash('error', 'Email address is required.');
        return $this->redirect(['site/login']);
    }

    $user = User::findOne(['company_email' => $email]);

    if (!$user) {
        Yii::$app->session->setFlash('error', 'No user found with this email address.');
        return $this->redirect(['site/login']);
    }

    try {
        // Generate new token
        if (!$user->generatePasswordResetToken()) {
            throw new \Exception('Failed to generate new token');
        }

        // Create reset URL
        $resetUrl = Yii::$app->urlManager->createAbsoluteUrl([
            'site/set-initial-password',
            'token' => $user->password_reset_token
        ]);

        // Send new email
        $emailSent = Yii::$app->mailer->compose('@app/views/site/_email_credentials', [
            'company' => Company::findOne($user->company_id),
            'user' => $user,
            'resetUrl' => $resetUrl
        ])
        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
        ->setTo($user->company_email)
        ->setSubject('New Password Reset Link - ' . Yii::$app->name)
        ->send();

        if (!$emailSent) {
            throw new \Exception('Failed to send email');
        }

        Yii::$app->session->setFlash('success', 'A new password reset link has been sent to your email.');

    } catch (\Exception $e) {
        Yii::error("Failed to generate new token: " . $e->getMessage());
        Yii::$app->session->setFlash('error', 'Failed to generate new reset link. Please try again.');
    }

    return $this->redirect(['site/login']);
}
public function actionUpdateStatus() {
    if (!Yii::$app->request->isAjax) {
        return;
    }
    
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $id = Yii::$app->request->post('id');
    $newStatus = (int)Yii::$app->request->post('status');
    
    $user = User::findOne($id);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    
    if ($user->role == 4) {
        return [
            'success' => false,
            'message' => 'Cannot modify protected users'
        ];
    }

    try {
        $user->status = $newStatus;
        if ($user->save()) {
            return [
                'success' => true,
                'message' => $newStatus == 10 ? 'User activated' : 'User deactivated',
                'newStatus' => $user->status
            ];
        }
    } catch (\Exception $e) {
        Yii::error("Error updating user status: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update status'
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Failed to update user status'
    ];
}
// In your controller (e.g., CompanyController.php or SiteController.php)
// public function actionRenewContract($id)
// {
//     // Find the company
//     $company = Company::findOne($id);
//     if ($company === null) {
//         throw new NotFoundHttpException('The requested company does not exist.');
//     }

//     // Handle the POST request for renewal
//     if (Yii::$app->request->isPost) {
//         // Start a transaction
//         $transaction = Yii::$app->db->beginTransaction();
        
//         try {
//             // Get current end date
//             $currentEndDate = new DateTime($company->end_date);
            
//             // Store old end date before modification
//             $oldEndDate = $company->end_date;
            
//             // Add 1 year to the current end date
//             $newEndDate = $currentEndDate->modify('+1 year');
            
//             // Update company end date
//             $company->end_date = $newEndDate->format('Y-m-d');
            
//             // Create renewal record
//             $renewal = new ContractRenewal();
//             $renewal->company_id = $company->id;
//             $renewal->old_end_date = $oldEndDate;
//             $renewal->new_end_date = $company->end_date;
//             $renewal->renewed_at = date('Y-m-d H:i:s');
//             $renewal->status = 'completed';
            
//             // Save both records
//             if ($company->save() && $renewal->save()) {
//                 $transaction->commit();
//                 Yii::$app->session->setFlash('success', 'Contract has been successfully renewed until ' . 
//                     Yii::$app->formatter->asDate($company->end_date));
                
//                 // Redirect to profile page
//                 return $this->redirect(['/user/profile', 'id' => Yii::$app->user->id]);
//             } else {
//                 $transaction->rollBack();
//                 Yii::$app->session->setFlash('error', 'Failed to renew contract. Please try again.');
//             }
//         } catch (\Exception $e) {
//             $transaction->rollBack();
//             Yii::error('Contract renewal failed: ' . $e->getMessage());
//             Yii::$app->session->setFlash('error', 'An error occurred while renewing the contract.');
//         }
//     }
    
//     // Render the renewal confirmation form
//     return $this->render('renew-contract', [
//         'company' => $company,
//     ]);
// }
}
    
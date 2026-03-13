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
use app\config\Constants;


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



    // First, let's modify the action to debug the data:
    public function actionCreateUser()
    {
        // Fetch companies with role
        $companies = Yii::$app->db->createCommand('
            SELECT 
                c.id,
                c.company_name,
                c.company_email,
                c.modules,
                c.role  -- Make sure this column exists in your company table
            FROM company c
            WHERE c.status = 1
            ORDER BY c.company_name ASC
        ')->queryAll();

        // Debug log the raw data
        foreach ($companies as $company) {
            Yii::debug([
                'company_name' => $company['company_name'],
                'role_raw' => $company['role'],
                'role_type' => gettype($company['role']),
                'role_empty' => empty($company['role'])
            ], 'role_debugging');
        }

        return $this->render('create-user', [
            'companies' => $companies,
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
                        'name' => $post['name'] ?? $post['company_name'],
                        'company_name' => $post['company_name'],
                        'company_email' => $post['company_email'],
                        'company_type' => $post['company_type'] ?? 'default',  // Add this line with appropriate default
                        'subscription_level' => $post['subscription_level'] ?? 'basic',  // Add this line with appropriate default
                        'start_date' => date('Y-m-d', strtotime($post['start_date'])),
                        'end_date' => date('Y-m-d', strtotime($post['end_date'])),
                        'role' => 'user',
                        'status' => 1,
                        'modules' => isset($post['modules']) ? implode(',', $post['modules']) : '',
                        'created_at' => new \yii\db\Expression('GETDATE()'),
                        'updated_at' => new \yii\db\Expression('GETDATE()')
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
                    // ✅ Initialize session activity time on successful login
                    Yii::$app->session->set('lastActivityTime', time());
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
        
        // Only calculate unread messages count for admin (1) or super-admin (4)
        $userRole = Yii::$app->user->identity->role;
        $isAdmin = ($userRole == 1 || $userRole == 4 || $userRole === 'admin' || $userRole === 'superadmin');
        
        $unreadMessagesCount = 0;
        if ($isAdmin) {
            // Count unread ticket messages
            $unreadMessagesCount = \app\models\TicketMessage::find()
                ->where(['admin_viewed' => 0])
                ->count();
        }

        return $this->render('admin', [
            'dataProvider' => $dataProvider,
            'ticketCounts' => $ticketCounts,
            'unreadMessagesCount' => $unreadMessagesCount,
        ]);
    }


 
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
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->resetPassword()) {
                Yii::$app->session->setFlash('success', 'New password saved.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Failed to reset password.');
            }
        }
        
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
        
    } catch (InvalidArgumentException $e) {
        Yii::$app->session->setFlash('error', $e->getMessage());
        return $this->goHome(); // Redirect instead of trying to render
    }
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
                        Yii::$app->session->setFlash(
                            'success',
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
        $activeCount = User::find()->where(['status' => User::STATUS_ACTIVE])->count(); // Use the constant instead of the string 'active'

        // Fetch inactive users count
        $inactiveCount = User::find()->where(['status' => User::STATUS_INACTIVE])->count(); // Use the constant instead of the string 'inactive'

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

                // Check if email already exists
                $existingEmail = (new \yii\db\Query())
                    ->from('company')
                    ->where(['company_email' => $post['company_email']])
                    ->exists();

                if ($existingEmail) {
                    throw new \Exception('This email address is already registered in the system. Please use a different email.');
                }

                $sql = "INSERT INTO company (
                name, 
                company_email, 
                start_date, 
                end_date, 
                role, 
                status, 
                company_name,
                company_type,
                subscription_level,
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
                'basic',
                'basic',
                GETDATE(),
                GETDATE()
            )";

                $result = Yii::$app->db->createCommand($sql)
                    ->bindValues([
                        ':name' => $post['name'],
                        ':email' => $post['company_email'],
                        ':start_date' => $post['start_date'],
                        ':end_date' => $post['end_date'],
                        ':role' => 'admin',
                        ':status' => 1,
                        ':company_name' => $post['name']  // Using name directly instead of timestamp
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
            
            // Use PHP's time() function instead of UNIX_TIMESTAMP()
            $timestamp = time();
            
            // Direct insert into client table - now including the name field
            $result = Yii::$app->db->createCommand()->insert('client', [
                'name' => $post['name'] ?? $post['company_name'], // Use name if provided, otherwise use company_name
                'company_name' => $post['company_name'],
                'company_email' => $post['company_email'],
                'module' => $modules,
                'created_at' => $timestamp,  // Changed from Expression to direct value
                'updated_at' => $timestamp   // Changed from Expression to direct value
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


     
    public function actionUpdateRenewalStatus()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax || !$request->isPost) {
            return $this->asJson(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = $request->post('id');
        $status = $request->post('status');

        if (!$id || !$status) {
            return $this->asJson(['success' => false, 'message' => 'Missing required parameters']);
        }

        // Find the renewal record
        $renewal = ContractRenewal::findOne($id);
        if (!$renewal) {
            return $this->asJson(['success' => false, 'message' => 'Renewal record not found']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Update renewal status
            $renewal->renewal_status = $status;
            $renewal->updated_at = new \yii\db\Expression('GETDATE()');
            $renewal->updated_by = Yii::$app->user->id;

            if (!$renewal->save()) {
                throw new \Exception('Failed to update renewal status: ' . json_encode($renewal->errors));
            }

            // If status is 'approved', update the company end_date
            if ($status === 'approved') {
                // Get the user who requested the renewal
                $user = User::findOne($renewal->requested_by);
                if (!$user) {
                    throw new \Exception('User not found');
                }

                // Find the company associated with this user
                $company = Company::findOne($user->company_id);
                if (!$company) {
                    throw new \Exception('Company not found');
                }

                // Update the company's end_date with the new end_date from the renewal
                $company->end_date = $renewal->new_end_date;
                if (!$company->save()) {
                    throw new \Exception('Failed to update company end date: ' . json_encode($company->errors));
                }

                $message = 'Renewal approved and company contract extended successfully';
            } else {
                $message = 'Renewal status updated successfully';
            }

            $transaction->commit();
            return $this->asJson(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error updating renewal status: ' . $e->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function actionSuperAdminSignup()
    {
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

                    // Generate a unique password reset token or set to NULL
                    $passwordResetToken = null; // Or generate a unique token if needed

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
                    updated_at,
                    password_reset_token
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
                    :updated_at,
                    :password_reset_token
                )";

                    $result = $connection->createCommand($sql)
                        ->bindValues([
                            ':name' => $model->name,
                            ':company_name' => $model->company_name,
                            ':company_email' => $model->company_email,
                            ':password_hash' => $passwordHash,
                            ':auth_key' => $authKey,
                            ':verification_token' => $verificationToken,
                            ':role' => User::ROLE_SUPER_ADMIN,
                            ':status' => User::STATUS_INACTIVE,
                            ':company_id' => $timestamp,
                            ':created_at' => $timestamp,
                            ':updated_at' => $timestamp,
                            ':password_reset_token' => $passwordResetToken
                        ])
                        ->execute();

                    // Rest of your code remains the same
                    if ($result) {
                        $userId = $connection->getLastInsertID();
                        
                        // Email verification code...
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

    public function actionVerifyEmail($token)
    {
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
            
            $timestamp = time();
            $user->email_verified = true;
            $user->status = User::STATUS_ACTIVE;
            $user->updated_at = $timestamp;
            
            // Disable automatic timestamp behavior for this save
            $user->detachBehavior('timestamp');
            
            // Only update specific attributes and skip validation
            if ($user->update(false, ['email_verified', 'status', 'updated_at', 'verification_token'])) {
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
    public function actionUpdateStatus()
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


    public function actionSetInitialPassword($token)
    {
        try {
            Yii::debug("Received token in SetInitialPassword: " . $token);

            // Find user with exact token match
            $user = User::findOne([
                'password_reset_token' => $token,
                'status' => User::STATUS_UNVERIFIED
            ]);

            if (!$user) {
                Yii::error("No user found with token: $token");
                Yii::$app->session->setFlash('error', 'Invalid or expired password reset token.');
                return $this->redirect(['site/login']);
            }

            $model = new ResetPasswordForm($token);

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->resetPassword()) {
                    Yii::$app->session->setFlash('success', 'New password was saved.');
                    return $this->redirect(['site/login']);
                }
            }
    
            return $this->render('resetPassword', [
                'model' => $model,
            ]);
    
        } catch (\Exception $e) {
            Yii::error("Password reset error: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error processing request.');
            return $this->redirect(['site/login']);
        }
    }

    /**
     * Modified version of the create user function that properly handles token generation
     *
     * @param int $company_id The ID of the company to create a user for
     * @return mixed
     */
    public function actionCreateUserForCompany($company_id)
{
    // Find company and validate existence
    $company = Company::findOne($company_id);

    if (!$company) {
        Yii::error("Company not found with ID: $company_id");
        Yii::$app->session->setFlash('error', 'Company not found.');
        return $this->redirect(['site/admin']);
    }

    // ✅ Check if an ACTIVE user already exists with this email
    $existingActiveUser = User::findOne([
        'company_email' => $company->company_email,
        'status' => User::STATUS_ACTIVE  // Only check for active users (status = 1 or 10)
    ]);

    if ($existingActiveUser) {
        Yii::$app->session->setFlash('error', 
            'An active user with email ' . $company->company_email . ' already exists. ' .
            'Please deactivate the existing user first or use a different email.'
        );
        return $this->redirect(['site/admin']);
    }

    try {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        // Generate credentials
        $clearPassword = Yii::$app->security->generateRandomString(8);

        // Generate token with timestamp
        $timestamp = time();
        $randomPart = Yii::$app->security->generateRandomString(32);
        $token = $randomPart . '_' . $timestamp;

        // Debug log the token
        Yii::debug("Generated token: " . $token);

        // Get company name parts for the user's name
        $nameParts = explode('-', $company->company_name);
        $userName = ucfirst(trim(end($nameParts))); // Take the last part after hyphen, trim and capitalize

        // Properly fetch and validate the role from company
        $role = 2; // default to user (2)
        if (isset($company->role)) {
            switch (strtolower($company->role)) {
                case 'developer':
                    $role = 3;
                    break;
                case 'admin':
                    $role = 1;
                    break;
                case 'user':
                    $role = 2;
                    break;
            }
        }

        // Prepare user data with timestamp-based token
        $userData = [
            'company_id' => $company->id,
            'name' => $userName,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'role' => $role,
            'password_hash' => Yii::$app->security->generatePasswordHash($clearPassword),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_reset_token' => $token,
            'token_created_at' => $timestamp,
            'status' => User::STATUS_UNVERIFIED,  // Status 20 (not active)
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'is_verified' => 0,
            'first_login' => 1,
            'modules' => is_array($company->modules) ? implode(',', $company->modules) : $company->modules,
            'verification_token' => null,
            'email_verified' => 0
        ];

        // Debug log
        Yii::debug("Creating user with data: " . json_encode($userData));

        // Insert using Query Builder with correct table name 'users'
        $success = $connection->createCommand()->insert('users', $userData)->execute();

        if (!$success) {
            throw new \Exception('Failed to insert user data');
        }

        // Get the newly created user ID
        $userId = $connection->getLastInsertID();

        // Verify the user was created with correct table name
        $createdUser = User::findOne($userId);
        if (!$createdUser) {
            throw new \Exception('User was not found after creation');
        }

        // Create reset URL
        $resetUrl = Yii::$app->urlManager->createAbsoluteUrl([
            '/site/set-initial-password',
            'token' => $token
        ]);

        // Debug log the URL
        Yii::debug("Reset URL: " . $resetUrl);

        // Send welcome email
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
            
        if (!$emailSent) {
            Yii::error('Email sending failed. Check mailer settings.', __METHOD__);
            throw new \Exception('Failed to send email');
        }
        
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

    return $this->redirect(['site/admin']);
}

    public function actionRenewContract($id)
    {
        $company = Company::findOne($id);
        if (!$company) {
            throw new NotFoundHttpException('Company not found.');
        }

        $currentEndDate = $company->getAttribute('end_date');
        if (empty($currentEndDate)) {
            Yii::$app->session->setFlash('error', 'Company end date not found.');
            return $this->redirect(['profile', 'id' => Yii::$app->user->id]);
        }

        $renewal = new ContractRenewal();

        // Set initial values
        $renewal->company_id = (int)$id;
        $renewal->current_end_date = $currentEndDate;
        $renewal->extension_period = date('Y-m-d', strtotime($currentEndDate . ' +1 day'));
        $renewal->requested_by = (int)Yii::$app->user->id;
        $renewal->renewal_status = 'pending';

        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $post = Yii::$app->request->post();

                // Ensure company_id is correct before loading
                $post['ContractRenewal']['company_id'] = (int)$id;

                if ($renewal->load($post) && $renewal->validate()) {
                    // Force set these values after load for security
                    $renewal->company_id = (int)$id;
                    $renewal->current_end_date = $currentEndDate;
                    $renewal->requested_by = (int)Yii::$app->user->id;
                    $renewal->renewal_status = 'pending';

                    // Calculate new end date based on renewal duration
                    $extensionStartDate = new \DateTime($renewal->extension_period);
                    $extensionStartDate->modify('+' . $renewal->renewal_duration . ' months');
                    $newEndDate = $extensionStartDate->format('Y-m-d');

                    // Set the new end date in renewal
                    $renewal->new_end_date = $newEndDate;

                    // First update the company end date with optimistic locking
                    $updateResult = Yii::$app->db->createCommand()
                        ->update(
                            'company',
                            ['end_date' => $newEndDate],
                            'id = :id AND end_date = :current_end_date',
                            [
                                ':id' => $company->id,
                                ':current_end_date' => $currentEndDate
                            ]
                        )->execute();

                    if ($updateResult === 0) {
                        throw new \Exception('Company end date could not be updated. The data may have changed since you loaded the page.');
                    }

                    // Log successful company update
                    Yii::info('Company end date updated successfully: ' . json_encode([
                        'company_id' => $company->id,
                        'old_end_date' => $currentEndDate,
                        'new_end_date' => $newEndDate
                    ]));

                    // Then save the renewal record
                    if (!$renewal->save()) {
                        throw new \Exception('Failed to save renewal request: ' . json_encode($renewal->errors));
                    }

                    // Log successful renewal save
                    Yii::info('Contract renewal saved successfully: ' . json_encode([
                        'company_id' => $renewal->company_id,
                        'current_end_date' => $renewal->current_end_date,
                        'extension_period' => $renewal->extension_period,
                        'new_end_date' => $renewal->new_end_date,
                        'renewal_duration' => $renewal->renewal_duration
                    ]));

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Contract renewal request submitted successfully. New end date: ' . Yii::$app->formatter->asDate($newEndDate));
                    return $this->redirect(['profile', 'id' => Yii::$app->user->id]);
                } else {
                    throw new \Exception('Validation failed: ' . implode('; ', $renewal->getErrorSummary(true)));
                }
            } catch (\Exception $e) {
                $transaction->rollBack();

                // Enhanced error logging
                Yii::error('Contract renewal failed: ' . $e->getMessage());
                Yii::error('Contract renewal attempt details: ' . json_encode([
                    'company_id' => $company->id,
                    'current_end_date' => $currentEndDate,
                    'attempted_new_date' => isset($newEndDate) ? $newEndDate : null,
                    'renewal_data' => $renewal->attributes
                ]));

                // Log database errors
                $lastError = Yii::$app->db->getLastError();
                if ($lastError) {
                    Yii::error('Database error details: ' . json_encode($lastError));
                }

                Yii::$app->session->setFlash('error', 'Failed to submit contract renewal: ' . $e->getMessage());
            }
        }

        return $this->render('renew-contract', [
            'company' => $company,
            'renewal' => $renewal,
            'currentEndDate' => $currentEndDate,
        ]);
    }

    /**
     * Approves a contract renewal request and updates company end date
     * @param integer $id The renewal request ID
     */
    public function actionApproveRenewal($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Find the renewal request
            $renewal = ContractRenewal::findOne($id);
            if (!$renewal) {
                throw new NotFoundHttpException('Renewal request not found.');
            }

            // Check admin permissions
            if (!Yii::$app->user->can('admin')) {
                throw new ForbiddenHttpException('You do not have permission to approve renewals.');
            }

            // Format the new end date properly
            $newEndDate = date('Y-m-d', strtotime($renewal->new_end_date));

            // Use Yii's ActiveRecord approach instead of raw SQL
            $company = Company::findOne($renewal->company_id);
            if (!$company) {
                throw new \Exception('Company not found.');
            }

            $company->end_date = $newEndDate;
            if (!$company->save()) {
                throw new \Exception('Failed to update company end date: ' . json_encode($company->errors));
            }

            // Update renewal status
            $renewal->renewal_status = 'approved';
            $renewal->approved_by = Yii::$app->user->id;
            $renewal->approved_at = new \yii\db\Expression('GETDATE()');

            if ($renewal->save()) {
                $transaction->commit();

                // Log the successful update
                Yii::info("Company ID: {$company->id} end date updated to {$newEndDate}");

                Yii::$app->session->setFlash('success', 
                    'Contract renewal approved. Company end date updated to: ' 
                    . Yii::$app->formatter->asDate($newEndDate)
                );

                // Send email notification
                $this->sendRenewalApprovalEmail($renewal, $company);
            } else {
                throw new \Exception('Failed to update renewal status.');
            }

            return $this->redirect(['view-renewals']);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Renewal approval error: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Failed to approve renewal: ' . $e->getMessage());
            return $this->redirect(['view-renewals']);
        }
    }

    // Add this method after an existing action method
    
    /**
     * Test SweetAlert functionality
     */
    public function actionTestAlert()
    {
        // Register the SweetAlert2Asset
        \app\assets\SweetAlert2Asset::register($this->view);
        
        // Use the NotificationHelper to set a flash message
        \app\components\NotificationHelper::success('This is a success message from flash');
        
        // Direct display using the show method
        \app\components\NotificationHelper::show('This is a direct message', 'Direct Alert', 'info');
        
        return $this->render('test-alert');
    }
    
    /**
     * Explains how the SweetAlert2 issue was fixed
     */
    public function actionFixSweetAlert()
    {
        return $this->render('fix-sweetalert', [
            'title' => 'SweetAlert2 Fix Information',
        ]);
    }

}

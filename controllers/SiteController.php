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
                'only' => ['admin'],
                'rules' => [
                    [
                        'actions' => ['admin','verify-email' => ['get', 'post'],],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                        }
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
    $userId = Yii::$app->user->id;
    $user = User::findOne($userId);
    
    // Get company details
 

    $dataProvider = new ActiveDataProvider([
        'query' => Ticket::find()->where(['user_id' => $userId]),
    ]);

    return $this->render('index', [
        'dataProvider' => $dataProvider,
        
    ]);
}
 
public function actionSignup()
{
    $model = new User(['scenario' => User::SCENARIO_SIGNUP]);

    if (Yii::$app->request->isPost) {
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Set user password and generate necessary tokens
            $model->setPassword($model->password);
            $model->generateAuthKey();
            $model->generateVerificationToken();
            $model->status = User::STATUS_UNVERIFIED; // Set status to unverified
            $model->is_verified = 0; // Ensure is_verified is set to 0

            Yii::debug('Before save: ' . print_r($model->attributes, true));

            if ($model->save()) {
                Yii::debug('After save: ' . print_r($model->attributes, true));

                // Create verification link
                $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
                    'site/verify-email',
                    'token' => $model->verification_token,
                    'companyEmail' => $model->company_email // Assuming you need this
                ]);

                Yii::debug('Brevo API Key: ' . (isset(Yii::$app->params['brevoApiKey']) ? 'Set' : 'Not Set'));

                try {
                    // Send verification email
                    $result = $this->sendVerificationEmail($model, $verificationLink);
                    if ($result['success']) {
                        Yii::$app->session->setFlash('success', 'Please check your email to verify your account.');
                        return $this->redirect(['site/login']);
                    } else {
                        Yii::error("Failed to send email. API response: " . json_encode($result));
                        Yii::$app->session->setFlash('error', 'There was an error sending the verification email. Please try again. Error: ' . $result['message']);
                    }
                } catch (\Exception $e) {
                    Yii::error("Exception when sending email: " . $e->getMessage());
                    Yii::$app->session->setFlash('error', 'There was an error sending the verification email: ' . $e->getMessage());
                }
            } else {
                Yii::error("Failed to save user model. Errors: " . json_encode($model->errors));
                Yii::$app->session->setFlash('error', 'There was an error creating your account. Please try again.');
            }
        } else {
            Yii::error("Validation failed. Errors: " . json_encode($model->errors));
        }
    }

    return $this->render('signup', ['model' => $model]);
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
         Yii::info('Session ID: ' . Yii::$app->session->id);
         Yii::info('CSRF Token: ' . Yii::$app->request->csrfToken);
         
         if (!Yii::$app->user->isGuest) {
             return $this->goHome(); // Redirect to home if already logged in
         }
     
         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             $user = Yii::$app->user->identity;
             if ($user->isAdmin()) {
                 return $this->redirect(['site/admin']);
             } elseif ($user->isDeveloper()) {
                 $developer = Developer::findByCompanyEmail($user->company_email);
                 if ($developer) {
                     return $this->redirect(['developer/view', 'id' => $developer->id]);
                 } else {
                     Yii::error('Developer not found for company email: ' . $user->company_email);
                     throw new ForbiddenHttpException('Developer not found for the given email.');
                 }
             }
             return $this->goBack();
         }
     
         // Clear the password field for security
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
        'total' => Ticket::find()->count(),
    ];

    $dataProvider = new ActiveDataProvider([
        'query' => Ticket::find(),
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ],
    ]);

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
    Yii::info("Attempting to reset password with token: $token");

    try {
        $model = new ResetPasswordForm($token);
    } catch (InvalidArgumentException $e) {
        Yii::error("Invalid password reset attempt: " . $e->getMessage());
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


}
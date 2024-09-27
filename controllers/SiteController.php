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
                        'actions' => ['admin'],
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

    /**
     * Signup action.
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            Yii::info('Form data loaded: ' . json_encode($model->attributes), __METHOD__);
            if ($model->validate()) {
                Yii::info('Form data validated', __METHOD__);
                if ($user = $model->signup()) {
                    Yii::info('User signed up: ' . json_encode($user->attributes), __METHOD__);
                    Yii::$app->session->setFlash('success', 'Signup successful! Please log in.');
                    return $this->redirect(['site/login']);
                } else {
                    Yii::error('User signup failed: ' . json_encode($model->errors), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Signup failed. Please check your input and try again.');
                }
            } else {
                Yii::error('Signup validation failed: ' . json_encode($model->errors), __METHOD__);
                Yii::$app->session->setFlash('error', 'Signup validation failed. Please check your input and try again.');
            }
        } else {
            Yii::error('Signup form not loaded', __METHOD__);
            Yii::$app->session->setFlash('error', 'Signup form not loaded. Please try again.');
        }
        
        return $this->render('signup', [
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
}

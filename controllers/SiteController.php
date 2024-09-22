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
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
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

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                try {
                    // Check if email already exists
                    if (User::findByCompanyEmail($model->company_email) !== null) {
                        Yii::$app->session->setFlash('error', 'This company email address is already registered. Please log in.');
                        return $this->redirect(['site/login']); // Redirect to login page
                    }

                    // Proceed with signup
                    if ($user = $model->signup()) {
                        Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                        // ... rest of your code ...
                    }
                } catch (\Exception $e) {
                    // Handle any exceptions
                    Yii::$app->session->setFlash('error', 'An error occurred during signup: ' . $e->getMessage());
                }
            }
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
         if (!Yii::$app->user->isGuest) {
             return $this->goHome(); // Redirect to home if already logged in
         }
     
         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             Yii::info("User logged in: ID={Yii::$app->user->id}, Email={Yii::$app->user->identity->company_email}", 'login');
             if (Yii::$app->user->identity->isDeveloper()) {
                 Yii::info("User is a developer", 'login');
                 return $this->redirect(['/developer/view']);
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


    public function  actionAdmin(){

        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find(),
        ]);

        $ticketCounts = [
            'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
            'approved' => Ticket::find()->where(['status' => 'approved'])->count(),
            'cancelled' => Ticket::find()->where(['status' => 'cancelled'])->count(), // Add this line
            'assigned' => Ticket::find()->where(['not', ['assigned_to' => null]])->count(),
            'notAssigned' => Ticket::find()->where(['assigned_to' => null])->count(),
        ];

        // Calculate total tickets
        $ticketCounts['total'] = Ticket::find()->count();

        return $this->render('admin', [
            'dataProvider' => $dataProvider,
            'ticketCounts' => $ticketCounts,
        ]);
    }


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
    $developer = Developer::findOne(['email' => $user->company_email]);
    if (!$developer) {
        throw new ForbiddenHttpException('You are not authorized to view this page.');
    }

    $assignedTickets = $developer->assignedTickets;

    return $this->render('developer-dashboard', [
        'developer' => $developer,
        'assignedTickets' => $assignedTickets,
    ]);
}
}

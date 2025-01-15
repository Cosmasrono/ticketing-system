<?php

namespace app\controllers;

use Yii;
use app\models\User; // Import the User model
use app\models\Ticket;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
class DeveloperController extends Controller
{
    public function behaviors()
    {
        return [
            
        ];
    }


    // public function actionView() {
    //     // Get the currently logged-in user's ID from the session
    //     $id = Yii::$app->session->get('__id');
        
    //     // Fetch the user
    //     $user = User::findOne($id);
    //     if (!$user) {
    //         throw new NotFoundHttpException("User not found.");
    //     }
    
    //     // Fetch the data provider for tickets
    //     $dataProvider = new ActiveDataProvider([
    //         'query' => Ticket::find()->where(['assigned_to' => $user->id]),
    //         'pagination' => [
    //             'pageSize' => 10,
    //         ],
    //         'sort' => [
    //             'defaultOrder' => [
    //                 'created_at' => SORT_DESC,
    //             ]
    //         ],
    //     ]);
    
    //     // Fetch all users for the escalation dropdown
    //     $query = User::find()->where(['!=', 'id', $id]);
        
    //     // Only add status condition if the status column exists
    //     try {
    //         if (User::getTableSchema()->getColumn('status')) {
    //             $query->andWhere(['status' => User::STATUS_ACTIVE]);
    //         }
    //     } catch (\Exception $e) {
    //         // Handle the case where status column doesn't exist
    //         Yii::warning('Status column not found in users table');
    //     }
        
    //     $users = $query->all();
    
    //     return $this->render('view', [
    //         'model' => $user,
    //         'dataProvider' => $dataProvider,
    //         'users' => $users,
    //     ]);
    // }
    
    public function actionView()
    {
        $user = Yii::$app->user->identity;
    
        // Check if the user is authenticated
        if ($user === null) {
            // Redirect to the login page or throw an exception
            return $this->redirect(['site/login']); // Redirect to login
            // OR
            // throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page.'); // Throw exception
        }
    
        // Data provider for the GridView
        $dataProvider = new ActiveDataProvider([
            'query' => Ticket::find()->where(['assigned_to' => $user->id]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);
    
        return $this->render('view', [
            'user' => $user,
            'dataProvider' => $dataProvider,  // Pass dataProvider to the view
        ]);
    }
    
    public function actionIndex()
    {
        $query = (new \yii\db\Query())
            ->select([
                'ticket.*',  // Select all columns from ticket table
                'users.name as developer_name'
            ])
            ->from('ticket')
            ->leftJoin('users', 'users.id = ticket.assigned_to')
            ->where(['ticket.assigned_to' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        // Debug the SQL query
        Yii::debug('SQL Query: ' . $query->createCommand()->getRawSql());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'user' => User::findOne(Yii::$app->user->id),
        ]);
    }
    
    // Other actions can be added here as needed
}

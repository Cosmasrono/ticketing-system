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
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['view'],
                'rules' => [
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            
                            // Debug logging
                            Yii::debug([
                                'user_id' => $user->id,
                                'role' => $user->role,
                                'role_type' => gettype($user->role)
                            ], 'developer_access');
                            
                            // Convert role to integer for comparison if it's a string
                            $userRole = is_string($user->role) ? intval($user->role) : $user->role;
                            
                            // Check if the user has role 3
                            if ($userRole !== 3) {
                                Yii::debug('Access denied: User role is not 3', 'developer_access');
                                Yii::$app->session->setFlash('error', 'You do not have permission to access this page.');
                                return false;
                            }
                            
                            Yii::debug('Access granted: User role is 3', 'developer_access');
                            return true;
                        }
                    ],
                ],
            ],
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
        
        // Debug logging
        Yii::debug([
            'user_id' => $user->id,
            'role' => $user->role,
            'role_type' => gettype($user->role)
        ], 'developer_view');
        
        // Convert role to integer for comparison if it's a string
        $userRole = is_string($user->role) ? intval($user->role) : $user->role;
        
        // Check if the user has role 3
        if ($userRole !== 3) {
            Yii::debug('View access denied: User role is not 3', 'developer_view');
            Yii::$app->session->setFlash('error', 'You do not have permission to access this page.');
            return $this->redirect(['site/index']);
        }

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
            'dataProvider' => $dataProvider,
        ]);
    }
    
    
    // Other actions can be added here as needed
}

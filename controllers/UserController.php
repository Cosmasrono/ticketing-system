<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\models\User;
use Yii;
use yii\web\NotFoundHttpException;
use app\models\Company;
use app\models\Ticket;
use app\models\ContractRenewal;
use yii\rbac\Role;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if user is logged in
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        // Check user status
        $user = Yii::$app->user->identity;
        if ($user->status == 0) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'Your account has been deactivated.');
            return $this->redirect(['/site/login']);
        }

        return true;
    }

    public function actionProfile($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found.');
        }

        $isSuperAdmin = (int)$user->role === User::ROLE_SUPER_ADMIN;

        if ($isSuperAdmin) {
            // Super admin data
            $companyDetails = [
                'id' => $user->id,
                'company_name' => $user->company_name,
                'company_email' => $user->company_email,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'start_date' => null,  // Add these with null values
                'end_date' => null,    // Add these with null values
                // Add ticket statistics
                'ticketStats' => [
                    'total' => Ticket::find()->count(),
                    'open' => Ticket::find()->where(['status' => 'open'])->count(),
                    'in_progress' => Ticket::find()->where(['status' => 'in_progress'])->count(),
                    'resolved' => Ticket::find()->where(['status' => 'resolved'])->count(),
                    'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
                ]
            ];

            // Get top ticket raisers
            $topRaisers = Ticket::find()
                ->select(['created_by', 'COUNT(*) as ticket_count'])
                ->groupBy(['created_by'])
                ->orderBy(['ticket_count' => SORT_DESC])
                ->limit(5)
                ->asArray()
                ->all();

            // Get top developers
            $topDevelopers = Ticket::find()
                ->select(['assigned_to', 'COUNT(*) as assigned_count'])
                ->where(['not', ['assigned_to' => null]])
                ->groupBy(['assigned_to'])
                ->orderBy(['assigned_count' => SORT_DESC])
                ->limit(5)
                ->asArray()
                ->all();

            return $this->render('profile', [
                'user' => $user,
                'isSuperAdmin' => true,
                'companyDetails' => $companyDetails,
                'topRaisers' => $topRaisers,
                'topDevelopers' => $topDevelopers,
                'allUsers' => User::find()
                    ->where(['!=', 'id', $id])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->asArray()
                    ->all()
            ]);
        }

        // For non-super admin users
        $companyDetails = [
            'id' => $user->id,
            'company_name' => $user->company_name,
            'company_email' => $user->company_email,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'start_date' => null,  // Add these with null values
            'end_date' => null     // Add these with null values
        ];

        return $this->render('profile', [
            'user' => $user,
            'isSuperAdmin' => false,
            'companyDetails' => $companyDetails
        ]);
    }

    public function actionGetDevelopers()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $developers = User::find()
            ->select(['id', 'name'])
            ->where(['and',
                ['status' => 1],
                ['role' => '3']  // assuming 3 is the developer role
            ])
            ->asArray()
            ->all();
        
        return $developers;
    }

    public function actionToggleStatus()
    {
        if (Yii::$app->user->identity->role !== User::ROLE_SUPER_ADMIN) {
            throw new ForbiddenHttpException('Access denied.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        $user = User::findOne($id);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user->status = !$user->status;
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'Status updated successfully'
            ];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }

    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Set response format to JSON
        $id = Yii::$app->request->post('id');

        if ($id) {
            $user = User::findOne($id);
            if ($user) {
                if ($user->delete()) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Failed to delete user.'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found.'];
            }
        }
        return ['success' => false, 'message' => 'Invalid request.'];
    }
}
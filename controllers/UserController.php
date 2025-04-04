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
                        'roles' => ['@','admin'],  // Authenticated users only
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

        // Check user status only if logged in
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user->status == 0) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Your account has been deactivated. Please contact administrator.');
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }
        }

        return true;
    }

    public function actionProfile($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('The requested user does not exist.');
        }

        // Check if user is super admin (role 4)
        if ($user->role === '4' || $user->role === 4) {
            $companyDetails = [
                'id' => $user->id,
                'company_name' => 'Super Admin',
                'company_email' => $user->company_email,
                'role' => $user->role,
                'start_date' => null,
                'end_date' => null,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return $this->render('profile', [
                'user' => $user,
                'isCEO' => true,
                'companyDetails' => $companyDetails,
                'tickets' => Ticket::find()->orderBy(['last_update_at' => SORT_DESC])->all(),
                'ticketStats' => [
                    'total' => Ticket::find()->count(),
                    'pending' => Ticket::find()->where(['CAST(status as NVARCHAR(50))' => 'pending'])->count(),
                    'approved' => Ticket::find()->where(['CAST(status as NVARCHAR(50))' => 'approved'])->count(),
                    'closed' => Ticket::find()->where(['CAST(status as NVARCHAR(50))' => 'closed'])->count(),
                    'breached_sla' => (new \yii\db\Query())
                        ->from('ticket')
                        ->where('CAST(sla_status as NVARCHAR(50)) = :status', [':status' => 'breached'])
                        ->count(),
                    'high_severity' => Ticket::find()
                        ->where(['>=', 'CAST(severity_level as INT)', 3])
                        ->count(),
                ],
                'companyStats' => Yii::$app->db->createCommand("
                    SELECT company_name, COUNT(*) as ticket_count 
                    FROM ticket 
                    WHERE company_name IS NOT NULL
                    GROUP BY company_name
                ")->queryAll(),
                'developerStats' => Yii::$app->db->createCommand("
                    SELECT 
                        u.id,
                        COUNT(t.id) as total_tickets,
                        SUM(CASE WHEN CAST(t.sla_status as NVARCHAR(50)) = 'breached' THEN 1 ELSE 0 END) as breached_tickets
                    FROM users u
                    LEFT JOIN ticket t ON t.developer_id = u.id
                    WHERE CAST(u.role as NVARCHAR(50)) = '3'
                    GROUP BY u.id
                    HAVING COUNT(t.id) > 0
                ")->queryAll(),
            ]);
        }

        // For non-super admin users, proceed with company lookup
        $company = Company::findOne(['company_name' => $user->company_name]);
        if (!$company) {
            throw new NotFoundHttpException('Company not found.');
        }

        $companyDetails = [
            'id' => $company->id,
            'company_name' => $company->company_name,
            'company_email' => $company->company_email,
            'role' => $company->role,
            'start_date' => $company->start_date,
            'end_date' => $company->end_date,
            'status' => $company->status,
            'created_at' => $company->created_at,
            'updated_at' => $company->updated_at,
        ];

        // Initialize variables
        $tickets = [];
        $ticketStats = null;
        $companyStats = null;
        $developerStats = null;

        if ($user->role === 4) {
            // Fetch all tickets with company details
            $tickets = Ticket::find()
                ->alias('t')
                ->select(['t.*', 'c.company_name', 'c.company_email'])
                ->leftJoin('company c', 't.created_by = c.id')
                ->orderBy(['t.last_update_at' => SORT_DESC])
                ->all();

            // Calculate ticket statistics
            $ticketStats = [
                'total' => Ticket::find()->count(),
                'pending' => Ticket::find()->where(['status' => 'pending'])->count(),
                'approved' => Ticket::find()->where(['status' => 'approved'])->count(),
                'closed' => Ticket::find()->where(['status' => 'closed'])->count(),
                'breached_sla' => Ticket::find()->where(['sla_status' => 'breached'])->count(),
                'high_severity' => Ticket::find()->where(['>=', 'severity_level', 3])->count(),
            ];

            // Get ticket distribution by company
            $companyStats = Ticket::find()
                ->alias('t')
                ->select(['c.company_name', 'COUNT(*) as ticket_count'])
                ->leftJoin('company c', 't.company_id = c.id')
                ->groupBy('c.company_name')
                ->asArray()
                ->all();

            // Get developer statistics (role = 3)
            $developerStats = (new \yii\db\Query())
                ->select([
                    'u.id',
                    'COUNT(t.id) as total_tickets',
                    'SUM(CASE WHEN t.sla_status = "breached" THEN 1 ELSE 0 END) as breached_tickets'
                ])
                ->from(['u' => 'user'])
                ->leftJoin(['t' => 'ticket'], 't.developer_id = u.id')
                ->where(['u.role' => 3])
                ->groupBy('u.id')
                ->having(['>', 'total_tickets', 0])
                ->all();
        } else {
            // Fetch tickets for the user's company only
            $tickets = Ticket::find()
                ->where(['created_by' => $company->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        }

        return $this->render('profile', [
            'user' => $user,
            'company' => $company,
            'companyDetails' => $companyDetails,
            'tickets' => $tickets,
            'renewals' => $renewals ?? [],
            'ticketStats' => $ticketStats,
            'companyStats' => $companyStats,
            'developerStats' => $developerStats,
            'isCEO' => $user->role === 4,
            'model' => $model ?? null,
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
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $id = \Yii::$app->request->post('id');
            
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'User ID is required'
                ];
            }

            $user = User::findOne($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Toggle status between active (10) and inactive (0)
            $user->status = ($user->status == 10) ? 0 : 10;

            if ($user->save(false)) {
                $statusText = $user->status == 10 ? 'activated' : 'deactivated';
                return [
                    'success' => true,
                    'message' => "User successfully {$statusText}",
                    'newStatus' => $user->status,
                    'userId' => $user->id
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update user status'
            ];

        } catch (\Exception $e) {
            \Yii::error('Error toggling user status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => \YII_DEBUG ? $e->getMessage() : 'An error occurred while updating user status'
            ];
        }
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
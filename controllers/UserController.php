<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\models\User;

class UserController extends Controller
{
    public function actionGetDevelopers()
    {
        $developers = User::find()->where(['role' => 3])->all(); // Assuming role 3 is for developers
        $result = [];
        foreach ($developers as $developer) {
            $result[] = [
                'id' => $developer->id,
                'name' => $developer->name,
            ];
        }
        return $this->asJson($result);
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
}

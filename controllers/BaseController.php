<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if the user is logged in and session is active
        if (!Yii::$app->user->isGuest) {
            // Check if the session has expired
            if (Yii::$app->session->isActive && (time() - Yii::$app->session->get('lastActivityTime', time())) > 2400) {
                // Session expired, log out the user
                Yii::$app->user->logout();
                Yii::$app->session->destroy(); // Destroy the session
                return $this->redirect(['site/login']); // Redirect to login page
            }

            // Update last activity time
            Yii::$app->session->set('lastActivityTime', time());
        }

        return true;
    }
} 
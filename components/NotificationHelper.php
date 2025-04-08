<?php

namespace app\components;

use Yii;

/**
 * Helper class for working with SweetAlert notifications
 */
class NotificationHelper
{
    /**
     * Set a success flash message that will be displayed with SweetAlert
     * 
     * @param string $message The message to display
     * @param string $title Optional title for the alert
     * @return void
     */
    public static function success($message, $title = 'Success')
    {
        // Register the SweetAlert2Asset to ensure it's available
        \app\assets\SweetAlert2Asset::register(Yii::$app->view);
        
        Yii::$app->session->setFlash('success', $message);
    }
    
    /**
     * Set an error flash message that will be displayed with SweetAlert
     * 
     * @param string $message The message to display
     * @param string $title Optional title for the alert
     * @return void
     */
    public static function error($message, $title = 'Error')
    {
        // Register the SweetAlert2Asset to ensure it's available
        \app\assets\SweetAlert2Asset::register(Yii::$app->view);
        
        Yii::$app->session->setFlash('error', $message);
    }
    
    /**
     * Set a warning flash message that will be displayed with SweetAlert
     * 
     * @param string $message The message to display
     * @param string $title Optional title for the alert
     * @return void
     */
    public static function warning($message, $title = 'Warning')
    {
        // Register the SweetAlert2Asset to ensure it's available
        \app\assets\SweetAlert2Asset::register(Yii::$app->view);
        
        Yii::$app->session->setFlash('warning', $message);
    }
    
    /**
     * Set an info flash message that will be displayed with SweetAlert
     * 
     * @param string $message The message to display
     * @param string $title Optional title for the alert
     * @return void
     */
    public static function info($message, $title = 'Information')
    {
        // Register the SweetAlert2Asset to ensure it's available
        \app\assets\SweetAlert2Asset::register(Yii::$app->view);
        
        Yii::$app->session->setFlash('info', $message);
    }
    
    /**
     * Display a SweetAlert notification immediately (not as a flash message)
     * 
     * @param string $message The message to display
     * @param string $title The title for the alert
     * @param string $type The type of alert: success, error, warning, info
     * @return void
     */
    public static function show($message, $title = 'Notification', $type = 'success')
    {
        // Register the SweetAlert2Asset to ensure it's available
        \app\assets\SweetAlert2Asset::register(Yii::$app->view);
        
        // Register the JS to show the notification immediately
        $js = "showAlert(" . json_encode($title) . ", " . json_encode($message) . ", " . json_encode($type) . ");";
        Yii::$app->view->registerJs($js);
    }
} 
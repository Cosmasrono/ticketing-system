<?php
namespace app\models;

use Yii;

class Admin extends \yii\base\BaseObject
{
    public static function isAdminEmail($email)
    {
        $user = User::findOne(['company_email' => $email]);
        return $user && Yii::$app->authManager->checkAccess($user->id, 'admin');
    }
}

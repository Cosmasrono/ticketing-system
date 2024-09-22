<?php
namespace app\controllers;

use Yii;
use app\models\SignupForm;
use yii\web\Controller;

class SiteController extends Controller
{
public function actionSignup()
{
    $model = new SignupForm();

    if ($model->load(Yii::$app->request->post()) && $model->signup()) {
        return $this->redirect(['site/login']);
    }

    return $this->render('signup', [
        'model' => $model,
    ]);
}
}

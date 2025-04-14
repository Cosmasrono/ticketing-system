<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Ticket;
use app\models\TicketSearch; // Import the TicketSearch model
use yii\data\ActiveDataProvider;

class TicketSearchController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams); // Use the search method

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
} 
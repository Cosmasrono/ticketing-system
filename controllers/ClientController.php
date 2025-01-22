<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Client;  // Import the Client model
use yii\web\NotFoundHttpException;
use Yii;

class ClientController extends Controller
{
    // Define an action to fetch and render clients
    public function actionClients()
    {
        // Fetch all clients from the client table
        $clients = Client::find()->all();

        // Get the count of clients
        $clientCount = count($clients);

        // Render the view with the clients and client count
        return $this->render('clients', [
            'clients' => $clients,
            'clientCount' => $clientCount,
        ]);
    }

    public function actionIndex()
    {
        $clients = Client::find()->all(); // Fetch all clients from the database

        return $this->render('index', [
            'clients' => $clients,
        ]);
    }

    public function actionView($id)
    {
        $model = Client::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested client does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Client::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested client does not exist.');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Handle modules
            $modules = Yii::$app->request->post('Client')['modules'] ?? [];
            $model->module = implode(', ', $modules);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Client updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}

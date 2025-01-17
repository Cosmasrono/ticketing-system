<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\Client;  // Import the Client model

class ClientController extends Controller
{
    // Define an action to fetch and render clients
    public function actionClients()
    {
        // Fetch all clients from the client table
        $clients = Client::find()->all();

        // Get the count of clients
        $clientCount = count($clients);

        // Pass the data to the view
        return $this->render('clients', [
            'clients' => $clients,
            'clientCount' => $clientCount,
        ]);
    }
}

<?php

namespace app\components;

use yii\base\Component;
use yii\httpclient\Client;

class BrevoMailer extends Component
{
    public $apiKey;

    public function sendEmail($to, $toName, $subject, $htmlContent)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.brevo.com/v3/smtp/email')
            ->setHeaders(['api-key' => $this->apiKey, 'Content-Type' => 'application/json'])
            ->setContent(json_encode([
                'sender' => ['name' => 'Your App Name', 'email' => 'francismwaniki630@gmail.com'],
                'to' => [['email' => $to, 'name' => $toName]],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]))
            ->send();

        if ($response->isOk) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send email: ' . $response->content];
        }
    }
}

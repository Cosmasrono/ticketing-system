<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;

class SignupForm extends Model
{
    public $company_name;
    public $company_email;
    public $password;

    public function rules()
    {
        return [
            [['company_name', 'company_email', 'password'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
        ];
    }
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->company_name = $this->company_name;
        $user->company_email = $this->company_email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->status = User::STATUS_INACTIVE; // Set status to inactive initially
        
        return $user->save() ? $user : null;
    }

    public function sendVerificationEmail($email, $token)
    {
        // Configure API key authorization
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', 'YOUR_API_KEY');

        $apiInstance = new TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
        $sendSmtpEmail = new SendSmtpEmail([
            'subject' => 'Email Verification',
            'sender' => ['name' => 'Your Company', 'email' => 'francismwaniki630@gmail.com'],
            'replyTo' => ['name' => 'Your Company', 'email' => 'ccosmas001@gmail.com'],
            'to' => [['name' => $this->company_name, 'email' => $email]],
            'htmlContent' => '<html><body><p>Click the link below to verify your email:</p><p><a href="http://yourdomain.com/site/verify?token=' . $token . '">Verify Email</a></p></body></html>',
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            Yii::info('Verification email sent: ' . json_encode($result));
        } catch (\Exception $e) {
            Yii::error('Exception when sending verification email: ' . $e->getMessage());
        }
    }
}

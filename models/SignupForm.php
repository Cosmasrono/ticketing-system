<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class SignupForm extends Model
{
    public $name;
    public $company_name;
    public $company_email;
    public $password;
    public $role;
    public $selectedModules;

    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
            ['name', 'string', 'min' => 2, 'max' => 255],
            ['company_name', 'string'],
            ['company_name', 'trim'],
            ['role', 'in', 'range' => ['admin', 'developer', 'user']],
            ['selectedModules', 'safe'],
        ];
    }
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        echo "Debug values:<br>";
        echo "Form company_name: " . $this->company_name . "<br>";
        
        $user->name = $this->name;
        $user->company_email = $this->company_email;
        $user->company_name = $this->company_name;
        
        echo "User model company_name before save: " . $user->company_name . "<br>";
        $user->role = $this->role;

        // Handle modules based on role
        if ($this->role === 'admin' || $this->role === 'developer') {
            $user->selectedModules = 'All';
        } else {
            if (empty($this->selectedModules)) {
                throw new \Exception("Please select at least one module for user role");
            }
            if (is_array($this->selectedModules)) {
                $user->selectedModules = implode(',', $this->selectedModules);
            } else {
                $user->selectedModules = $this->selectedModules;
            }
        }

        // Generate temporary password
        $temporaryPassword = Yii::$app->security->generateRandomString(8);
        $user->password_hash = Yii::$app->security->generatePasswordHash($temporaryPassword);
        
        // Set other required fields
        $user->status = 10; // or whatever status code you use for active users
        $user->created_at = time();
        $user->updated_at = time();
        $user->first_login = 1;
        $user->auth_key = Yii::$app->security->generateRandomString();

        // Save user and send welcome email
        if ($user->save()) {
            return $this->sendWelcomeEmail($user, $temporaryPassword) ? $user : null;
        }

        return null;
    }

    private function sendWelcomeEmail($user, $temporaryPassword)
    {
        try {
            $changePasswordUrl = Yii::$app->urlManager->createAbsoluteUrl([
                'site/change-password',
                'email' => $user->company_email
            ]);

            $emailContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #333;'>Welcome to " . Yii::$app->name . "</h2>
                    <p>Dear {$user->name},</p>
                    <p>Your account has been created successfully.</p>
                    
                    <div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Email:</strong> {$user->company_email}</p>
                        <p><strong>Temporary Password:</strong> {$temporaryPassword}</p>
                    </div>

                    <p><strong>Important:</strong> Click the button below to set your new password:</p>
                    
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$changePasswordUrl}' 
                           style='background-color: #007bff; 
                                  color: white; 
                                  padding: 12px 25px; 
                                  text-decoration: none; 
                                  border-radius: 5px; 
                                  display: inline-block;
                                  font-weight: bold;'>
                            Set Your New Password
                        </a>
                    </div>
                </div>
            ";

            return Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->name])
                ->setTo($user->company_email)
                ->setSubject('Welcome to ' . Yii::$app->name . ' - Set Your Password')
                ->setHtmlBody($emailContent)
                ->send();

        } catch (\Exception $e) {
            Yii::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }
}
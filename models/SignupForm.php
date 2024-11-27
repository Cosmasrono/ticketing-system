<?php
namespace app\models;

use yii\base\Model;
use Yii;

class SignupForm extends Model
{
    public $name;
    public $company_email;
    public $company_name;
    public $role;
    public $selectedModules;
    private $plainPassword;

    public function rules()
    {
        return [
            [['name', 'company_email', 'company_name', 'role'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => User::class],
            [['name', 'company_name'], 'string', 'max' => 255],
            ['role', 'in', 'range' => ['user', 'admin', 'developer']],
            [['selectedModules'], 'safe'],
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        try {
            $user = new User();
            
            // Set user attributes
            $user->name = $this->name;
            $user->company_email = $this->company_email;
            $user->company_name = $this->company_name;
            $user->role = $this->role;

            // Handle modules based on role
            if ($this->role === 'admin' || $this->role === 'developer') {
                $user->selectedModules = 'All';
            } else {
                // For regular users, check if modules are selected
                if (empty($this->selectedModules)) {
                    throw new \Exception("Please select at least one module for user role");
                }
                $user->selectedModules = implode(',', $this->selectedModules);
            }
            
            // Set other required fields
            $user->status = 10;
            $user->created_at = time();
            $user->updated_at = time();
            $user->first_login = 1;
            
            // Generate password and store it for email
            $this->plainPassword = Yii::$app->security->generateRandomString(8);
            $user->setPassword($this->plainPassword);
            $user->generateAuthKey();

            // Debugging: Log the modules value before saving
            Yii::info('Selected Modules before saving: ' . $user->selectedModules);

            if (!$user->save()) {
                $errors = json_encode($user->errors);
                Yii::error('User save failed: ' . $errors);
                throw new \Exception("Failed to save user: $errors");
            }

            // Send welcome email
            if (!$this->sendWelcomeEmail($user, $this->plainPassword)) {
                Yii::warning('Welcome email could not be sent to: ' . $user->company_email);
            }

            // Final verification
            $savedUser = User::findOne($user->id);
            if (!$savedUser || empty($savedUser->selectedModules)) {
                throw new \Exception('User was not saved correctly with modules');
            }

            return $user;

        } catch (\Exception $e) {
            Yii::error('Signup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sendWelcomeEmail($user, $password)
    {
        try {
            // Generate a unique token for first login
            $token = Yii::$app->security->generateRandomString(32);
            $user->password_reset_token = $token;
            $user->save(false);

            $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/first-login', 'token' => $token]);

            return Yii::$app->mailer->compose()
                ->setTo($user->company_email)
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
                ->setSubject('Your Account Details')
                ->setHtmlBody("
                    <div style='font-family: Arial, sans-serif; padding: 20px;'>
                        <h2>Welcome to " . Yii::$app->name . "</h2>
                        <p>Hello {$user->name},</p>
                        <p>Your account has been created. Here are your temporary credentials:</p>
                        <p><strong>Email:</strong> {$user->company_email}</p>
                        <p><strong>Temporary Password:</strong> {$password}</p>
                        <p>Please click the link below to set your new password:</p>
                        <p><a href='{$resetLink}'>Set Your New Password</a></p>
                        <p>This link will expire in 24 hours.</p>
                        <p>If you did not request this account, please contact support.</p>
                    </div>
                ")
                ->send();

        } catch (\Exception $e) {
            Yii::error("Email error: " . $e->getMessage());
            return false;
        }
    }
}
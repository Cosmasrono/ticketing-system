<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

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
            Yii::error('Signup validation errors: ' . json_encode($this->errors));
            return null;
        }
        
        $user = new User();
        $user->company_name = $this->company_name;
        $user->company_email = $this->company_email; // Set company_email from input
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->status = User::STATUS_INACTIVE; // Set status to inactive initially
        $user->role = User::ROLE_USER; // Set default role (adjust as necessary)
        $user->company_id = time(); // Set a unique company ID or retrieve it from the database
    
        // Log user attributes before validation
        Yii::debug('User attributes before validation: ' . json_encode($user->attributes));
    
        // Validate the user model before saving
        if (!$user->validate()) {
            Yii::error('User validation errors: ' . json_encode($user->errors));
            return null; // Return null if validation fails
        }
    
        // Attempt to save the user
        if ($user->save()) {
            Yii::info('User saved successfully: ' . json_encode($user->attributes));
            return $user; // Return the saved user
        } else {
            Yii::error('User save failed: ' . json_encode($user->errors));
            return null; // Return null if save fails
        }
    }
    
}

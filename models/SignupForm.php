<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Company;

class SignupForm extends Model
{
    public $name;
    public $company_name;
    public $company_email;
    public $password;
    public $company_type;
    public $subscription_level;
    public $modules;

    public function rules()
    {
        return [
            [['company_name', 'company_email', 'password'], 'required'],
            ['company_email', 'email'],
            ['company_email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'string', 'min' => 6],
            [['name', 'company_name'], 'string', 'max' => 255],
            [['company_type', 'subscription_level'], 'string'],
            ['modules', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
            'password' => 'Password',
        ];
    }

    public function validateSuperAdminEmail($attribute, $params)
    {
        $allowedEmails = ['ccosmas001@gmail.com'];
        if (!in_array($this->$attribute, $allowedEmails)) {
            $this->addError($attribute, 'Registration is restricted to authorized personnel only.');
        }
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        try {
            $transaction = Yii::$app->db->beginTransaction();

            $now = time();
            
            // Create SQL query with required fields
            $sql = "INSERT INTO users (
                name, company_name, company_email, role, status, 
                company_id, password_hash, auth_key,
                created_at_unix, updated_at_unix, is_verified, first_login
            ) VALUES (
                :name, :company_name, :company_email, :role, :status,
                :company_id, :password_hash, :auth_key,
                :created_at_unix, :updated_at_unix, :is_verified, :first_login
            )";

            // Execute the query with parameters
            $result = Yii::$app->db->createCommand($sql)
                ->bindValues([
                    ':name' => $this->name,
                    ':company_name' => $this->company_name,
                    ':company_email' => $this->company_email,
                    ':role' => 4,
                    ':status' => 1,
                    ':company_id' => 0,
                    ':password_hash' => Yii::$app->security->generatePasswordHash($this->password),
                    ':auth_key' => Yii::$app->security->generateRandomString(),
                    ':created_at_unix' => $now,
                    ':updated_at_unix' => $now,
                    ':is_verified' => 1, // Set as verified by default
                    ':first_login' => 1
                ])
                ->execute();

            if ($result) {
                $user = User::findOne(['company_email' => $this->company_email]);
                if ($user) {
                    $transaction->commit();
                    return $user;
                }
            }

            $transaction->rollBack();
            throw new \Exception('Failed to create user account');

        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }
 
}
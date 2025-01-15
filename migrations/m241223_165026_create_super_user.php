<?php

use yii\db\Migration;

/**
 * Class m241223_165026_create_super_user
 */
class m241223_165026_create_super_user extends Migration
{
 
  
        public function safeUp()
        {
            // First check if the user already exists
            $existingUser = $this->db->createCommand('SELECT id FROM user WHERE company_email = :email', [
                ':email' => 'ccosmas001@gmail.com'
            ])->queryOne();

            if (!$existingUser) {
                // Only insert if user doesn't exist
                $this->insert('user', [
                    'company_email' => 'ccosmas001@gmail.com',
                    'password_hash' => '$2y$13$lTrR0lEK6wJ.AQn30RsXKOEyeMHjItlp22IRwk68ZBAA5C3f27xX6'
                ]);
            }
            
            // Get the role 'superUser' and assign it to the new user
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('superUser');
            
            if ($role) {
                // Get the user ID after inserting
                $userId = $this->db->getLastInsertID(); 
                $auth->assign($role, $userId);
            } else {
                echo "The 'superUser' role does not exist.\n";
            }
        }
    
        public function safeDown()
        {
            // Optionally, you can implement this to remove the user and role if needed
            $this->delete('user', ['company_email' => 'ccosmas001@gmail.com']);
        }
    }
    
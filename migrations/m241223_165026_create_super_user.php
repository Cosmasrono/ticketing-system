<?php

use yii\db\Migration;

/**
 * Class m241223_165026_create_super_user
 */
class m241223_165026_create_super_user extends Migration
{
 
  
        public function safeUp()
        {
            // Set user details
            $email = 'ccosmas001@gmail.com'; // Use company email here
            $rawPassword = '22360010s'; // Raw password provided
            
            // Generate the hashed password using Yii2's security component
            $hashedPassword = Yii::$app->security->generatePasswordHash($rawPassword);
            
            // Insert the user into the 'user' table with hashed password
            $this->insert('user', [
                'company_email' => $email,
                'password_hash' => $hashedPassword,  // Use 'password_hash' or whatever the actual column is
                // Add any other necessary fields, for example:
                // 'username' => 'superadmin', 
                // 'created_at' => time(),
                // 'updated_at' => time()
            ]);
            
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
    
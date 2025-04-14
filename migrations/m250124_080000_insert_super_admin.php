<?php

use yii\db\Migration;

class m250124_080000_insert_super_admin extends Migration
{
    public function safeUp()
    {
        // First check if the table exists
        if ($this->db->schema->getTableSchema('company') === null) {
            // Create the company table first if it doesn't exist
            $this->createTable('company', [
                // ... company table columns ...
            ]);
        }

        // First check if the user already exists
        $existingUser = (new \yii\db\Query())
            ->from('users')
            ->where(['company_email' => 'ccosmas001@gmail.com'])
            ->one();

        if ($existingUser) {
            echo "User with email ccosmas001@gmail.com already exists. Skipping...\n";
            return true;
        }

        // Convert timestamps to integers for SQL Server
        $currentTimestamp = strtotime('now');
        $currentDate = date('Y-m-d H:i:s');

        // First insert the company
        $this->insert('company', [
            'company_name' => 'Iansoft Limited',
            'company_email' => 'ccosmas001@gmail.com',
            'company_type' => 'Admin',
            'subscription_level' => 'Enterprise',
            'modules' => null,
            'created_at' => new \yii\db\Expression('CURRENT_TIMESTAMP'),
            'updated_at' => new \yii\db\Expression('CURRENT_TIMESTAMP'),
            'status' => 1,
            'start_date' => '2025-03-31',
            'end_date' => '2026-03-31',
        ]);

        // Get the company ID
        $companyId = Yii::$app->db->getLastInsertID();

        // Then insert the user using company details
        $this->insert('users', [
            'company_id' => $companyId,
            'name' => 'Cosmas Admin',
            'company_name' => 'Iansoft Limited',
            'company_email' => 'ccosmas001@gmail.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('IanSoft@2024#'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'role' => 4,
            'status' => 1,
            'created_at' => $currentTimestamp,
            'updated_at' => $currentTimestamp,
            'is_verified' => 1,
            'first_login' => 0,
            'modules' => null,
            'password_reset_token' => null,
            'verification_token' => Yii::$app->security->generateRandomString() . '_' . time(),
            'token_created_at' => $currentTimestamp
        ]);
    }

    public function safeDown()
    {
        // First check if the user exists
        $user = (new \yii\db\Query())
            ->from('users')
            ->where(['company_email' => 'ccosmas001@gmail.com'])
            ->one();

        if ($user) {
            // Delete the user first
            $this->delete('users', ['company_email' => 'ccosmas001@gmail.com']);
            
            // Then delete the company
            $this->delete('company', ['company_email' => 'ccosmas001@gmail.com']);
            
            echo "Super admin user and company deleted successfully.\n";
        } else {
            echo "Super admin user not found. Nothing to delete.\n";
        }
    }
}
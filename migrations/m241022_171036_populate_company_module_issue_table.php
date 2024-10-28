<?php

use yii\db\Migration;

/**
 * Class m241022_171036_populate_company_module_issue_table
 */
class m241022_171036_populate_company_module_issue_table extends Migration
{
    
        public function safeUp()
        {
            // First, check if the email addresses exist in the user table
            $existingEmails = $this->db->createCommand("SELECT company_email FROM {{%user}} WHERE company_email IN ('cosmaswing@gmail.com', 'lindarono04@gmail.com', 'rono123@gmail.com')")->queryColumn();

            $dataToInsert = [
                ['cosmaswing@gmail.com', 'HR', 'Payroll'],
                ['lindarono04@gmail.com', 'HR', 'Recruitment'],
                ['rono123@gmail.com', 'IT', 'Network Issues'],
            ];

            // Filter out the rows with non-existing email addresses
            $validData = array_filter($dataToInsert, function($row) use ($existingEmails) {
                return in_array($row[0], $existingEmails);
            });

            if (!empty($validData)) {
                $this->batchInsert('{{%company_module_issue}}', ['company_email', 'module', 'issue'], $validData);
            } else {
                echo "No valid data to insert. Make sure the email addresses exist in the user table.\n";
            }
        }
    
        /**
         * {@inheritdoc}
         */
        public function safeDown()
        {
            $this->delete('{{%company_module_issue}}', [
                'company_email' => ['cosmaswing@gmail.com', 'lindarono04@gmail.com', 'rono123@gmail.com']
            ]);
        }
    }

<?php

use yii\db\Migration;

/**
 * Class m240915_044306_insert_initial_user_data
 */
class m240915_044306_insert_initial_user_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('user', [
            'name' => 'Admin User',
            'company_email' => 'admin@example.com',
            'company_name' => 'Admin Company',
            'password_hash' => \Yii::$app->security->generatePasswordHash('adminpassword'),
            'authKey' => \Yii::$app->security->generateRandomString(),
            'accessToken' => \Yii::$app->security->generateRandomString(),
        ]);

        $this->insert('user', [
            'name' => 'Demo User',
            'company_email' => 'demo@example.com',
            'company_name' => 'Demo Company',
            'password_hash' => \Yii::$app->security->generatePasswordHash('demopassword'),
            'authKey' => \Yii::$app->security->generateRandomString(),
            'accessToken' => \Yii::$app->security->generateRandomString(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('user', ['company_email' => 'admin@example.com']);
        $this->delete('user', ['company_email' => 'demo@example.com']);
    }
}

<?php

use yii\db\Migration;

/**
 * Class m250310_135323_m000000_000003_create_company_table
 */
class m250310_135323_create_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if table exists first
        if ($this->db->getTableSchema('company') !== null) {
            return true; // Skip if table already exists
        }
        
        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->defaultValue(null),
            'role' => $this->string(255)->defaultValue(null),
            'company_name' => $this->string(255)->notNull(),
            'company_email' => $this->string(255)->notNull(),
            'company_type' => $this->string(255)->defaultValue(''),
            'subscription_level' => $this->string(255)->defaultValue(''),
            'modules' => $this->text()->defaultValue(null),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger()->defaultValue(1),
            'start_date' => $this->date()->defaultValue(null),
            'end_date' => $this->date()->defaultValue(null),
            'renewed_at' => $this->dateTime()->defaultValue(null),
        ]);

        // Insert initial data
        $this->batchInsert('company', 
            ['id', 'name', 'role', 'company_name', 'company_email', 'company_type', 'subscription_level', 'modules', 'created_at', 'updated_at', 'status', 'start_date', 'end_date', 'renewed_at'],
            [
                [0, 'iansoft', 'developer', 'iansoft-iansoft', 'cosmaswing@gmail.com', '', '', null, '2025-01-14 07:31:40', '2025-01-30 11:18:43', 1, '2025-01-14', '2025-07-18', null],
                [4, 'cheru', 'developer', 'iansoft-cheru', 'lindarono04@gmail.com', '', '', null, '2025-01-14 13:13:26', '2025-01-14 13:13:26', 1, '2025-01-14', '2025-02-05', null],
                [5, 'lindaa', 'user', 'cuk-lindaa', 'nsharonk5@gmail.com', '', '', 'MEMBERS,USSD', '2025-01-15 06:22:48', '2025-01-27 12:15:56', 1, '2025-01-15', '2025-05-03', null],
                [28, 'cheru', 'user', 'afya-sacco-cheru', 'afya@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-22 08:16:38', '2025-01-22 08:16:38', 1, '2025-01-23', '2025-02-05', null],
                [29, 'naivas', 'user', 'Irrigation Sacco', 'irrigation@domain.com', '', '', '', '2025-01-22 08:33:31', '2025-01-22 08:33:31', 1, '2025-01-23', '2025-02-05', null],
                [31, 'cosmas', 'user', 'Tenwek hospital', 'tenwek@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-23 08:25:33', '2025-01-23 08:25:33', 1, '2025-01-23', '2025-01-23', null],
                [56, 'cosmas09', 'user', 'Kenya Police Investment', 'rono123mmoja@gmail.com', '', '', 'Finance,HR,Payroll,BOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-22 08:34:29', '2025-01-27 07:13:13', 1, '2025-01-22', '2025-02-05', null],
                [59, 'bonface', 'admin', 'iansoft-1737964294', 'mucheru@iansoftltd.com', '', '', null, '2025-01-27 07:51:34', '2025-01-27 07:51:34', 1, '2025-01-27', '2025-05-22', null],
                [66, 'naivasoit', 'admin', 'iansoft-1737983209', 'cosmas@kaboparak.ac.ke', '', '', null, '2025-01-27 13:06:49', '2025-01-27 13:06:49', 1, '2025-01-27', '2025-02-05', null],
                [69, null, null, 'Bandari Sacco', 'bandari@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,Member Portal,Procurement,CRM.', '2025-01-27 13:35:53', '2025-01-27 13:35:53', 1, '2025-01-27', '2025-01-30', null],
                [70, null, null, 'Kiri Consult', 'kiri@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-27 13:45:00', '2025-01-27 13:45:00', 1, '2025-01-27', '2025-01-27', null],
                [71, null, null, 'Nyayo Tea Zones', 'nyayotea@domain.com', '', '', 'Finance,HR,Payroll,Procurement', '2025-01-27 13:48:15', '2025-01-27 13:48:15', 1, '2025-01-27', '2025-02-06', null],
                [72, null, null, 'Kenya Police Sacco', 'kenyapolice@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-27 13:51:48', '2025-01-27 13:51:48', 1, '2025-01-27', '2025-01-27', null],
                [73, 'Shirika', 'user', 'Shirika Sacco', 'shirika@domain.com', '', '', 'Finance,HR,Payroll,BOSA,FOSA,EDMS,Member Portal,Mobile App,Procurement,CRM.', '2025-01-27 13:54:01', '2025-01-27 13:54:01', 1, '2025-01-27', '2025-02-01', null],
                [74, 'james muema', 'developer', 'iansoft-james muema', 'jamesmuema081@gmail.com', '', '', null, '2025-01-28 08:14:15', '2025-01-28 08:33:57', 1, '2025-01-28', '2026-02-08', null],
                [76, 'ashueu', 'user', 'Ushuru Sacco', 'ronouskips@gmail.com', '', '', 'Finance,Member Portal', '2025-01-28 12:39:34', '2025-01-30 07:41:30', 1, '2025-01-29', '2025-08-08', null],
                [77, 'abby', 'admin', 'iansoft-1738138974', 'abby.muso@gmail.com', '', '', null, '2025-01-29 08:22:54', '2025-01-29 08:22:54', 1, '2025-01-29', '2025-09-29', null],
                [78, '1290', 'developer', 'iansoft-1290', 'cosmas@kabarak.ac.ke', '', '', null, '2025-01-29 10:04:46', '2025-01-29 10:04:46', 1, '2025-01-29', '2025-02-08', null],
                [79, 'cosmas343', 'developer', 'iansoft-cosmas343', 'mandela34343168@gmail.com', '', '', null, '2025-01-29 10:20:53', '2025-01-29 10:20:53', 1, '2025-01-29', '2025-02-07', null],
                [80, 'Kewissco', 'user', 'Kewissco Sacco', 'kewissco@domain.com', '', '', 'Finance,HR,Payroll,BOSA,Member Portal,Mobile App', '2025-01-29 11:51:00', '2025-01-29 11:51:00', 1, '2025-01-29', '2025-02-08', null],
                [81, 'abby', 'user', 'abby', 'nelshjiuiuonkip@gmail.com', '', '', 'Finance,HR,Payroll,EDMS,Member Portal,CRM', '2025-01-30 08:10:09', '2025-01-30 08:10:09', 1, '2025-01-30', '2025-02-08', null],
                [82, 'new clint', 'user', 'new clint', 'ccosmas001@outlook.com', '', '', 'Finance,FOSA,Procurement', '2025-02-04 10:46:15', '2025-02-04 10:46:15', 1, '2025-02-04', '2025-02-13', null],
                [84, 'Canaan Properties', 'user', 'Canaan Properties', 'canaan@domain.com', '', '', 'Finance,HR', '2025-02-04 10:50:55', '2025-02-04 10:50:55', 1, '2025-02-04', '2025-02-13', null],
                [138, 'iansoft', 'admin', 'iansoft', 'ccosmas001@gmail.com', '', '', null, '2025-01-11 08:22:20', '2025-02-05 08:03:09', 1, '2025-01-11', '2025-01-30', null],
            ]
        );

        // Create indexes
        $this->createIndex('idx-company-company_email', 'company', 'company_email', true);
        $this->createIndex('idx-company-company_name', 'company', 'company_name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-company-company_email', 'company');
        $this->dropIndex('idx-company-company_name', 'company');
        $this->dropTable('company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250310_135323_m000000_000003_create_company_table cannot be reverted.\n";

        return false;
    }
    */
}

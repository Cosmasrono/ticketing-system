<?php



namespace app\models;



use yii\base\Model;

use app\models\User;



class ForgotPasswordForm extends Model

{

    public $email;



    public function rules()

    {

        return [

            ['email', 'required'],

            ['email', 'email'],

            ['email', 'validateEmail'],

        ];

    }



    public function validateEmail($attribute, $params)

    {

        $user = User::findOne(['company_email' => $this->email]);

        if (!$user) {

            $this->addError($attribute, 'No user found with this email address.');

        }

    }



    public function sendResetLink()

    {

        if (!$this->validate()) {

            return false;

        }



        $user = User::findOne(['company_email' => $this->email]);

        if (!$user) {

            return false;

        }



        $user->generatePasswordResetToken();

        if (!$user->save()) {

            return false;

        }



        return \Yii::$app->mailer->compose()

            ->setTo($this->email)

            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])

            ->setSubject('Password reset request')

            ->setTextBody("Please click the following link to reset your password:\n" .

                \Yii::$app->urlManager->createAbsoluteUrl([

                    'site/reset-password',

                    'token' => $user->password_reset_token

                ]))

            ->send();

    }

}

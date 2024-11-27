<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { padding: 20px; }
        .important { color: #ff0000; font-weight: bold; }
        .credentials { background: #f5f5f5; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to <?= Yii::$app->name ?></h2>
        
        <p>Hello <?= $companyName ?>,</p>
        
        <p>Your account has been created successfully. Here are your login credentials:</p>
        
        <div class="credentials">
            <p><strong>Login Email:</strong> <?= $email ?></p>
            <p><strong>Password:</strong> <?= $password ?></p>
        </div>

        <p class="important">Please verify your email by clicking the link below:</p>
        <p><a href="<?= $verificationLink ?>"><?= $verificationLink ?></a></p>

        <p class="important">After verification, please change your password immediately after logging in.</p>

        <p>You can login at: <a href="<?= $loginUrl ?>"><?= $loginUrl ?></a></p>

        <p>If you have any questions or issues, please contact our support team.</p>

        <p>Best regards,<br>
        <?= Yii::$app->name ?> Team</p>
    </div>
</body>
</html> 
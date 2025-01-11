<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { padding: 20px; }
        .important { color: #ff0000; font-weight: bold; }
        .credentials { background: #f5f5f5; padding: 15px; margin: 15px 0; }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        .link-box {
            word-break: break-all;
            background: #f9f9f9;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to <?= Yii::$app->name ?></h2>
        
        <p>Dear <?= $name ?>,</p>
        
        <p>Your account has been created for <?= $company ?>. Here are your temporary login credentials:</p>
        
        <div class="credentials">
            <p><strong>Username:</strong> <?= $username ?></p>
            <p><strong>Temporary Password:</strong> <?= $password ?></p>
            <p><strong>Role:</strong> <?= $role ?></p>
            <?php if (!empty($modules)): ?>
            <p><strong>Assigned Modules:</strong> <?= $modules ?></p>
            <?php endif; ?>
        </div>

        <p>You can either:</p>
        

        <p>
            <a href="<?= $setupLink ?>" class="button">Set Your New Password</a>
        </p>

        <p>Or copy and paste this link in your browser:</p>
        <div class="link-box">
            <?= $setupLink ?>
        </div>

        <p class="important">Note: For security reasons, please change your password after first login.</p>

        <p>If you have any questions, please contact your system administrator.</p>

        <p>Best regards,<br>
        <?= Yii::$app->name ?> Team</p>
    </div>
</body>
</html>

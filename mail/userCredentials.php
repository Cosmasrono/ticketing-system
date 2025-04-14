<?php
/* @var $this yii\web\View */
/* @var $userName string */
/* @var $email string */
/* @var $password string */
/* @var $companyName string */

?>
<p>Hello, <?= htmlspecialchars($userName) ?>,</p>

<p>Your account has been created. Here are your login credentials:</p>
<ul>
    <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
    <li><strong>Password:</strong> <?= htmlspecialchars($password) ?></li>
</ul>

<p>Please change your password after first login.</p>

<p>Regards,<br><?= Yii::$app->name ?></p>

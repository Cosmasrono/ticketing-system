<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PasswordResetRequestForm $model */

$this->title = 'Reset Password';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-logo">
            <a href="<?= Yii::$app->homeUrl ?>">
                <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Iansoft Logo" style="max-width: 90px;">
            </a>
        </div>

        <h2 class="auth-title">Forgot your password?</h2>
        <p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="auth-alert auth-alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
            <div class="text-center mt-3">
                <?= Html::a('← Back to login', ['site/login'], ['class' => 'auth-back-link']) ?>
            </div>

        <?php elseif (Yii::$app->session->hasFlash('error')): ?>
            <div class="auth-alert auth-alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
            <?= $form->field($model, 'company_email', ['options' => ['class' => 'auth-field']])
                ->textInput(['autofocus' => true, 'placeholder' => 'you@company.com', 'class' => 'auth-input'])
                ->label('Email Address') ?>
            <div class="auth-actions">
                <?= Html::submitButton('Send Reset Link', ['class' => 'auth-btn']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="text-center mt-3">
                <?= Html::a('← Back to login', ['site/login'], ['class' => 'auth-back-link']) ?>
            </div>

        <?php else: ?>
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
            <?= $form->field($model, 'company_email', ['options' => ['class' => 'auth-field']])
                ->textInput(['autofocus' => true, 'placeholder' => 'you@company.com', 'class' => 'auth-input'])
                ->label('Email Address') ?>
            <div class="auth-actions">
                <?= Html::submitButton('Send Reset Link', ['class' => 'auth-btn']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="text-center mt-3">
                <?= Html::a('← Back to login', ['site/login'], ['class' => 'auth-back-link']) ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $this->registerCss(<<<CSS
.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1C1C4E 0%, #2d2d6e 50%, #E85720 100%);
    margin-top: -70px;
    padding: 20px;
}

.auth-card {
    background: #fff;
    border-radius: 4px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.auth-logo {
    text-align: center;
    margin-bottom: 24px;
}

.auth-title {
    font-size: 22px;
    font-weight: 700;
    color: #1C1C4E;
    text-align: center;
    margin-bottom: 8px;
}

.auth-subtitle {
    font-size: 14px;
    color: #6b7280;
    text-align: center;
    margin-bottom: 28px;
}

.auth-field {
    margin-bottom: 20px;
}

.auth-field label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}

.auth-input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1.5px solid #d1d5db;
    border-radius: 4px;
    font-size: 15px;
    transition: border-color 0.2s;
    outline: none;
}

.auth-input:focus {
    border-color: #E85720;
    box-shadow: 0 0 0 3px rgba(232, 87, 32, 0.1);
}

.auth-actions {
    margin-top: 8px;
}

.auth-btn {
    width: 100%;
    height: 46px;
    background: #E85720;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.auth-btn:hover {
    background: #d04d1c;
    transform: translateY(-1px);
}

.auth-btn:active {
    transform: translateY(0);
}

.auth-back-link {
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
}

.auth-back-link:hover {
    color: #E85720;
}

.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 20px;
}

.auth-alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.auth-alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    margin-bottom: 20px;
}

.has-error .auth-input {
    border-color: #ef4444;
}

.help-block {
    font-size: 12px;
    color: #ef4444;
    margin-top: 4px;
}
CSS); ?>
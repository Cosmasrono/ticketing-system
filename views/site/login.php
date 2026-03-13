<?php
/**
 * UPDATED LOGIN VIEW - views/site/login.php
 * 
 * Key improvements:
 * 1. Properly displays success flash messages from password reset
 * 2. Shows error messages
 * 3. Auto-dismisses or allows manual dismiss
 */
?>

<?php use yii\helpers\Html; ?>
<?php use yii\bootstrap5\ActiveForm; ?>

<?php $this->title = 'Login'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="<?= Yii::$app->homeUrl ?>">
                <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Iansoft Logo" style="max-width: 90px;">
            </a>
        </div>

        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your account to continue</p>

        <!-- ✅ IMPROVED: Success Flash Message -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div id="success-alert" class="auth-alert auth-alert-success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <div>
                    <strong>Success!</strong>
                    <p style="margin: 0; margin-top: 4px;"><?= Yii::$app->session->getFlash('success') ?></p>
                </div>
                <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('success-alert').style.display='none';"></button>
            </div>
            <script>
                // Auto-dismiss after 5 seconds (optional)
                setTimeout(function() {
                    const alert = document.getElementById('success-alert');
                    if (alert) {
                        alert.style.display = 'none';
                    }
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- ✅ IMPROVED: Error Flash Message -->
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div id="error-alert" class="auth-alert auth-alert-danger" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <div>
                    <strong>Error</strong>
                    <p style="margin: 0; margin-top: 4px;"><?= Yii::$app->session->getFlash('error') ?></p>
                </div>
                <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('error-alert').style.display='none';"></button>
            </div>
        <?php endif; ?>

        <!-- ✅ IMPROVED: Warning Flash Message -->
        <?php if (Yii::$app->session->hasFlash('warning')): ?>
            <div id="warning-alert" class="auth-alert auth-alert-warning" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.7a1.13 1.13 0 0 0 .98 1.734h11.396a1.13 1.13 0 0 0 .98-1.734L8.982 1.566zM8 5a.905.905 0 0 1 .9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <div>
                    <strong>Warning</strong>
                    <p style="margin: 0; margin-top: 4px;"><?= Yii::$app->session->getFlash('warning') ?></p>
                </div>
                <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('warning-alert').style.display='none';"></button>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        
        <?= $form->field($model, 'company_email', ['options' => ['class' => 'auth-field']])
            ->textInput(['autofocus' => true, 'placeholder' => 'you@company.com', 'class' => 'auth-input'])
            ->label('Email Address') ?>

        <?= $form->field($model, 'password', ['options' => ['class' => 'auth-field']])
            ->passwordInput(['placeholder' => 'Enter your password', 'class' => 'auth-input'])
            ->label('Password') ?>

        <div class="auth-actions">
            <?= Html::submitButton('Sign In', ['class' => 'auth-btn']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="auth-footer">
            <p>Don't have an account? <?= Html::a('Sign up', ['site/signup'], ['class' => 'auth-link']) ?></p>
            <p><?= Html::a('Forgot password?', ['site/request-password-reset'], ['class' => 'auth-link']) ?></p>
        </div>
    </div>
</div>

<?php $this->registerCss(<<<CSS
.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 20px;
    border-left: 4px solid;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-alert-success {
    background: #f0fdf4;
    color: #166534;
    border-left-color: #22c55e;
}

.auth-alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border-left-color: #ef4444;
}

.auth-alert-warning {
    background: #fffbeb;
    color: #92400e;
    border-left-color: #f59e0b;
}

.auth-alert strong {
    display: block;
    font-weight: 600;
    margin-bottom: 2px;
}

.auth-alert .btn-close {
    margin-left: auto;
    flex-shrink: 0;
}

.auth-footer {
    margin-top: 24px;
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.auth-footer p {
    font-size: 13px;
    margin-bottom: 8px;
}

.auth-footer p:last-child {
    margin-bottom: 0;
}

.auth-link {
    color: #E85720;
    text-decoration: none;
    font-weight: 500;
}

.auth-link:hover {
    text-decoration: underline;
}
CSS); ?>
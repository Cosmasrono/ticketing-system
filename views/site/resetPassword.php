<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ResetPasswordForm $model */

$this->title = 'Set New Password';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-logo">
            <a href="<?= Yii::$app->homeUrl ?>">
                <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Iansoft" style="max-width: 90px;">
            </a>
        </div>

        <h2 class="auth-title">Set New Password</h2>
        <p class="auth-subtitle">Choose a strong password for your account.</p>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="auth-alert auth-alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

        <div class="auth-field">
            <label>New Password</label>
            <div class="password-wrap">
                <?= $form->field($model, 'password', ['options' => ['class' => ''], 'template' => '{input}{error}'])
                    ->passwordInput(['class' => 'auth-input', 'placeholder' => 'Enter new password', 'autofocus' => true, 'id' => 'new-password']) ?>
                <button type="button" class="toggle-pw" onclick="togglePassword('new-password', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="auth-field">
            <label>Confirm Password</label>
            <div class="password-wrap">
                <?= $form->field($model, 'confirmPassword', ['options' => ['class' => ''], 'template' => '{input}{error}'])
                    ->passwordInput(['class' => 'auth-input', 'placeholder' => 'Confirm new password', 'id' => 'confirm-password']) ?>
                <button type="button" class="toggle-pw" onclick="togglePassword('confirm-password', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="auth-actions">
            <?= Html::submitButton('Save New Password', ['class' => 'auth-btn']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="text-center mt-3">
            <?= Html::a('← Back to login', ['site/login'], ['class' => 'auth-back-link']) ?>
        </div>

    </div>
</div>

<?php $this->registerJs(<<<JS
function togglePassword(id, btn) {
    var input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.style.opacity = '1';
    } else {
        input.type = 'password';
        btn.style.opacity = '0.5';
    }
}
JS); ?>

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

.auth-logo { text-align: center; margin-bottom: 24px; }

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

.auth-field > label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}

.password-wrap {
    position: relative;
}

.password-wrap .auth-input {
    padding-right: 44px;
}

.toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    opacity: 0.5;
    padding: 0;
    line-height: 1;
}

.toggle-pw:hover { opacity: 1; color: #E85720; }

.auth-input {
    display: block;
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

.auth-actions { margin-top: 8px; }

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

.auth-btn:hover { background: #d04d1c; transform: translateY(-1px); }
.auth-btn:active { transform: translateY(0); }

.auth-back-link { font-size: 13px; color: #6b7280; text-decoration: none; }
.auth-back-link:hover { color: #E85720; }

.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 20px;
}

.auth-alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.has-error .auth-input { border-color: #ef4444; }
.help-block { font-size: 12px; color: #ef4444; margin-top: 4px; }
CSS); ?>
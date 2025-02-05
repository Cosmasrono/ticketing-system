<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Alert;

$this->title = 'Super Admin Registration';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup">
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="card-body">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success">
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        This registration is only for authorized super administrators.
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <?= $form->field($model, 'name')->textInput([
                        'placeholder' => 'Enter Full Name',
                        'autocomplete' => 'off'
                    ]) ?>

                    <?= $form->field($model, 'company_name')->textInput([
                        'placeholder' => 'Enter Company Name',
                        'autocomplete' => 'off'
                    ]) ?>

                    <?= $form->field($model, 'company_email')->textInput([
                        'placeholder' => 'Enter Company Email',
                        'autocomplete' => 'off'
                    ])->hint('Only authorized email addresses are allowed.') ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Enter Password'
                    ])->hint('Password must be at least 6 characters long.') ?>

                    <?= $form->field($model, 'company_type')->hiddenInput(['value' => 'Admin'])->label(false) ?>
                    <?= $form->field($model, 'subscription_level')->hiddenInput(['value' => 'Enterprise'])->label(false) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Register', ['class' => 'btn btn-primary btn-lg', 'name' => 'signup-button']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="text-center mt-3">
                <p>Already have an account? <?= Html::a('Login here', ['site/login']) ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCss("
    .site-signup {
        padding: 20px 0;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .alert {
        margin-bottom: 20px;
    }
");
?> 
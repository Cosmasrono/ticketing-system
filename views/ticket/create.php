<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Create Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Tickets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ticket-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="ticket-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>

        <?php ActiveForm::end(); ?>

    </div>

</div>

<style>
    /* Orange-themed Login, Signup, Dashboard, and Ticket Creation Styles */
body {
    background-color: #FFF3E0;
    color: #E65100;
}

.site-login, .site-signup, .site-index, .ticket-create {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #FFF;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1, h2 {
    color: #FF9800;
    text-align: center;
    margin-bottom: 20px;
}

p {
    color: #F57C00;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-color: #FFB74D;
}

.form-control:focus {
    border-color: #FF9800;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
}

.btn-primary, .btn-success {
    background-color: #FF9800;
    border-color: #FF9800;
    color: #FFF;
}

.btn-secondary {
    background-color: #FFA726;
    border-color: #FFA726;
    color: #FFF;
}

.btn-primary:hover, .btn-primary:focus,
.btn-secondary:hover, .btn-secondary:focus,
.btn-success:hover, .btn-success:focus {
    background-color: #F57C00;
    border-color: #F57C00;
}

a {
    color: #FF5722;
}

a:hover {
    color: #E64A19;
}

/* Ticket creation form specific styles */
.ticket-form {
    background-color: #FFF3E0;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.ticket-form .form-group {
    margin-bottom: 20px;
}

.ticket-form label {
    color: #F57C00;
    font-weight: bold;
}

.ticket-form .btn-success {
    width: 100%;
    padding: 10px;
    font-size: 18px;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .site-login, .site-signup, .site-index, .ticket-create {
        max-width: 100%;
        margin: 0 15px;
    }
    
    .btn-lg, .ticket-form .btn-success {
        width: 100%;
        margin: 0.5rem 0;
    }
}

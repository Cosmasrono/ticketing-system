<?php
use yii\helpers\Html;

$this->title = 'Dashboard';
?>

<div class="site-index">
    <!-- Main Content -->
    <div class="jumbotron text-center">
        <h1 class="display-4">Welcome to Iansoft Dashboard!</h1>
        <p class="lead">This is your application dashboard where you can manage your tickets.</p>
        <?= Html::a('Create Ticket', ['ticket/create'], ['class' => 'btn btn-primary btn-lg']) ?>
        <?= Html::a('View Tickets', ['ticket/view'], ['class' => 'btn btn-secondary btn-lg']) ?>
        <?= Html::a('Admin Tickets', ['site/admin'], ['class' => 'btn btn-secondary btn-lg']) ?>
        <!-- developer tickets -->
        <?= Html::a('Developer Tickets', ['developer/view'], ['class' => 'btn btn-secondary btn-lg']) ?>
    </div>

    <div class="body-content text-center">
        <h2>Overview of Iansoft</h2>
        <p>Iansoft is a leading software solutions provider specializing in creating efficient, reliable, and user-friendly applications. Our products are designed to meet the needs of a diverse range of users, from individuals to large organizations.</p>
        <p>With a focus on innovation and customer satisfaction, Iansoft continuously strives to improve its services and expand its offerings to meet the evolving demands of the tech industry.</p>
        
        <p>Here you can see an overview of your tickets, manage them, and create new ones.</p>
        
        <!-- Add any other content you'd like here -->
    </div>
</div>

<?php
$this->registerCss("
    .navbar-nav .nav-item .nav-link {
        padding: 0.5rem 1rem;
        margin: 0 0.5rem;
    }
    .jumbotron {
        margin-bottom: 2rem;
    }
    .body-content {
        margin-top: 2rem;
    }
    .btn-lg {
        margin-top: 1rem;
    }
");
?>
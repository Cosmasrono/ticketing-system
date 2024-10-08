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


<style>
/* Orange-themed Login, Signup, and Dashboard Styles */
body {
    background-color: #FFF3E0;
    color: #E65100;
}

.site-login, .site-signup, .site-index {
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

.btn-primary {
    background-color: #FF9800;
    border-color: #FF9800;
}

.btn-secondary {
    background-color: #FFA726;
    border-color: #FFA726;
    color: #FFF;
}

.btn-primary:hover, .btn-primary:focus,
.btn-secondary:hover, .btn-secondary:focus {
    background-color: #F57C00;
    border-color: #F57C00;
}

a {
    color: #FF5722;
}

a:hover {
    color: #E64A19;
}

/* Dashboard specific styles */
.jumbotron {
    background-color: #FFF3E0;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 8px;
}

.display-4 {
    color: #FF9800;
    font-weight: bold;
}

.lead {
    color: #F57C00;
}

.btn-lg {
    margin: 0.5rem;
}

.body-content {
    background-color: #FFF;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Navbar styles */
.navbar {
    background-color: #FF9800;
}

.navbar-nav .nav-item .nav-link {
    color: #FFF;
    padding: 0.5rem 1rem;
    margin: 0 0.5rem;
}

.navbar-nav .nav-item .nav-link:hover {
    background-color: #F57C00;
    border-radius: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .site-login, .site-signup, .site-index {
        max-width: 100%;
        margin: 0 15px;
    }
    
    .btn-lg {
        width: 100%;
        margin: 0.5rem 0;
    }
}
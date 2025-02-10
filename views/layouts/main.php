<?php
/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\web\JqueryAsset;
use app\models\User;
 

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
JqueryAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
    /* Navbar styling */
    .navbar {
        background: linear-gradient(to right, #FF8C00, #FF4500) !important; /* Orange gradient */
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        padding: 0.5rem 1rem;
    }

    /* Brand/Logo styling */
    .navbar-brand {
        color: #fff !important;
        font-weight: bold;
        font-size: 1.5rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    /* Navigation links */
    .navbar-nav .nav-link {
        color: rgba(255,255,255,0.9) !important;
        padding: 0.5rem 1rem !important;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #fff !important;
        background-color: rgba(255,255,255,0.1);
        border-radius: 4px;
    }

    /* Active link */
    .navbar-nav .nav-item.active .nav-link {
        color: #fff !important;
        background-color: rgba(255,255,255,0.2);
        border-radius: 4px;
    }

    /* Logout button */
    .btn-link.logout {
        color: rgba(255,255,255,0.9) !important;
        text-decoration: none;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn-link.logout:hover {
        color: #fff !important;
        background-color: rgba(255,255,255,0.1);
        border-radius: 4px;
    }

    /* Icons styling */
    .fas {
        margin-right: 5px;
    }

    /* Mobile menu button */
    .navbar-toggler {
        border-color: rgba(255,255,255,0.5);
    }

    .navbar-toggler:focus {
        box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
    }

    /* Dropdown menu styling */
    .dropdown-menu {
        background-color: #FF8C00;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .dropdown-item {
        color: rgba(255,255,255,0.9);
    }

    .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
        color: #fff;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .navbar-nav {
            padding: 0.5rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .navbar-collapse {
            background-color: #FF8C00;
            padding: 1rem;
            border-radius: 0 0 4px 4px;
        }
    }

    /* Add some spacing for fixed navbar */
    body {
        padding-top: 60px;
    }

    main {
        margin-top: 20px;
    }

    .navbar-logo {
        max-height: 40px;
        width: auto;
        margin-right: 10px;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
    }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
 
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">
        <nav id="w0" role="navigation">

        <!-- logo  -->
       
            <div class="container">
        
            <?php
NavBar::begin([
    'brandLabel' => Html::img('https://www.iansoftltd.com/assets/img/logo.jpg', ['alt'=>'Logo', 'class'=>'navbar-logo']),
    'brandUrl' => Yii::$app->homeUrl,
    'options' => ['class' => 'navbar-expand-md navbar-dark fixed-top', 'style' => 'background-color: #FF8C00;']
]);

// Initialize menuItems as an empty array
$menuItems = [];

// Check if the user is logged in
if (!Yii::$app->user->isGuest) {
    // User is logged in; display appropriate menu items based on role
    if (Yii::$app->user->identity->role === 'developer') {
        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => '<i class="fas fa-user"></i> Profile',
             'url' => ['/user/profile', 'id' => Yii::$app->user->id],
             'encode' => false],
            ['label' => '<i class="fas fa-code"></i> Developer Dashboard', 
             'url' => ['/developer/view'],
             'encode' => false],
        ];
    } else {
        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
        
            ['label' => '<i class="fas fa-plus-circle"></i> Create Ticket', 
             'url' => ['/ticket/create'],
             'encode' => false],
            ['label' => '<i class="fas fa-list"></i> View Tickets', 
             'url' => ['/ticket/index'],
             'encode' => false],
            ['label' => '<i class="fas fa-cog"></i> Admin Panel', 
             'url' => ['/site/admin'],
             'encode' => false],
            ['label' => '<i class="fas fa-code"></i> Developer Dashboard', 
             'url' => ['/developer/view'],
             'encode' => false],

             ['label' => '<i class="fas fa-user"></i> Profile',
             'url' => ['/user/profile', 'id' => Yii::$app->user->id],
             'encode' => false],
        ];
    }

    // Add logout button for both developer and regular users
    $menuItems[] = '<li>'
        . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
        . Html::submitButton(
            'Logout (' . Yii::$app->user->identity->company_name . ')',
            ['class' => 'btn btn-link logout text-decoration-none']
        )
        . Html::endForm()
        . '</li>';
} else {
    // User is not logged in; show only Home and Login
    $menuItems = [
        ['label' => 'Home', 'url' => ['/site/index']],
        ['label' => 'Login', 'url' => ['/site/login']],
    ];
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => $menuItems,
]);

NavBar::end();
?>

            </div>
        </nav>
        
        <main id="main" class="flex-shrink-0" role="main">
            <div class="container">
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                <?php endif ?>
                <?= Alert::widget() ?>
                <?php foreach (Yii::$app->session->getAllFlashes() as $key => $message): ?>
                    <div class="alert alert-<?= $key ?>"><?= $message ?></div>
                <?php endforeach; ?>
                <?= $content ?>
            </div>
        </main>

        <footer id="footer" class="mt-auto py-3 bg-light">
            <div class="container">
                <div class="row ">
                    <div class="col-md-6 text-center text-md-start">&copy; Iansoft Technologies <?= date('Y') ?></div>
                    <div class="col-md-6 text-center text-md-end"> @nebtech technologies</div>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php $this->endBody() ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
<?php $this->endPage() ?>
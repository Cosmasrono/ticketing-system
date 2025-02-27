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
use app\assets\LandingAsset;
use yii\helpers\Url;

// Register appropriate asset bundle based on user state and current action
if (Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'index') {
    LandingAsset::register($this);
} else {
    //AppAsset::register($this);
    LandingAsset::register($this);
}

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
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* Navbar Styling */

        .navbar {
            background: #1B1D4E !important;
            backdrop-filter: blur(10px);
            padding: 15px 15px;
            transition: all 0.4s ease-in-out;
            
            /* margin-bottom: 20px; */
        }

        .navbar-nav {
            display: flex;
            gap: 18px;
            margin-bottom: 20px;
        }

        /* User Icon Dropdown */
        .nav-item.dropdown .nav-link {
            font-size: 16px;
            color: #F8F9FA !important;
            padding: 4px 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }

        .dropdown-menu {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            padding: 8px;
        }

        .dropdown-item {
            color: #161616;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease-in-out;
        }

        .dropdown-item:hover {
            background-color: rgba(232, 87, 32, 0.1);
            color: #E85720;
        }

        

        /* Index Page Header on Scroll

        .body.scrolled .header {
            --background-color: rgba(40, 58, 90, 0.9);
        }

        .header {
            background-color: #3d4d6a;
        }

        /* Brand Logo Styling */
        .navbar-logo {
            max-height: 50px;
            margin-right: 8px;
            border-radius: 50%
        }

        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(40, 58, 90, 0.95);
                padding: 1rem;
                border-radius: 0 0 10px 10px;
                transition: all 0.5s ease-in-out;
            }
        }

        body {
            padding-top: 70px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body class="guest-index-page <?= Yii::$app->user->isGuest && in_array(Yii::$app->controller->action->id, ['login', 'request-password-reset', 'super-admin-signup']) ? 'guest-index-page' : '' ?>">

    <?php if (Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'index'): ?>
        <!-- Guest Landing Page Header -->
        <header id="guest-header" class="guest-header d-flex align-items-center fixed-top p-10">
            <div class="container-fluid d-flex">
                <a href="<?= Yii::$app->homeUrl ?>" class="logo d-flex align-items-center">
                    <img src="https://www.iansoftltd.com/assets/img/logo.jpg" alt="Iansoft Logo">
                </a>
            </div>
            <div class="container-fluid container-xl d-flex align-items-center justify-content-end">
                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li><a href="#hero" class="active">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#enterprisesol">Enterprise Solutions</a></li>
                    </ul>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>
                <div><a class="btn-getstarted" href="/site/login">Login</a></div>
            </div>
        </header>
    <?php elseif (Yii::$app->user->isGuest && in_array(Yii::$app->controller->action->id, ['login', 'request-password-reset', 'super-admin-signup'])): ?>
        <!-- No header displayed for login, register, and superadmin registration -->
    <?php else: ?>
        <!-- Regular Header for Authenticated Users -->
        <header id="header">
            <?php
            NavBar::begin([
                'brandLabel' => Html::img('https://www.iansoftltd.com/assets/img/logo.jpg', ['alt' => 'Logo', 'class' => 'navbar-logo']),
                'brandUrl' => Yii::$app->homeUrl,
                'options' => ['class' => 'navbar-expand-md navbar-dark  fixed-top']
            ]);

            $menuItems = [
                ['label' => 'Home', 'url' => ['/site/index']],
                [
                    'label' => '<i class="fas fa-plus-circle"></i> Create Ticket',
                    'url' => ['/ticket/create'],
                    'encode' => false
                ],
                [
                    'label' => '<i class="fas fa-list"></i> View Tickets',
                    'url' => ['/ticket/index'],
                    'encode' => false
                ],
                [
                    'label' => '<i class="fas fa-cog"></i> Admin Panel',
                    'url' => ['/site/admin'],
                    'encode' => false
                ],
                [
                    'label' => '<i class="fas fa-code"></i> Developer Dashboard',
                    'url' => ['/developer/view'],
                    'encode' => false
                ]
            ];

            if (Yii::$app->user->isGuest) {
                // $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => '<i class="fas fa-user"></i> ' . Html::encode(Yii::$app->user->identity->company_name),
                    'encode' => false,
                    'options' => ['class' => 'nav-item dropdown'],
                    'url' => '#',
                    'linkOptions' => [
                        'class' => 'nav-link dropdown-toggle',
                        'data-bs-toggle' => 'dropdown',
                        'aria-expanded' => 'false'
                    ],
                    'items' => [
                        ['label' => 'Profile', 'url' => ['/user/profile', 'id' => Yii::$app->user->id]],
                        ['label' => 'Logout', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']]
                    ]
                ];
            }

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav ms-auto mb-2 mb-md-0'],
                'items' => $menuItems
            ]);

            NavBar::end();
            ?>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </header>
    <?php endif; ?>

    <main id="main" class="flex-shrink-0" role="main">
        <?php if (!(Yii::$app->user->isGuest) && Yii::$app->controller->action->id !== 'index'): ?>
            <div class="container">
                <!--?= Alert::widget() ?> -->
                <?= $content ?>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
    </main>

    <?php if (!(Yii::$app->user->isGuest && in_array(Yii::$app->controller->action->id, ['login', 'request-password-reset', 'super-admin-signup']))): ?>
        <footer id="finefooter" class="finefooter">
            <div class="container footer-bottom">
                <div class="row gy-4">
                    <div class="col-lg-5 col-md-5 footer-about">
                        <a href="<?= Yii::$app->homeUrl ?>" class="d-flex align-items-center">
                            <h3 class="sitename">Iansoft Technologies</h3>
                        </a>
                        <div class="footer-contact pt-3">
                            <p>Nachu Plaza, 10th Floor</p>
                            <p>Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2">
                        <!-- Spacer column -->
                    </div>
                    <div class="col-lg-5 col-md-5 text-lg-end">
                        <h4>Connect with Us</h4>
                        <div class="social-links d-flex justify-content-lg-end">
                            <a href=""><i class="bi bi-twitter"></i></a>
                            <a href=""><i class="bi bi-facebook"></i></a>
                            <a href=""><i class="bi bi-whatsapp"></i></a>
                            <a href=""><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container copyright text-center mt-4">
                <p>© <span>Iansoft</span> <strong class="px-1 sitename">Technologies</strong> <span><?= date('Y') ?></span></p>
            </div>
        </footer>
    <?php endif; ?>


    <!-- Scroll Top Button -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Preloader -->
    <!-- <div id="preloader"></div> -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
    <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php

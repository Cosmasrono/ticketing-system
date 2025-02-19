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
    AppAsset::register($this);
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
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* Navbar styling */
        .navbar {
            background: #37517e !important;
            /* Orange gradient */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            padding: 0.5rem 1rem;
        }

        /* Brand/Logo styling */
        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
            font-size: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Navigation links */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Active link */
        .navbar-nav .nav-item.active .nav-link {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* Logout button */
        .btn-link.logout {
            color: rgba(255, 255, 255, 0.9) !important;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-link.logout:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Icons styling */
        .fas {
            margin-right: 5px;
        }

        /* Mobile menu button */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        /* Dropdown menu styling */
        .dropdown-menu {
            background-color: #FF8C00;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .dropdown-item {
            color: rgba(255, 255, 255, 0.9);
        }

        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
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

<?php if (Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'index'): ?>
    <!-- Guest Landing Page Header -->

    <body class="guest-index-page">
    <header id="guest-header" class="guest-header d-flex align-items-center fixed-top">
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

                <a class="btn-getstarted" href="/site/login">Login</a>
            </div>
        </header>


    <?php else: ?>
        <!-- Regular Header for Authenticated Users -->
        <header id="header">
            <?php
            NavBar::begin([
                'brandLabel' => Html::img('https://www.iansoftltd.com/assets/img/logo.jpg', ['alt' => 'Logo', 'class' => 'navbar-logo']),
                'brandUrl' => Yii::$app->homeUrl,
                'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
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
                ],
                [
                    'label' => '<i class="fas fa-user"></i> Profile',
                    'url' => ['/user/profile', 'id' => Yii::$app->user->id],
                    'encode' => false
                ],
            ];

            if (Yii::$app->user->isGuest) {
                // $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => 'Logout (' . Yii::$app->user->identity->company_name . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
            }

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav ms-auto mb-2 mb-md-0'],
                'items' => $menuItems
            ]);


            NavBar::end();
            ?>
        </header>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    <?php endif; ?>

    <main id="main" class="flex-shrink-0" role="main">
        <?php if (!(Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'index')): ?>

              <!--?= $content ?--> 
            <div class="container">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div> 

        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
    </main>

    <?php if (Yii::$app->user->isGuest && Yii::$app->controller->action->id === 'index'): ?>
        <!-- Guest Landing Page Footer -->
        <footer id="footer" class="footer">
            <div class="container footer-top">
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

        <!-- Scroll Top Button -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-up-short"></i>
        </a>
        <!-- Preloader -->
        <div id="preloader"></div>
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/vendor/php-email-form/validate.js"></script>
        <script src="assets/vendor/aos/aos.js"></script>
        <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
        <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
        <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
        <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
        <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <?php else: ?>
        <!-- Regular Footer for Authenticated Users -->
        <footer id="footer" class="footer">
            <div class="container footer-top">
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

        <!-- Scroll Top Button -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-up-short"></i>
        </a>
        <!-- Preloader -->
        <div id="preloader"></div>
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/vendor/php-email-form/validate.js"></script>
        <script src="assets/vendor/aos/aos.js"></script>
        <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
        <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
        <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
        <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
        <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <?php endif; ?>

    <?php $this->endBody() ?>
    </body>

</html>
<?php $this->endPage() ?>
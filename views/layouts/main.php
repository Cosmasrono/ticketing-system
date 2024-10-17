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

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
$this->registerJsFile('@web/js/jquery.js', ['position' => \yii\web\View::POS_HEAD]);
JqueryAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">
        <nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation">
            <div class="container">
                <?php
                NavBar::begin([
                    // 'brandLabel' => Yii::$app->name,
                    'brandUrl' => Yii::$app->homeUrl,
                    'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
                ]);

                $menuItems = [
                    ['label' => 'Home', 'url' => ['/site/index']],
                ];

                if (!Yii::$app->user->isGuest) {
                    // Show all menu items
                    $menuItems[] = ['label' => 'Admin', 'url' => ['/site/admin']];
                    $menuItems[] = '<li>'
                        . Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                            'Logout (' . Html::encode(Yii::$app->user->identity->company_name) . ')',
                            ['class' => 'btn btn-link logout']
                        )
                        . Html::endForm()
                        . '</li>';
                }

                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav'],
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
                    <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

<?php
use yii\helpers\Html;

$this->title = 'Iansoft Dashboard';
?>

<div class="site-index">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Iansoft Ticketing System</h1>
            <p class="hero-subtitle">Streamline your support process with our efficient ticket management solution</p>
            <?= Html::a('Get Started', ['ticket/create'], ['class' => 'btn btn-primary btn-lg animated-button']) ?>
        </div>
        <div class="hero-image">
            <img src="https://media.istockphoto.com/id/1313917874/vector/project-management-software-come-with-a-ticketing-system-to-manage-to-do-list.jpg?s=612x612&w=0&k=20&c=Yy_cF2GZUPI8ZalIpsA8UrC2jBt7mrqC42XalTaFObQ=" alt="Ticketing System" class="img-fluid">
        </div>
    </div>




    <!-- Features Section -->
    <div class="features-section">
        <h2 class="section-title">Key Features</h2>
        <div class="feature-cards">
            <div class="feature-card">
                <i class="fas fa-ticket-alt feature-icon"></i>
                <h3>Easy Ticket Creation</h3>
                <p>Create and manage tickets effortlessly</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line feature-icon"></i>
                <h3>Real-time Updates</h3>
                <p>Stay informed with instant notifications</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-users feature-icon"></i>
                <h3>Team Collaboration</h3>
                <p>Work together seamlessly on tickets</p>
            </div>
        </div>
    </div>


    <!-- Quick Access Section -->
    <div class="quick-access-section">
        <h2 class="section-title">Quick Access</h2>
         -->
    </div>

    <!-- About Section -->
    <div class="about-section">
        <h2 class="section-title">About Iansoft</h2>
        <p>Iansoft is a leading software solutions provider specializing in creating efficient, reliable, and user-friendly applications. Our products are designed to meet the needs of a diverse range of users, from individuals to large organizations.</p>
        <p>With a focus on innovation and customer satisfaction, Iansoft continuously strives to improve its services and expand its offerings to meet the evolving demands of the tech industry.</p>
    </div>
</div>

<?php
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

$this->registerCss("
    /* General Styles */
    body {
        font-family: 'Roboto', 'Arial', sans-serif;
        background-color: #FFF3E0;
        color: #E65100;
    }

    .site-index {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .section-title {
        color: #FF9800;
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.5em;
    }

    /* Hero Section */
    .hero-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 50px;
        background: linear-gradient(135deg, #FFF, #FFF3E0);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        animation: fadeIn 1s ease-out;
    }

    .hero-content {
        flex: 1;
        padding: 40px;
    }

    .hero-title {
        font-size: 2.5em;
        color: #FF9800;
        margin-bottom: 20px;
        animation: slideInDown 1s ease-out;
    }

    .hero-subtitle {
        font-size: 1.2em;
        color: #F57C00;
        margin-bottom: 30px;
        animation: slideInUp 1s ease-out 0.5s;
        animation-fill-mode: both;
    }

    .hero-image {
        flex: 1;
        padding: 20px;
    }

    .hero-image img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        animation: zoomIn 1s ease-out 1s;
        animation-fill-mode: both;
    }

    /* Features Section */
    .features-section {
        margin-bottom: 50px;
    }

    .feature-cards {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .feature-card {
        flex-basis: calc(33.333% - 20px);
        background-color: #FFF;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
        font-size: 3em;
        color: #FF9800;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.2);
    }

    /* Quick Access Section */
    .quick-access-section {
        margin-bottom: 50px;
    }

    .button-group {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    .animated-button {
        margin: 10px;
        transition: all 0.3s ease;
    }

    .animated-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .animated-button i {
        margin-right: 8px;
    }

    /* About Section */
    .about-section {
        background-color: #FFF;
        border-radius: 10px;
        padding: 40px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Button Styles */
    .btn-primary {
        background-color: #FF9800;
        border-color: #FF9800;
    }

    .btn-primary:hover {
        background-color: #F57C00;
        border-color: #F57C00;
    }

    /* Animations */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .hero-content .btn-primary {
        animation: pulse 2s infinite;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .feature-card:hover .feature-icon {
        animation: rotate 1s linear;
    }

    /* Staggered Animations */
    .feature-card:nth-child(1) { animation-delay: 0.2s; }
    .feature-card:nth-child(2) { animation-delay: 0.4s; }
    .feature-card:nth-child(3) { animation-delay: 0.6s; }

    .animated-button:nth-child(1) { animation-delay: 0.2s; }
    .animated-button:nth-child(2) { animation-delay: 0.4s; }
    .animated-button:nth-child(3) { animation-delay: 0.6s; }
    .animated-button:nth-child(4) { animation-delay: 0.8s; }

    /* Responsive Design */
    @media (max-width: 992px) {
        .feature-card {
            flex-basis: calc(50% - 20px);
        }
    }

    @media (max-width: 768px) {
        .hero-section {
            flex-direction: column;
        }

        .feature-card {
            flex-basis: 100%;
            margin-bottom: 20px;
        }

        .button-group {
            flex-direction: column;
        }

        .animated-button {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .hero-title {
            font-size: 2em;
        }

        .hero-subtitle {
            font-size: 1em;
        }
    }

    /* Professional Card Styling */
    .admin-index .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .admin-index .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }

    .admin-index .card-header {
        font-weight: 600;
        padding: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .admin-index .card-body {
        padding: 1.25rem;
    }

    /* Status Cards Specific Styling */
    .admin-index .bg-primary { background: linear-gradient(45deg, #4e73df, #224abe) !important; }
    .admin-index .bg-success { background: linear-gradient(45deg, #1cc88a, #13855c) !important; }
    .admin-index .bg-danger { background: linear-gradient(45deg, #e74a3b, #be2617) !important; }
    .admin-index .bg-info { background: linear-gradient(45deg, #36b9cc, #258391) !important; }
    .admin-index .bg-warning { background: linear-gradient(45deg, #f6c23e, #dda20a) !important; }
    .admin-index .bg-secondary { background: linear-gradient(45deg, #858796, #60616f) !important; }
    .admin-index .bg-dark { background: linear-gradient(45deg, #5a5c69, #373840) !important; }

    /* GridView Styling */
    .admin-index .grid-view {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        padding: 1rem;
    }

    .admin-index .table {
        margin-bottom: 0;
    }

    .admin-index .table thead th {
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
        color: #4e73df;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 1rem;
    }

    .admin-index .table tbody tr {
        transition: all 0.2s ease;
    }

    .admin-index .table tbody tr:hover {
        background-color: #f8f9fc;
    }

    .admin-index .table td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* Action Buttons Styling */
    .admin-index .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-start;
        align-items: center;
    }

    .admin-index .btn {
        font-weight: 500;
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .admin-index .btn-sm {
        padding: 0.25rem 0.5rem;
    }

    .admin-index .btn-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
        border: none;
    }

    .admin-index .btn-danger {
        background: linear-gradient(45deg, #e74a3b, #be2617);
        border: none;
    }

    .admin-index .btn-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
        border: none;
    }

    .admin-index .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .admin-index .btn.disabled,
    .admin-index .btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Page Title */
    .admin-index h1.text-center {
        color: #2e384d;
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 1rem;
    }

    .admin-index h1.text-center::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: linear-gradient(45deg, #4e73df, #224abe);
        border-radius: 2px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-index .action-buttons {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .admin-index .btn {
            width: 100%;
        }
        
        .admin-index .table td {
            padding: 0.75rem;
        }
    }

    /* Alert Messages */
    .admin-index .alert {
        border: none;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .admin-index .alert-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
        color: white;
    }

    .admin-index .alert-danger {
        background: linear-gradient(45deg, #e74a3b, #be2617);
        color: white;
    }
");

$this->registerJs("
    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function animateOnScroll() {
        var elements = document.querySelectorAll('.section-title, .feature-card, .animated-button, .about-section');
        elements.forEach(function(el) {
            if (isElementInViewport(el) && !el.classList.contains('animate__animated')) {
                el.classList.add('animate__animated', 'animate__fadeInUp');
            }
        });
    }

    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('resize', animateOnScroll);

    // Add hover effect to feature cards
    var featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.feature-icon').style.animation = 'rotate 1s linear';
        });
        card.addEventListener('mouseleave', function() {
            this.querySelector('.feature-icon').style.animation = 'none';
        });
    });

    // Add click animation to buttons
    var buttons = document.querySelectorAll('.animated-button');
    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            this.classList.add('animate__animated', 'animate__rubberBand');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__rubberBand');
            }, 1000);
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^=\"#\"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
");
?>


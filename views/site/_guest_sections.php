<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Iansoft - Help Desk Solutions';
?>
<style>
    .container {
        max-width: 100%;

        /* Allow full width */
        /* Add padding for mobile */
    }

    .container section {
        padding: 70px 40px;
        max-width: 100%;
    }
</style>

<div class="index-container">
    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background" style="padding-left:10px; padding-right:10px;">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                    <h1>IanSoft Smart Help Desk Solution</h1>
                    <p>Transform your support operations with Iansoft Ticketing System</p>
                    <div class="d-flex">
                        <a href="#about" class="btn-get-started">Get Started</a>
                        <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center">
                            <i class="bi bi-play-circle"></i><span>Watch Demo</span>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="200">
                    <img src="<?= Yii::getAlias('@web/assets/img/hero-img.png') ?>" class="img-fluid animated" alt="">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section" style="padding-left:10px; padding-right:10px;">
        <div class="container section-title" data-aos="fade-up">
            <h2>About Us</h2>
        </div>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5 order-2 order-lg-1 why-us-img">
                    <img src="<?= Yii::getAlias('@web/assets/img/tickett.png') ?>" class="img-fluid" alt=""
                        data-aos="zoom-in" data-aos-delay="100" style="width: 400px; height: 300px; object-fit: contain;">
                </div>
                <div class="col-lg-7 content order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100">
                    <p>
                        At Iansoft Smart Help Desk, we empower businesses with cutting-edge support and enterprise solutions.<br>
                        Our intuitive ticketing system ensures:
                    </p>
                    <ul>
                        <li><i class="bi bi-check2-circle"></i> <span>99% customer satisfaction with seamless ticket management</span></li>
                        <li><i class="bi bi-check2-circle"></i> <span>Real-time tracking for instant updates on ticket status</span></li>
                        <li><i class="bi bi-check2-circle"></i> <span>24/7 expert support to keep your operations running smoothly</span></li>
                    </ul>
                    <a href="/" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works Section -->
    <section id="howitworks" class="section why-us light-background" style="padding-left:10px; padding-right:10px;">
        <div class="container-fluid">
            <div class="row gy-4">
                <div class="col-lg-7 d-flex flex-column justify-content-center order-2 order-lg-1">
                    <div class="content px-xl-5" data-aos="fade-up" data-aos-delay="100">
                        <h3><span>How it</span><strong> works</strong></h3>
                    </div>
                    <div class="faq-container px-xl-5" data-aos="fade-up" data-aos-delay="200">
                        <div class="faq-item faq-active">
                            <h3><span>01</span> Submit Ticket</h3>
                            <div class="faq-content">
                                <p>Create and submit your support ticket</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div>

                        <div class="faq-item">
                            <h3><span>02</span> Auto Assignment</h3>
                            <div class="faq-content">
                                <p>Ticket assigned to the right team</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div>

                        <div class="faq-item">
                            <h3><span>03</span> Track Progress</h3>
                            <div class="faq-content">
                                <p>Monitor status in real-time</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div>

                        <div class="faq-item">
                            <h3><span>04</span> Resolution</h3>
                            <div class="faq-content">
                                <p>Get solution and confirmation</p>
                            </div>
                            <i class="faq-toggle bi bi-chevron-right"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 order-1 order-lg-2 why-us-img">
                    <img src="<?= Yii::getAlias('@web/assets/img/why-us.png') ?>" class="img-fluid" alt="" data-aos="zoom-in" data-aos-delay="100">
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="features" class="services section light-background" style="padding-left:10px; padding-right:10px;">
        <div class="container section-title" data-aos="fade-up">
            <h2>Key Features</h2>
            <p>Powerful, intuitive, and designed to enhance your experience with seamless functionality.</p>
        </div>
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-item position-relative">
                        <div class="icon"><i class="bi bi-ticket-detailed"></i></div>
                        <h4><a href="" class="stretched-link">Ticket Management</a></h4>
                        <p>Efficiently organize and track support tickets with our intuitive interface.</p>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-item position-relative">
                        <div class="icon"><i class="bi bi-clock-history"></i></div>
                        <h4><a href="" class="stretched-link">Real-time tracking</a></h4>
                        <p>Monitor ticket status and updates in real-time with our advanced tracking system.</p>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-item position-relative">
                        <div class="icon"><i class="bi bi-bar-chart"></i></div>
                        <h4><a href="" class="stretched-link">Analytics & Reporting</a></h4>
                        <p>Gain insights with comprehensive analytics and customizable reports.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section dark-background" style="padding-left:10px; padding-right:10px;">
        <img src="<?= Yii::getAlias('@web/assets/img/cta-bg.jpg') ?>" alt="">
        <div class="container">
            <div class="row" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-xl-9 text-center text-xl-start">
                    <h3>Ready to transform your business? </h3>
                    <p>Join over <span>500+</span> companies in taking the next step with our powerful solutions. Streamline
                        operations, boost efficiency, and drive growth today.</p>
                </div>
                <div class="col-xl-3 cta-btn-container text-center">
                    <a class="cta-btn align-middle" href="<?= Url::to(['/site/login']) ?>">Get Started</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Enterprise Solution Section -->
    <section id="enterprisesol" class="team section" style="padding-left:10px; padding-right:10px;">
        <div class="container section-title" data-aos="fade-up">
            <h2>Enterprise Solutions</h2>
        </div>
        <div class="container">
            <div class="row gy-4">
                <!-- Microsoft Dynamics -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fab fa-microsoft fa-3x"></i>
                        </div>
                        <h3>Microsoft Dynamics</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Dynamics 365 Business Central</li>
                                <li><i class="fas fa-check-circle"></i> Dynamics 365 Finance</li>
                                <li><i class="fas fa-check-circle"></i> Dynamics 365 Supply Chain</li>
                                <li><i class="fas fa-check-circle"></i> CRM Solutions</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ERP Solutions -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fas fa-cogs fa-3x"></i>
                        </div>
                        <h3>ERP Solutions</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Financial Management</li>
                                <li><i class="fas fa-check-circle"></i> Inventory Management</li>
                                <li><i class="fas fa-check-circle"></i> HR & Payroll</li>
                                <li><i class="fas fa-check-circle"></i> Production Planning</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Custom Software -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fas fa-code fa-3x"></i>
                        </div>
                        <h3>Custom Software</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Bespoke Solutions</li>
                                <li><i class="fas fa-check-circle"></i> Web Applications</li>
                                <li><i class="fas fa-check-circle"></i> Mobile Apps</li>
                                <li><i class="fas fa-check-circle"></i> Integration Services</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Cloud Services -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fas fa-cloud fa-3x"></i>
                        </div>
                        <h3>Cloud Services</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Azure Cloud Solutions</li>
                                <li><i class="fas fa-check-circle"></i> Cloud Migration</li>
                                <li><i class="fas fa-check-circle"></i> Cloud Security</li>
                                <li><i class="fas fa-check-circle"></i> DevOps Services</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Business Intelligence -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fas fa-chart-bar fa-3x"></i>
                        </div>
                        <h3>Business Intelligence</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Data Analytics</li>
                                <li><i class="fas fa-check-circle"></i> Power BI Solutions</li>
                                <li><i class="fas fa-check-circle"></i> Reporting Tools</li>
                                <li><i class="fas fa-check-circle"></i> Data Visualization</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Consulting Services -->
                <div class="col-lg-4 col-md-6">
                    <div class="solution-card" data-tilt>
                        <div class="solution-icon">
                            <i class="fas fa-handshake fa-3x"></i>
                        </div>
                        <h3>Consulting Services</h3>
                        <div class="solution-content">
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> IT Strategy</li>
                                <li><i class="fas fa-check-circle"></i> Digital Transformation</li>
                                <li><i class="fas fa-check-circle"></i> Process Optimization</li>
                                <li><i class="fas fa-check-circle"></i> Technology Advisory</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

</body>

<style>




</style>
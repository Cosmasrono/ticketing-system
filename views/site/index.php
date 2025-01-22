<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Iansoft - Help Desk Solutions';
?>

<!-- Hero Section with Particle Background -->
<div id="particles-js" class="hero-section">
    <div class="overlay"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 animate__animated animate__fadeInLeft">
                <div class="glowing-text">
                    <h1 class="hero-title typewriter">Iansoft Smart Help Desk</h1>
                    <div class="dynamic-text">
                          <span class="txt-rotate" style="color:white" 
                                data-period="2000" 
                                    data-rotate='[ "Intelligent Solutions", "24/7 Support", "Real-time Tracking", "Smart Analytics" ]'>
                        </span>                  

                    </div>
                </div>
                <p class="hero-subtitle">Transform your support operations with our ticketing system</p>
                <div class="interactive-stats">
                    <div class="stat-counter" data-target="99">0%</div>
                    <span>Customer Satisfaction</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image-wrapper animate__animated animate__fadeInRight">
                    <div class="image-container">
                        <img src="https://b2core.com/app/uploads/2024/04/How-to-Create-a-Ticketing-System.png" 
                             alt="Help Desk Dashboard" 
                             class="hero-image parallax-effect">
                        <div class="pulse-effect"></div>
                    </div>
                    <div class="floating-card card-1" data-tilt>
                        <i class="fas fa-ticket-alt"></i>
                        <span>24/7 Support</span>
                    </div>
                    <div class="floating-card card-2" data-tilt>
                        <i class="fas fa-chart-line"></i>
                        <span>Real-time Updates</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Features Section -->
<section id="features" class="features-section">
    <div class="container">
        <h2 class="section-title text-center">Key Features</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card animate__animated animate__fadeInUp">
                    <img src="https://www.givainc.com/images/internal_ticketing_system.png" 
                         alt="Ticket Management" 
                         class="feature-image">
                    <h3>Ticket Management</h3>
                    <p>Efficiently organize and track support tickets with our intuitive interface.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <img src="https://www.uvdesk.com/wp-content/uploads/2023/01/top-3-free-open-source-ticket-system.webp" 
                         alt="Real-time Tracking" 
                         class="feature-image">
                    <h3>Real-time Tracking</h3>
                    <p>Monitor ticket status and updates in real-time with our advanced tracking system.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                    <img src="https://storage.googleapis.com/cdn-website-bolddesk/2022/07/338aa398-automated-ticketing-system-compressed.jpg" 
                         alt="Analytics & Reporting" 
                         class="feature-image">
                    <h3>Analytics & Reporting</h3>
                    <p>Gain insights with comprehensive analytics and customizable reports.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Solutions Section -->
<section class="solutions-section">
    <div class="container">
        <h2 class="section-title text-center glowing-text">Our Enterprise Solutions</h2>
        <div class="row g-4">
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
                        <div class="solution-hover">
                            <p>Complete business management solution for SMEs and Enterprises</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
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
                        <div class="solution-hover">
                            <p>Integrated enterprise resource planning solutions</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
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
                        <div class="solution-hover">
                            <p>Tailored software solutions for your unique needs</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
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
                        <div class="solution-hover">
                            <p>Comprehensive cloud computing solutions</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
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
                        <div class="solution-hover">
                            <p>Transform data into actionable insights</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
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
                        <div class="solution-hover">
                            <p>Expert guidance for your digital journey</p>
                            <a href="#" class="btn btn-light btn-sm">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Statistics Section -->
<section class="statistics bg-light parallax-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="stat-item animate__animated animate__fadeIn" data-tilt>
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt fa-3x text-primary mb-3"></i>
                    </div>
                    <div class="counter-wrapper">
                        <h3 class="counter" data-target="10000">0</h3>
                        <span class="plus">+</span>
                    </div>
                    <p>Tickets Resolved</p>
                    <div class="progress-ring">
                        <svg class="progress-ring__circle" width="120" height="120">
                            <circle class="progress-ring__circle-bg" r="52" cx="60" cy="60"/>
                            <circle class="progress-ring__circle-fg" r="52" cx="60" cy="60"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item animate__animated animate__fadeIn" data-tilt style="animation-delay: 0.8s;">
                    <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                    <div class="counter-wrapper">
                        <h3 class="counter" data-target="30">0</h3>
                        <span class="unit">min</span>
                    </div>
                    <p>Avg Response Time</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item animate__animated animate__fadeIn" style="animation-delay: 1s;">
                    <i class="fas fa-smile fa-3x text-primary mb-3"></i>
                    <h3>98%</h3>
                    <p>Client Satisfaction</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item animate__animated animate__fadeIn" style="animation-delay: 1.2s;">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3>500+</h3>
                    <p>Active Users</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced How It Works Section -->
<section class="how-it-works transform-3d">
    <div class="container">
        <h2 class="section-title text-center glowing-text">How It Works</h2>
        <div class="timeline">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="step-card animate__animated animate__fadeInUp" data-tilt>
                        <div class="step-number">1</div>
                        <div class="step-icon pulse-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h4>Submit Ticket</h4>
                        <p>Create and submit your support ticket</p>
                        <div class="hover-content">
                            <ul class="feature-list">
                                <li>Easy submission</li>
                                <li>File attachments</li>
                                <li>Priority selection</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="step-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;" data-tilt>
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h4>Auto Assignment</h4>
                        <p>Ticket assigned to the right team</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="step-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;" data-tilt>
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h4>Track Progress</h4>
                        <p>Monitor status in real-time</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="step-card animate__animated animate__fadeInUp" style="animation-delay: 0.6s;" data-tilt>
                        <div class="step-number">4</div>
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4>Resolution</h4>
                        <p>Get solution and confirmation</p>
                    </div>
                </div>
            </div>
            <div class="timeline-connector"></div>
        </div>
    </div>
</section>

<!-- Centered CTA Section -->
<section class="cta-section text-center parallax-section d-flex align-items-center justify-content-center">
    <div class="overlay-gradient"></div>
    <div class="particles-background" id="particles-cta"></div>
    <div class="container">
        <div class="cta-wrapper">
            <div class="cta-content">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <div class="content-box">
                            <h2 class="glowing-text mega-title">
                                Ready to Transform Your Business?
                            </h2>
                            <div class="animated-subtitle">
                                <p class="mb-4 animate__animated animate__fadeIn">
                                    Join over <span class="highlight-number">500+</span> businesses 
                                    that trust our enterprise solutions
                                </p>
                            </div>
                            
                            <div class="cta-buttons-wrapper">
                                <div class="cta-buttons d-flex justify-content-center gap-3">
                                    <a href="#" class="btn btn-primary btn-lg pulse-button">
                                        <i class="fas fa-rocket"></i> Get Started
                                        <span class="btn-hover-effect"></span>
                                    </a>
                                    <a href="#" class="btn btn-glass btn-lg">
                                        <i class="fas fa-play"></i> Watch Demo
                                        <span class="btn-hover-effect"></span>
                                    </a>
                                </div>
                            </div>

                            <div class="trust-indicators">
                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        <div class="trust-item" data-aos="fade-up">
                                            <div class="trust-icon">
                                                <i class="fas fa-shield-alt"></i>
                                            </div>
                                            <div class="trust-content">
                                                <span>Enterprise-Grade Security</span>
                                                <small>ISO 27001 Certified</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="trust-item" data-aos="fade-up" data-aos-delay="100">
                                            <div class="trust-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="trust-content">
                                                <span>24/7 Expert Support</span>
                                                <small>Always Available</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="trust-item" data-aos="fade-up" data-aos-delay="200">
                                            <div class="trust-icon">
                                                <i class="fas fa-sync"></i>
                                            </div>
                                            <div class="trust-content">
                                                <span>Regular Updates</span>
                                                <small>Latest Technologies</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="social-media-section text-center">
    <div class="container">
        <h2 class="section-title">Connect with Us</h2>
        <div class="social-icons">
            <a href="https://www.linkedin.com/search/results/all/?fetchDeterministicClustersOnly=true&heroEntityKey=urn%3Ali%3Aorganization%3A13281915&keywords=iansoft%20technologies%20ltd&origin=RICH_QUERY_SUGGESTION&position=0&searchId=19b5cb35-217b-4e89-aa3c-028b8614b2f1&sid=5Ie&spellCorrectionEnabled=false" target="_blank" class="social-icon">
                <i class="fab fa-linkedin"></i>
            </a>
            <a href="https://www.whatsapp.com" target="_blank" class="social-icon">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://www.facebook.com" target="_blank" class="social-icon">
                <i class="fab fa-facebook"></i>
            </a>
            <a href="https://www.twitter.com" target="_blank" class="social-icon">
                <i class="fab fa-twitter"></i>
            </a>
        </div>
    </div>
</section>

<!-- footer -->
 

<style>


.txt-rotate {
    color: #FF8C00; /* Dark orangish color */
}

:root {
    --primary-color: #ff6b35;
    --secondary-color: #f7882f;
    --accent-color: #ff9a5c;
    --dark-color: #2b2d42;
    --light-color: #f8f9fa;
}

.hero-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    padding: 120px 0;
    margin-top: -24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.overlay {
    position: absolute;
    top: 50%;
    margin-right: 2cm;
    padding-right: 2cm;

    /* z-index:1; */
 }

.hero-image-wrapper {
    position: relative;
    padding: 20px;
}

.hero-image {
    width: 100%;
    height: auto;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: transform 0.5s ease, box-shadow 0.5s ease;
    position: relative;
    z-index: 1;
}

.hero-image:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.3);
}

.floating-card {
    position: absolute;
    background: white;
    padding: 15px 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
    animation: float 3s ease-in-out infinite;
    z-index: 2;
}

.floating-card i {
    color: var(--primary-color);
    font-size: 1.5rem;
}

.floating-card span {
    font-weight: 600;
    font-size: 0.9rem;
}

.card-1 {
    top: 0;
    right: 30px;
    animation-delay: 0.5s;
}

.card-2 {
    bottom: 40px;
    left: 0;
    animation-delay: 1s;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
    100% {
        transform: translateY(0px);
    }
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    animation: fadeInUp 1s ease;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
    animation: fadeInUp 1s ease 0.2s;
}

.hero-buttons {
    animation: fadeInUp 1s ease 0.4s;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.btn-outline-light {
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-outline-light:hover {
    background-color: white;
    color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.hover-effect {
    position: relative;
    overflow: hidden;
}

.hover-effect::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,0.2),
        transparent
    );
    transition: 0.5s;
}

.hover-effect:hover::after {
    left: 100%;
}

@media (max-width: 991px) {
    .hero-section {
        padding: 80px 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-image-wrapper {
        margin-top: 40px;
    }
    
    .floating-card {
        display: none;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-buttons .btn {
        display: block;
        width: 100%;
        margin: 10px 0;
    }
}

/* Add a subtle gradient overlay */
.hero-image::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        45deg,
        rgba(255,107,53,0.1),
        rgba(247,136,47,0.1)
    );
    border-radius: 20px;
    
}

/* Add a glowing effect */
@keyframes glow {
    0% {
        box-shadow: 0 0 20px rgba(255,107,53,0.5);
    }
    50% {
        box-shadow: 0 0 40px rgba(255,107,53,0.8);
    }
    100% {
        box-shadow: 0 0 20px rgba(255,107,53,0.5);
    }
}

.hero-image-wrapper:hover .hero-image {
    animation: glow 2s infinite;
}

.feature-card, .step-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    height: 100%;
    text-align: center;
}

.feature-card:hover, .step-card:hover {
    transform: translateY(-5px);
}

.feature-image, .step-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-weight: bold;
}

.statistics {
    padding: 80px 0;
}

.stat-item {
    padding: 30px;
}

.stat-item i {
    color: var(--primary-color);
}

.stat-item h3 {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 15px 0;
    color: var(--dark-color);
}

.section-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 50px;
    color: var(--dark-color);
}

.cta-section {
    background: var(--light-color);
    padding: 80px 0;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .feature-card, .step-card {
        margin-bottom: 30px;
    }
}

/* Enhanced Hero Section Animations */
.hero-title {
    animation: slideInDown 1s ease-out, glowText 3s infinite;
}

@keyframes glowText {
    0% { text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
    50% { text-shadow: 2px 2px 20px rgba(255,107,53,0.5); }
    100% { text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
}

.hero-subtitle {
    animation: slideInLeft 1s ease-out 0.5s both;
}

.hero-buttons {
    animation: fadeInUp 1s ease-out 1s both;
}

/* Enhanced Feature Card Animations */
.feature-card {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeIn 0.5s ease-out;
}

.feature-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 30px rgba(255,107,53,0.2);
}

.feature-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent, rgba(255,107,53,0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.feature-card:hover::after {
    transform: translateX(100%);
}

/* Enhanced Statistics Animation */
.stat-item {
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: scale(1.05);
}

.stat-item i {
    transition: transform 0.3s ease;
}

.stat-item:hover i {
    transform: rotateY(360deg);
    transition: transform 0.8s ease;
}

.stat-item h3 {
    animation: countUp 2s ease-out;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced Step Card Animations */
.step-card {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-card:hover {
    transform: translateY(-15px) scale(1.03);
    box-shadow: 0 20px 40px rgba(255,107,53,0.15);
}

.step-icon i {
    transition: all 0.5s ease;
}

.step-card:hover .step-icon i {
    transform: scale(1.2) rotate(360deg);
    color: var(--secondary-color);
}

.step-number {
    animation: bounce 1s ease infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* Enhanced Floating Cards */
.floating-card {
    animation: floatCard 4s ease-in-out infinite;
}

@keyframes floatCard {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(2deg); }
}

/* Enhanced CTA Section */
.cta-section {
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,107,53,0.05), rgba(247,136,47,0.05));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.6s ease-out;
}

.cta-section:hover::before {
    transform: scaleX(1);
}

/* Scroll Reveal Animation */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
}

.reveal.active {
    opacity: 1;
    transform: translateY(0);
}

/* Smooth Scroll Behavior */
html {
    scroll-behavior: smooth;
}

/* Enhanced Button Animations */
.btn {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background: rgba(255,255,255,0.1);
    transform: translate(-50%, -50%) rotate(45deg) scale(0);
    transition: transform 0.6s ease;
}

.btn:hover::before {
    transform: translate(-50%, -50%) rotate(45deg) scale(1);
}

/* Enhanced Image Hover Effects */
.feature-image {
    transition: all 0.5s ease;
}

.feature-card:hover .feature-image {
    transform: scale(1.05);
    filter: brightness(1.1);
}

/* Interactive Stats Counter */
.interactive-stats {
    margin-top: 2rem;
    font-size: 2rem;
    font-weight: bold;
}

.stat-counter {
    display: inline-block;
    color: var(--accent-color);
    font-size: 3rem;
    margin-right: 1rem;
}

/* Typewriter Effect */
.typewriter {
    overflow: hidden;
    border-right: .15em solid var(--accent-color);
    white-space: nowrap;
    animation: typing 3.5s steps(40, end),
               blink-caret .75s step-end infinite;
}

@keyframes typing {
    from { width: 0 }
    to { width: 100% }
}

@keyframes blink-caret {
    from, to { border-color: transparent }
    50% { border-color: var(--accent-color) }
}

/* Interactive Feature Cards */
.card-flip {
    perspective: 1000px;
    position: relative;
    height: 100%;
}

.feature-card.interactive {
    transition: transform 0.6s;
    transform-style: preserve-3d;
    cursor: pointer;
}

.card-front, .card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    padding: 2rem;
}

.card-back {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.feature-card.interactive:hover {
    transform: rotateY(180deg);
}

/* Parallax Effect */
.hero-section {
    background-attachment: fixed;
    position: relative;
}

/* Interactive Progress Bars */
.progress-wrapper {
    margin: 1rem 0;
}

.progress {
    height: 10px;
    border-radius: 5px;
    background: rgba(255,255,255,0.2);
}

.progress-bar {
    width: 0;
    transition: width 1.5s ease-in-out;
}

/* Floating Elements */
.floating {
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translate(0, 0px); }
    50% { transform: translate(0, 15px); }
    100% { transform: translate(0, -0px); }
}

 
    

    /* developer,admin closng th ticket */
    /* comments and feedback */
    /* ticjet automation */
    /* cliant creatinn */
    /* profile  */
    /* developer collaboration */
    /* data driven */

    /* filtering  */

    /* reports  */
/* Glowing Text Effect */
.glowing-text {
    text-shadow: 0 0 10px rgba(255,255,255,0.8),
                 0 0 20px rgba(255,107,53,0.8),
                 0 0 30px rgba(247,136,47,0.8);
    animation: glow 3s ease-in-out infinite alternate;
}

/* Dynamic Text Animation */
.dynamic-text {
    font-size: 2rem;
    color: var(--accent-color);
    margin: 1rem 0;
}

/* Pulse Effect */
.pulse-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    border-radius: 20px;
    border: 3px solid var(--primary-color);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0;
    }
}

/* Parallax Effect */
.parallax-effect {
    transition: transform 0.5s ease-out;
    will-change: transform;
}

/* Enhanced Card Hover Effects */
.feature-card {
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 107, 53, 0.2),
        transparent
    );
    transform: translateX(-100%);
    transition: 0.6s;
}

.feature-card:hover::before {
    transform: translateX(100%);
}

/* New 3D Transform Effect */
.transform-3d {
    transform-style: preserve-3d;
    perspective: 1000px;
}

.transform-3d:hover {
    transform: rotateY(10deg) rotateX(5deg);
}

/* Smooth Scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: var(--light-color);
}

::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 5px;
}

/* Loading Animation */
.loading-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loader {
    width: 50px;
    height: 50px;
    border: 5px solid #fff;
    border-bottom-color: transparent;
    border-radius: 50%;
    animation: rotate 1s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Timeline Connector */
.timeline-connector {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    z-index: 0;
    transform: translateY(-50%);
}

/* Enhanced Step Cards */
.step-card {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.5s ease;
}

.step-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.pulse-icon {
    position: relative;
}

.pulse-icon::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(255,107,53,0.2);
    transform: translate(-50%, -50%);
    animation: pulse 2s infinite;
}

/* Trust Indicators */
.trust-indicators {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255,255,255,0.9);
}

/* Progress Ring Animation */
.progress-ring__circle {
    transition: stroke-dashoffset 0.35s;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}

.progress-ring__circle-bg {
    stroke: rgba(255,107,53,0.2);
    fill: none;
    stroke-width: 4;
}

.progress-ring__circle-fg {
    stroke: var(--primary-color);
    fill: none;
    stroke-width: 4;
    stroke-dasharray: 326.726;
    stroke-dashoffset: 326.726;
}

/* Parallax Section */
.parallax-section {
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

/* Counter Animation */
.counter-wrapper {
    position: relative;
    display: inline-flex;
    align-items: baseline;
}

.plus, .unit {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-left: 0.25rem;
}

/* Hover Content */
.hover-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,107,53,0.95);
    color: white;
    padding: 2rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-card:hover .hover-content {
    opacity: 1;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .timeline-connector {
        display: none;
    }
    
    .trust-indicators {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
}

.solutions-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.solution-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.solution-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.solution-icon {
    color: var(--primary-color);
    margin-bottom: 20px;
    transition: transform 0.3s ease;
}

.solution-card:hover .solution-icon {
    transform: scale(1.1);
}

.solution-content {
    margin-top: 20px;
}

.solution-content .feature-list {
    list-style: none;
    padding: 0;
}

.solution-content .feature-list li {
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.solution-content .feature-list i {
    color: var(--primary-color);
}

.solution-hover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.solution-card:hover .solution-hover {
    opacity: 1;
}

.solution-hover p {
    margin-bottom: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .solution-card {
        margin-bottom: 30px;
    }
}

.cta-section {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.content-box {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 4rem 2rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.mega-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 2rem;
    background: linear-gradient(45deg, #fff, #f8f9fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 20px rgba(255,255,255,0.3);
}

.cta-buttons {
    margin: 2.5rem 0;
}

.btn {
    min-width: 200px;
    padding: 1rem 2rem;
    border-radius: 50px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
    border: none;
    box-shadow: 0 5px 15px rgba(255,107,53,0.4);
}

.btn-glass {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
}

.trust-indicators {
    margin-top: 3rem;
}

.trust-item {
    background: rgba(255,255,255,0.08);
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1rem 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.trust-item:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.12);
}

.trust-icon i {
    font-size: 2rem;
    color: var(--primary-color);
}

.trust-content {
    text-align: left;
    color: white;
}

.trust-content span {
    display: block;
    font-weight: 600;
    font-size: 1.1rem;
}

.trust-content small {
    display: block;
    opacity: 0.8;
    margin-top: 0.2rem;
}

.highlight-number {
    color: var(--primary-color);
    font-weight: bold;
    font-size: 1.2em;
}

@media (max-width: 768px) {
    .content-box {
        padding: 2rem 1rem;
    }

    .mega-title {
        font-size: 2.5rem;
    }

    .cta-buttons {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        margin: 0.5rem 0;
    }

    .trust-item {
        margin: 0.5rem 0;
    }
}

/* Animation for buttons */
.pulse-button {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255,107,53,0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255,107,53,0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255,107,53,0);
    }
}

/* Social Media Section Styling */
.social-media-section {
    padding: 40px 0;
    background-color: var(--light-color);
}

.social-icons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

.social-icon {
    font-size: 2rem;
    color: var(--primary-color);
    transition: color 0.3s ease, transform 0.3s ease;
}

.social-icon:hover {
    color: var(--secondary-color);
    transform: scale(1.1);
}

.btn.disabled {
    pointer-events: none;
    opacity: 0.65;
    cursor: not-allowed;
}
</style>

<?php
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
?>

<!-- Add this JavaScript at the bottom of your file -->
<script>
// Scroll Reveal Animation
function reveal() {
    var reveals = document.querySelectorAll(".reveal");
    
    reveals.forEach(element => {
        var windowHeight = window.innerHeight;
        var elementTop = element.getBoundingClientRect().top;
        var elementVisible = 150;
        
        if (elementTop < windowHeight - elementVisible) {
            element.classList.add("active");
        }
    });
}

window.addEventListener("scroll", reveal);
reveal(); // Initial check

// Add reveal class to elements
document.querySelectorAll('.feature-card, .stat-item, .step-card').forEach(el => {
    el.classList.add('reveal');
});
</script>

<!-- Add this JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stats Counter Animation
    const counters = document.querySelectorAll('.stat-counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 100;
        
        function updateCount() {
            const count = parseInt(counter.innerText);
            if(count < target) {
                counter.innerText = Math.ceil(count + increment) + '%';
                setTimeout(updateCount, 20);
            }
        }
        
        updateCount();
    });

    // Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    });

    document.querySelectorAll('.feature-card, .stat-item, .step-card').forEach((el) => {
        observer.observe(el);
    });

    // Parallax Effect
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        document.querySelector('.hero-section').style.backgroundPositionY = 
            scrolled * 0.5 + 'px';
    });

    // Interactive Cards
    document.querySelectorAll('.feature-card.interactive').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
});

// Progress Bars Animation
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const target = bar.getAttribute('data-target');
        bar.style.width = target + '%';
    });
}

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

<!-- Add new JavaScript -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanilla-tilt@1.7.0/dist/vanilla-tilt.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Particles.js
    particlesJS('particles-js', {
        particles: {
            number: { value: 80 },
            color: { value: '#ffffff' },
            shape: { type: 'circle' },
            opacity: { value: 0.5 },
            size: { value: 3 },
            move: { enable: true, speed: 6 }
        }
    });

    // Initialize Tilt Effect
    VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
        max: 25,
        speed: 400,
        glare: true,
        "max-glare": 0.5
    });

    // Dynamic Text Rotation
    class TxtRotate {
        constructor(el, toRotate, period) {
            this.toRotate = toRotate;
            this.el = el;
            this.loopNum = 0;
            this.period = parseInt(period, 10) || 2000;
            this.txt = '';
            this.tick();
            this.isDeleting = false;
        }
        tick() {
            let i = this.loopNum % this.toRotate.length;
            let fullTxt = this.toRotate[i];

            if (this.isDeleting) {
                this.txt = fullTxt.substring(0, this.txt.length - 1);
            } else {
                this.txt = fullTxt.substring(0, this.txt.length + 1);
            }

            this.el.innerHTML = '<span class="wrap">'+this.txt+'</span>';

            let that = this;
            let delta = 200 - Math.random() * 100;

            if (this.isDeleting) { delta /= 2; }

            if (!this.isDeleting && this.txt === fullTxt) {
                delta = this.period;
                this.isDeleting = true;
            } else if (this.isDeleting && this.txt === '') {
                this.isDeleting = false;
                this.loopNum++;
                delta = 500;
            }

            setTimeout(function() {
                that.tick();
            }, delta);
        }
    }

    // Initialize Dynamic Text
    let elements = document.getElementsByClassName('txt-rotate');
    for (let i=0; i<elements.length; i++) {
        let toRotate = elements[i].getAttribute('data-rotate');
        let period = elements[i].getAttribute('data-period');
        if (toRotate) {
            new TxtRotate(elements[i], JSON.parse(toRotate), period);
        }
    }

    // Parallax Effect on Scroll
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax-effect');
        
        parallaxElements.forEach(element => {
            const speed = 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
});
</script>

<!-- Add this JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps

        function updateCounter() {
            const current = parseInt(counter.innerText);
            if (current < target) {
                counter.innerText = Math.ceil(current + step);
                setTimeout(updateCounter, 16);
            } else {
                counter.innerText = target;
            }
        }

        updateCounter();
    });

    // Progress Ring Animation
    const circles = document.querySelectorAll('.progress-ring__circle-fg');
    circles.forEach(circle => {
        const radius = circle.r.baseVal.value;
        const circumference = radius * 2 * Math.PI;
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        
        function setProgress(percent) {
            const offset = circumference - (percent / 100 * circumference);
            circle.style.strokeDashoffset = offset;
        }

        // Animate to 75% when in view
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setProgress(75);
                }
            });
        });

        observer.observe(circle);
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize particles for CTA background
    particlesJS('particles-cta', {
        particles: {
            number: { value: 80 },
            color: { value: '#ffffff' },
            shape: { type: 'circle' },
            opacity: { value: 0.5 },
            size: { value: 3 },
            move: {
                enable: true,
                speed: 2,
                direction: 'none',
                random: false,
                straight: false,
                out_mode: 'out',
                bounce: false,
            }
        },
        interactivity: {
            detect_on: 'canvas',
            events: {
                onhover: { enable: true, mode: 'repulse' },
                onclick: { enable: true, mode: 'push' },
                resize: true
            }
        }
    });
});
</script>

<?php if (!empty($inactiveUsers)): ?>
    <div class="inactive-users-section">
        <h3>Users Pending Activation</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inactiveUsers as $user): ?>
                        <tr>
                            <td><?= Html::encode($user->name) ?></td>
                            <td><?= Html::encode($user->company_name) ?></td>
                            <td><?= Html::encode($user->company_email) ?></td>
                            <td>
                                <?= Html::a('Activate', ['activate-user', 'id' => $user->id], [
                                    'class' => 'btn btn-success btn-sm',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to activate this user?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>


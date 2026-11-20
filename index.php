<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Home';
$isUserLoggedIn = !empty($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Darjibhai | Sewing the Happiness</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Main CSS -->
    <link
        rel="stylesheet"
        href="/darjibhai/assets/css/style.css"
    >

    <!-- Landing Page CSS -->
    <link
        rel="stylesheet"
        href="/darjibhai/assets/css/index.css"
    >

</head>

<body>


<!-- =====================================================
     HEADER / NAVBAR
===================================================== -->

<header class="site-header">

    <nav class="navbar navbar-expand-lg">

        <div class="container">

            <!-- Logo -->

            <a
                class="navbar-brand darji-logo"
                href="/darjibhai/index.php"
            >

                <span class="logo-icon">
                    <i class="fa-solid fa-scissors"></i>
                </span>

                <span>
                    Darji<span>bhai</span>
                </span>

            </a>


            <!-- Mobile Button -->

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >

                <i class="fa-solid fa-bars"></i>

            </button>


            <!-- Navigation -->

            <div
                class="collapse navbar-collapse"
                id="mainNavbar"
            >

                <ul class="navbar-nav mx-auto">

                    <li class="nav-item">

                        <a
                            class="nav-link active"
                            href="#home"
                        >
                            Home
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="#about"
                        >
                            About
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="#services"
                        >
                            Services
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="#reviews"
                        >
                            Reviews
                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="#contact"
                        >
                            Contact
                        </a>

                    </li>

                </ul>


                <!-- Login / Register -->

                <div class="navbar-buttons">
                    <?php if ($isUserLoggedIn): ?>
                    <a
                        href="/darjibhai/user/profile.php"
                        class="btn-register"
                    >
                        My Profile
                    </a>
                    <a
                        href="/darjibhai/user/orders.php"
                        class="btn-register"
                    >
                        My Orders
                    </a>
                    <?php else: ?>
                    <a
                        href="/darjibhai/auth/user-login.php"
                        class="btn-login"
                    >
                        Login
                    </a>
                    <a
                        href="/darjibhai/auth/user-register.php"
                        class="btn-register"
                    >
                        Register
                    </a>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </nav>

</header>


<!-- =====================================================
     HERO SECTION
===================================================== -->

<section
    id="home"
    class="hero-section"
>

    <div class="container">

        <div class="row align-items-center">

            <!-- Hero Content -->

            <div class="col-lg-6">

                <div class="hero-content">

                    <span class="hero-small-title">
                        <i class="fa-solid fa-scissors"></i>
                        Custom Tailoring Made Simple
                    </span>

                    <h1>
                        Your Style.
                        <span>Our Craft.</span>
                    </h1>

                    <p>
                        Experience easy and reliable tailoring
                        with Darjibhai. Place your order,
                        manage your measurements and track
                        your tailoring journey from one place.
                    </p>


                    <div class="hero-buttons">

                        <a
                            href="<?= $isUserLoggedIn ? '/darjibhai/user/profile.php' : '/darjibhai/auth/user-register.php' ?>"
                            class="hero-btn-primary"
                        >
                            <?= $isUserLoggedIn ? 'My Profile' : 'Get Started' ?>
                            <i class="fa-solid <?= $isUserLoggedIn ? 'fa-user' : 'fa-arrow-right' ?>"></i>
                        </a>

                        <a
                            href="#about"
                            class="hero-btn-secondary"
                        >

                            Learn More

                        </a>

                    </div>


                    <!-- Small Features -->

                    <div class="hero-features">

                        <div class="hero-feature">

                            <i class="fa-solid fa-ruler"></i>

                            <span>
                                Perfect Measurements
                            </span>

                        </div>

                        <div class="hero-feature">

                            <i class="fa-solid fa-shirt"></i>

                            <span>
                                Quality Stitching
                            </span>

                        </div>

                        <div class="hero-feature">

                            <i class="fa-solid fa-truck"></i>

                            <span>
                                Doorstep Service
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Hero Visual -->

            <div class="col-lg-6">

                <div class="hero-visual">

                    <div class="hero-circle"></div>

                    <div class="hero-card-main">

                        <div class="hero-card-icon">

                            <img src="/darjibhai/assets/images/shuit.jpg" alt="Shuit.jpeg">

                        </div>

                        <h3>
                            Made For You
                        </h3>

                        <p>
                            Designed and stitched
                            according to your style.
                        </p>

                    </div>


                    <div class="floating-card floating-card-one">

                        <i class="fa-solid fa-check"></i>

                        <div>

                            <strong>
                                Quality Work
                            </strong>

                            <small>
                                Carefully stitched
                            </small>

                        </div>

                    </div>


                    <div class="floating-card floating-card-two">

                        <i class="fa-solid fa-location-dot"></i>

                        <div>

                            <strong>
                                Track Your Order
                            </strong>

                            <small>
                                Know your order status
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     ABOUT SECTION
===================================================== -->

<section
    id="about"
    class="about-section"
>

    <div class="container">

        <div class="row align-items-center g-5">

            <!-- About Visual -->

            <div class="col-lg-5">

                <div class="about-image-box">

                    <div class="about-icon-large">

                        <img src="/darjibhai/assets/images/tailor.avif" alt="Tailor.jpeg">


                    </div>

                </div>

            </div>


            <!-- About Content -->

            <div class="col-lg-7">

                <span class="section-label">
                    About Darjibhai
                </span>

                <h2 class="section-title">
                    Traditional Tailoring
                    <span>With Modern Convenience</span>
                </h2>

                <p class="section-text">

                    Darjibhai brings your tailoring experience
                    into one simple platform. From placing
                    your order to tracking its progress,
                    everything is designed to be easy,
                    transparent and convenient.

                </p>

                <p class="section-text">

                    Our goal is to connect customers with
                    quality tailoring services while making
                    order management simple and organized.

                </p>


                <div class="about-points">

                    <div>

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Easy online ordering
                        </span>

                    </div>

                    <div>

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Manage your measurements
                        </span>

                    </div>

                    <div>

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Track your order status
                        </span>

                    </div>

                    <div>

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Simple customer dashboard
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     SERVICES SECTION
===================================================== -->

<section
    id="services"
    class="services-section"
>

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                What We Offer
            </span>

            <h2 class="section-title">
                Tailoring Made
                <span>Simple</span>
            </h2>

            <p>
                Everything you need for a smooth
                tailoring experience.
            </p>

        </div>


        <div class="row g-4">

            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-shirt"></i>

                    </div>

                    <h3>
                        Custom Tailoring
                    </h3>

                    <p>
                        Get products stitched according
                        to your requirements and style.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-ruler-combined"></i>

                    </div>

                    <h3>
                        Body Measurements
                    </h3>

                    <p>
                        Maintain your measurements and
                        make future orders easier.
                    </p>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-truck-fast"></i>

                    </div>

                    <h3>
                        Order Tracking
                    </h3>

                    <p>
                        Track your order progress from
                        placement to delivery.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     REVIEWS SECTION
===================================================== -->

<section
    id="reviews"
    class="reviews-section"
>

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                Customer Reviews
            </span>

            <h2 class="section-title">
                What Our Customers
                <span>Say</span>
            </h2>

        </div>


        <div class="row g-4">

            <div class="col-lg-4">

                <div class="review-card">

                    <div class="review-stars">

                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>

                    </div>

                    <p>
                        "The ordering process is simple and
                        convenient. I can easily check my
                        order status."
                    </p>

                    <div class="review-user">

                        <div class="review-avatar">
                            R
                        </div>

                        <div>

                            <strong>
                                Rahul
                            </strong>

                            <small>
                                Customer
                            </small>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-4">

                <div class="review-card">

                    <div class="review-stars">

                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>

                    </div>

                    <p>
                        "Darjibhai makes it easy to manage
                        tailoring orders and measurements."
                    </p>

                    <div class="review-user">

                        <div class="review-avatar">
                            P
                        </div>

                        <div>

                            <strong>
                                Priya
                            </strong>

                            <small>
                                Customer
                            </small>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-4">

                <div class="review-card">

                    <div class="review-stars">

                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>

                    </div>

                    <p>
                        "A convenient way to keep track of
                        my tailoring orders."
                    </p>

                    <div class="review-user">

                        <div class="review-avatar">
                            A
                        </div>

                        <div>

                            <strong>
                                Amit
                            </strong>

                            <small>
                                Customer
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     CONTACT SECTION
===================================================== -->

<section
    id="contact"
    class="contact-section"
>

    <div class="container">

        <div class="row g-5">

            <!-- Contact Information -->

            <div class="col-lg-5">

                <span class="section-label">
                    Contact Us
                </span>

                <h2 class="section-title">
                    Let's Talk About
                    <span>Your Style</span>
                </h2>

                <p class="section-text">

                    Have a question about your order
                    or our tailoring services?
                    Get in touch with us.

                </p>


                <div class="contact-info">

                    <div class="contact-item">

                        <div class="contact-icon">

                            <i class="fa-solid fa-phone"></i>

                        </div>

                        <div>

                            <strong>
                                Phone
                            </strong>

                            <span>
                                +91 12345 67890
                            </span>

                        </div>

                    </div>


                    <div class="contact-item">

                        <div class="contact-icon">

                            <i class="fa-solid fa-envelope"></i>

                        </div>

                        <div>

                            <strong>
                                Email
                            </strong>

                            <span>
                                info@darjibhai.com
                            </span>

                        </div>

                    </div>


                    <div class="contact-item">

                        <div class="contact-icon">

                            <i class="fa-solid fa-location-dot"></i>

                        </div>

                        <div>

                            <strong>
                                Location
                            </strong>

                            <span>
                                Rajkot, Gujarat, India
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Contact Form -->

            <div class="col-lg-7">

                <div class="contact-form-card">

                    <form
                        method="post"
                        action=""
                    >

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label>
                                    Your Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your name"
                                >

                            </div>


                            <div class="col-md-6">

                                <label>
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email"
                                >

                            </div>


                            <div class="col-12">

                                <label>
                                    Subject
                                </label>

                                <input
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="Enter subject"
                                >

                            </div>


                            <div class="col-12">

                                <label>
                                    Message
                                </label>

                                <textarea
                                    name="message"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Write your message"
                                ></textarea>

                            </div>


                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="contact-submit"
                                >

                                    Send Message

                                    <i class="fa-solid fa-paper-plane"></i>

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="site-footer">

    <div class="container">

        <div class="row g-4">

            <div class="col-lg-5">

                <a
                    href="/darjibhai/index.php"
                    class="footer-logo"
                >

                    <i class="fa-solid fa-scissors"></i>

                    darji<span>bhai</span>

                </a>

                <p>
                    Making tailoring simple,
                    convenient and personal.
                </p>

                <div class="footer-social">

                    <a href="#">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="#">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a href="#">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>

                </div>

            </div>


            <div class="col-lg-2 col-md-4">

                <h4>
                    Quick Links
                </h4>

                <a href="#home">
                    Home
                </a>

                <a href="#about">
                    About
                </a>

                <a href="#services">
                    Services
                </a>

                <a href="#contact">
                    Contact
                </a>

            </div>


            <div class="col-lg-2 col-md-4">

                <h4>
                    Account
                </h4>

                <?php if ($isUserLoggedIn): ?>
                    <a href="/darjibhai/user/profile.php">My Profile</a>
                    <a href="/darjibhai/user/orders.php">My Orders</a>
                <?php else: ?>
                    <a href="/darjibhai/auth/user-login.php">Login</a>
                    <a href="/darjibhai/auth/user-register.php">Register</a>
                <?php endif; ?>

            </div>


            <div class="col-lg-3 col-md-4">

                <h4>
                    Contact
                </h4>

                <p class="footer-contact">
                    Rajkot, Gujarat, India
                </p>

                <p class="footer-contact">
                    +91 12345 67890
                </p>

                <p class="footer-contact">
                    info@darjibhai.com
                </p>

            </div>

        </div>


        <div class="footer-bottom">

            <span>
                © <?= date('Y') ?> Darjibhai.
                All rights reserved.
            </span>

            <span>
                Sewing the Happiness
                <i class="fa-solid fa-scissors"></i>
            </span>

        </div>

    </div>

</footer>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script
    src="/darjibhai/assets/js/script.js"
></script>

<script src="/darjibhai/assets/js/form-validation.js" defer></script>
</body>

</html>

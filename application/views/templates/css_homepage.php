<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLMD Learning Management Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #800000;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #800000;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #fff;
            /* Light on desktop */
            transition: background-color 0.3s ease;
        }

        .navbar-toggler {
            border-color: #800000;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(128,0,0, 1)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }


        .navbar .nav-link {
            position: relative;
            color: #800000 !important;
            font-weight: 500;
            padding: 8px 12px;
            transition: color 0.3s ease;
        }

        .navbar .nav-link::after {
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            left: 0;
            bottom: 0;
            background-color: #800000;
            transition: width 0.3s;
        }

        .navbar .nav-link:hover::after {
            width: 100%;
        }

        .navbar .nav-link:hover {
            color: #a00000 !important;
        }

        .navbar .nav-link.btn {
            border: 2px solid #800000;
            color: #800000 !important;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar .nav-link.btn:hover {
            background-color: #800000;
            color: #fff !important;
        }

        .hero-section {
            background: url("<?= base_url(); ?>assets/images/bgftad.jpg") no-repeat center center;
            background-size: cover;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 10%;
            position: relative;
        }

        .hero-section .left {
            margin-top: -300px;
                max-width: 1280px;
                display: flex;
                flex-direction: column;
                justify-content: center; /* ensures internal text stays centered vertically */
            }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.1rem;
            margin-top: 20px;
            color: #f0f0f0;
        }

        .hero-section .btn {
            font-size: 1rem;
            padding: 12px 30px;
        }

        .hero-section .btn-outline-light {
            color: #fff;
            border-color: #fff;
        }

        .hero-section .btn-outline-light:hover {
            color: #800000;
        }

        .hero-section .btn-light {
            background-color: #fff;
            color: var(--primary-color);
        }

        .feature-icons {
            background-color: #fff;
            padding: 40px 20px;
            text-align: center;
        }

        .feature-icons i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .feature-icons p {
            margin-top: 10px;
        }

        .highlight-text {
            color: #ffc107;
            /* Example: Bootstrap warning yellow */
            font-weight: bold;
        }
        

        .dropdown-menu-light a {
            color:#800000;
        }
        @media (max-width: 768px) {
            .hero-section {
                text-align: center;
            }

            .hero-section .left,
            .hero-section .right {
                padding: 20px;
            }

            .navbar .nav-link {
                color: #800000 !important;
            }
        }
    </style>
<?php $this->load->view('templates/pwa'); ?>
</head>

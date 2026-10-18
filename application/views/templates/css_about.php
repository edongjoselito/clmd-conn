<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CLMD Learning Management Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />

  <style>
    :root {
      --ftad-maroon: #800000;
      --ftad-maroon-dark: #6b0000;
    }

    body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
    .navbar { background-color: #fff; transition: background-color 0.3s ease; }
    .navbar-toggler { border-color: var(--ftad-maroon); }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(128,0,0, 1)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    .navbar .nav-link {
      position: relative; color: var(--ftad-maroon) !important; font-weight: 500; padding: 8px 12px; transition: color 0.3s ease;
    }
    .navbar .nav-link::after {
      content: ''; position: absolute; width: 0%; height: 2px; left: 0; bottom: 0; background-color: var(--ftad-maroon); transition: width 0.3s;
    }
    .navbar .nav-link:hover::after { width: 100%; }
    .navbar .nav-link:hover { color: #a00000 !important; }
    .navbar .nav-link.btn {
      border: 2px solid var(--ftad-maroon); color: var(--ftad-maroon) !important; border-radius: 20px; transition: all 0.3s ease;
    }
    .navbar .nav-link.btn:hover { background-color: var(--ftad-maroon); color: #fff !important; }

    .hero {
      background: linear-gradient(135deg, var(--ftad-maroon), #b22222);
      color: #fff; padding: 80px 0 70px;
    }
    .hero h1 { font-weight: 700; }
    .hero .lead { opacity: 0.95; }

    .section-title {
      color: var(--ftad-maroon);
      font-weight: 700;
      margin-bottom: 1rem;
      text-align: center;
    }
    .section-underline {
      width: 60px; height: 4px; background: var(--ftad-maroon); border-radius: 10px; margin: 0.5rem auto 2rem;
    }

    .card-soft {
      border: 0;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.08);
      background: #fff;
    }

    .icon-badge {
      width: 56px; height: 56px; display: grid; place-items: center;
      border-radius: 50%; background: #fff; box-shadow: 0 6px 20px rgba(0,0,0,.08);
      color: var(--ftad-maroon);
    }

    .feature-icons i { transition: transform .2s ease; }
    .feature-icons .col-md-3:hover i { transform: translateY(-4px); }

    .timeline {
      position: relative; padding-left: 24px;
    }
    .timeline::before {
      content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e3e6ea;
    }
    .tl-item { position: relative; margin-bottom: 1.25rem; }
    .tl-item::before {
      content: ''; position: absolute; left: -2px; top: 6px; width: 14px; height: 14px;
      border-radius: 50%; background: var(--ftad-maroon);
    }
    .footer-cta {
      background: linear-gradient(135deg, #b22222, var(--ftad-maroon));
      color: #fff; border-radius: 20px;
    }
  </style>
<?php $this->load->view('templates/pwa'); ?>
</head>

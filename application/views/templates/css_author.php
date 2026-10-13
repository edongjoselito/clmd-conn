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
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    .navbar {
      background-color: #fff;
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

    .author-section {
      padding: 60px 20px;
      background: linear-gradient(to right, #800000, #b22222);
      color: #fff;
      text-align: center;
    }

    .author-section h2 {
      font-weight: 700;
      margin-bottom: 8px;
      font-size: 2.5rem;
      position: relative;
      display: inline-block;
    }

    .author-section h2::after {
      content: '';
      display: block;
      height: 3px;
      width: 50px;
      background: #fff;
      margin: 10px auto 0;
      border-radius: 20px;
    }

    .author-section p {
      font-size: 1.1rem;
      margin-top: 10px;
    }

    .profile-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
      overflow: hidden;
      text-align: center;
      padding: 30px 20px;
      transition: transform 0.4s ease, box-shadow 0.3s ease;
      position: relative;
    }

    .profile-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 14px 50px rgba(218, 165, 32, 0.3);
    }

    .profile-card img {
      width: 130px;
      height: 130px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 18px;
      border: 4px solid #800000;
      transition: transform 0.3s ease;
    }

    .profile-card:hover img {
      transform: scale(1.05);
    }

    .profile-card h5 {
      font-weight: 600;
      margin-bottom: 5px;
      color: #800000;
    }

    .profile-card span {
      display: block;
      color: #555;
      font-size: 0.95rem;
      margin-bottom: 12px;
    }

    .profile-card .social-icons a {
      margin: 0 8px;
      font-size: 1.25rem;
      color: #800000;
      transition: color 0.3s ease;
    }

    .profile-card .social-icons a:hover {
      color: #b22222;
    }
  </style>
</head>

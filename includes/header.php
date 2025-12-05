<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoFu Skansa - Lost and Found</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #e20a15ff;
            --secondary-color: #2600ffff; 
            --light-gray: #f8f9fa;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: var(--primary-color);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-warning {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            height: 30px;
            margin-right: 10px;
        }
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease-in-out;
        }
        .navbar-nav .nav-link:hover {
            background-color: rgba(252, 5, 5, 0.1);
        }
        .btn-success {
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="assets/Lodo Skansa.png" alt="Logo Sekolah">
            LoFu Skansa
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ditemukan.php">Barang Ditemukan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hilang.php">Barang Hilang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-success text-white ms-2" href="laporkan.php">Laporkan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">

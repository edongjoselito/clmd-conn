

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url(); ?>">
                <img src="<?= base_url(); ?>assets/images/ftad-logo.png" alt="Logo" height="80" width="200">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url(); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url(); ?>Pages/about">About</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manualsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Manuals
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="manualsDropdown">
                            <li><a class="dropdown-item" href="<?= base_url('resources/FTAD_Manual-for-Dissemination.pdf'); ?>" target="_blank">FTAD Manual</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('resources/GAD_Manual_FTAD-for-Dissemination-1.pdf'); ?>" target="_blank">GAD Manual</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#">Features</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url(); ?>Pages/authors">Authors</a></li>
                     <li class="nav-item"><a class="nav-link" href="<?= base_url(); ?>Pages/data_privacy">Data Privacy</a></li>
                </ul>
            </div>
        </div>
    </nav>
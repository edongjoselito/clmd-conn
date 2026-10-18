<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>CLMD Learning Management Portal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Responsive bootstrap 4 admin template" name="description" />
        <meta content="Coderthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="<?= base_url(); ?>assets/images/favicon.ico">

        <!-- App css -->
        <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
        <link href="<?= base_url(); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="<?= base_url(); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-stylesheet" />
        <style>
        .password-wrapper{
            position: relative;
        }

        .password-input{
            padding-right: 45px;
        }

        .toggle-password{
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 0;
            margin: 0;
            color: #6c757d;
            cursor: pointer;
            outline: none;
        }

        .toggle-password:focus{
            outline: none;
            box-shadow: none;
        }
        </style>

    <?php $this->load->view('templates/pwa'); ?>
</head>

    <body class="authentication-page">

        <div class="account-pages my-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-header p-4" style="background:#a00000">
                                <h4 class="text-white text-center mb-0 mt-0"><a href="<?= base_url(); ?>"><img src="<?= base_url(); ?>assets/images/ftad.png" width="40%" alt=""></a></h4>
                            </div>
                            <div class="card-body">
                            <?php if($this->session->flashdata('failed')) : ?>

                            <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>'
                                    .$this->session->flashdata('failed'). 
                                '</div>'; 
                            ?>
                            <?php endif; ?> 
                            <?php if($this->session->flashdata('success')) : ?>

                                <?= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>'
                                        .$this->session->flashdata('success'). 
                                    '</div>'; 
                                ?>
                                <?php endif; ?> 
                                
                            <?= validation_errors(); ?>
                                <?= form_open('log_in') ?>

                                    <div class="form-group mb-3">
                                        <label for="Username">School ID, username or email address</label>
                                        <input class="form-control" type="text" id="Username" name="username"  autocomplete="off" >
                                    </div>

                                    <!-- <div class="form-group mb-3">
                                        <label for="password">Password :</label>
                                        <div class="input-group">
                                            <input class="form-control" type="password" required name="password" id="password" autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div> -->

                                    <div class="form-group mb-3">
                                        <label for="password">Password :</label>
                                        
                                        <div class="password-wrapper">
                                            <input 
                                                id="password"
                                                class="form-control password-input" 
                                                type="password" 
                                                required 
                                                name="password" 
                                                autocomplete="off"
                                            >
                                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                                <i class="fa fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>



                                    <div class="form-group mb-3">
                                        <div class="checkbox checkbox-success">
                                           <a href="Pages/forgot_password" class="text-muted float-left">Forgot Password &nbsp;</a>
                                           
                                        </div>
                                    </div>
                                    <br /><br />
                                    <div class="form-group row text-center mb-12">
                                        <div class="col-12">
                                            <button class="btn btn-md btn-block waves-effect waves-light" style="background:#a00000" type="submit" name="submit">Sign In</button>
                                        </div>
                                    </div>

                                    <p>Do you already have an account? If not, please <a href="<?= base_url(); ?>signup"><span class="badge" style="background:#a00000">create</span></a> one.</p>

                                

                                    
                                </form>

                            </div>
                            <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <!-- end row -->

                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->

            </div>
        </div>

        <!-- Vendor js -->
        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
        <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const icon = document.getElementById("toggleIcon");

            if (password.type === "password") {
                password.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
        </script>

    </body>

</html>

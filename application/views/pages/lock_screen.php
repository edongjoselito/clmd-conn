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
        <link rel="shortcut icon" href="assets/images/dts.ico">

        <!-- App css -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

    </head>

    <body class="authentication-page">

        <div class="account-pages my-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-header text-center p-4 bg-primary">
                            <h4 class="text-white text-center mb-0 mt-0"><img  width="100%" src="<?= base_url(); ?>assets/images/logo.png" alt=""></h4>
                            </div>
                            <div class="card-body">
                            <?php 
                                $pro_image = $this->session->image;
                            ?>
                                <?= form_open('lock_user_screen'); ?>

                                    <div class="user-thumb text-center mb-4">
                                        <img src="<?= base_url(); ?>uploads/users/<?= $pro_image === '' ? 'icon/avatar-1.jpg' : rawurlencode(basename((string) $pro_image)); ?>" class="img-fluid rounded-circle avatar-lg" alt="thumbnail">
                                    </div>

                                    <div class="form-group text-center mb-0">
                                        <h5><?= html_escape($this->session->user); ?></h5>
                                        <?php if($this->session->flashdata('failed')) : ?>

                                        <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>'
                                                .$this->session->flashdata('failed'). 
                                            '</div>'; 
                                        ?>
                                        <?php endif; ?>
                                        <p>Enter your password to access the admin.</p>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" placeholder="Enter Password">
                                            <span class="input-group-append"> <button type="submit" class="btn btn-primary">Login</button> </span>
                                        </div>

                                    </div>
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
        <script src="assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="assets/js/app.min.js"></script>

    </body>

</html>

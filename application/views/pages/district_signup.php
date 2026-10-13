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

    </head>

    <body class="authentication-page">

        <div class="account-pages my-12">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-10">
                        <div class="card mt-4">
                            <div class="card-header p-4" style="background:#a00000">
                                <a href="<?= base_url(); ?>">
                                <h4 class="text-white text-center mb-0 mt-0"><img src="<?= base_url(); ?>assets/images/ftad.png" width="30%" alt=""></h4>
                                </a>
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
                                <?= form_open('signup_district') ?>

                                <div class="form-row">
                                    
                                    <div class="form-group col-md-6">
                                        <label for="schoolID">Username</label>
                                        <input class="form-control" type="text" id="schoolID" required="" name="schoolID">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="schoolID">Password</label>
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
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="schoolName">Email</label>
                                        <input class="form-control" type="email" id="schoolEmail" name="schoolEmail" required="">
                                    </div>
                                    
                                </div>

                                

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="schoolName">Division</label>
                                        <select name="division_id" id="division" class="form-control">
                                                <option value="">Select Division</option>
                                                    <?php foreach($division as $row){ ?>
                                                        <option value="<?= $row->id; ?>"><?= $row->description; ?></option>
                                                <?php } ?>
                                            </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="schoolID">District/Cluster</label>
                                        <select name="d_id" id="district" class="form-control">
                                                <option value="">Select District/Cluster</option>
                                            </select>
                                    </div>
                                </div>

                                

                                    
                                    <input type="hidden" valu="" name="renren">
                                    <input type="hidden" valu="" name="ivykate">
                                    <input type="hidden" valu="" name="ivankyle">
                                    <input type="hidden" valu="" name="ic">
                                    

                                    <div class="form-group mb-4">
                                        <div class="checkbox checkbox-success">
                                            <input id="remember" name="remember" type="checkbox" checked="" required>
                                            <label for="remember">
                                                I accept <strong><a href="#" data-toggle="modal" data-target="#myModal">Terms and Conditions</a></strong>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="g-recaptcha" data-sitekey="6LedsqorAAAAAMSwAX3ZLaCOyCFv5oVRRwR9AW34"></div>
                                    </div>


                                    <div class="form-group text-right mt-4 mb-4">
                                        <div class="col-12">
                                            <button class="btn btn-md waves-effect waves-light" style="background:#a00000" type="submit">Register</button>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-sm-12 text-center">
                                            <a href="<?= base_url(); ?>">Already have account?</a>
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

        <!-- sample modal content -->
                                        <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="background:#a00000">
                                                        <h5 class="modal-title text-white" id="myModalLabel">DECLARATION AND ATTESTATION:</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-justify" style="text-indent: 2rem;">The Department of Education (DepEd) complies with Republic Act No. 10173 or the Data Privacy Act of 2012 and its Implementing Rules and Regulations. By ticking the checkbox and clicking “Submit,” you freely, specifically, and informedly consent to DepEd’s collection, processing, and storage of your personal information (e.g., name, position/designation, school, contact details, and assessment responses) for lawful and legitimate purposes connected with the implementation, monitoring, and data management under DepEd Order No. 007, s. 2024 on the Revised School-Based Management (SBM) System. This system is a Regional initiative to consolidate results, provide technical assistance, and support continuous improvement at the school level through the Schools Division Offices.</p>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->

        <!-- Vendor js -->
        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                 <script>
                    function validateCaptcha(event) {
                        var response = grecaptcha.getResponse();
                        if (response.length == 0) {
                            alert("Please verify that you are not a robot.");
                            event.preventDefault(); // prevent form submission
                            return false;
                        }
                    }
                </script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
        $(document).ready(function(){
            $('#division').on('change', function(){
                var divisionID = $(this).val();
                $('#district').html('<option value="">Loading...</option>');
                if(divisionID != ''){
                    $.ajax({
                        url: '<?= base_url("Pages/get_district_by_division"); ?>',
                        method: 'POST',
                        data: {division_id: divisionID},
                        dataType: 'json',
                        success: function(response){
                            $('#district').html('<option value="">Select District</option>');
                            $.each(response, function(index, item){
                                $('#district').append('<option value="'+item.id+'">'+item.description+'</option>');
                            });
                        }
                    });
                } else {
                    $('#district').html('<option value="">Select District</option>');
                }
            });
        });
        </script>
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

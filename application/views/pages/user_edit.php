                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">

                                    <div class="page-title-right">
                                        <ol class="breadcrumb p-0 m-0">
                                            <li class="breadcrumb-item"><a href="<?= base_url(); ?><?= in_array($this->session->position, array('division', 'ict'), true) ? 'pages/userlist_division' : 'pages/userlist'; ?>">Manage User</a></li>
                                            <li class="breadcrumb-item active">Update User</li>
                                        </ol>
                                    </div>

                                <?php if($this->session->flashdata('success')) : ?>

                                    <?= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>'
                                            .$this->session->flashdata('success'). 
                                        '</div>'; 
                                    ?>
                                    <?php endif; ?>

                                    <?php if($this->session->flashdata('danger')) : ?>
                                    <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>'
                                            .$this->session->flashdata('danger'). 
                                        '</div>'; 
                                    ?>
                                    <?php endif;  ?>

                                    <?= validation_errors(); ?>
                                    
                                   
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->


                       <!-- ============================================================== -->
                       <!-- Main Content here -->
                       <!-- ============================================================== -->



                       <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-4"><?= $title; ?></h4>

                                        <?php 
                                            $attributes = array('class' => 'parsley-examples');
                                            echo form_open('pages/user_update', $attributes);
                                        ?>
                                            <input type="hidden" name="id" value="<?= (int) $data->id; ?>">
                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">First Name<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required class="form-control" name="fname" value="<?= html_escape($data->fname); ?>" oninput="this.value = this.value.toUpperCase()" placeholder="First Name">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Middle Name</label>
                                                <div class="col-md-7">
                                                    <input type="text"  name="mname" value="<?= html_escape($data->mname); ?>" class="form-control" placeholder="Middle Name" oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Last Name<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required name="lname" value="<?= html_escape($data->lname); ?>" class="form-control" placeholder="Last Name" oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-8 offset-md-4">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="customRadio1" name="gender" value="0" <?php if($data->gender == 0){echo "checked";}?> class="custom-control-input">
                                                        <label class="custom-control-label text-xs" for="customRadio1">Male</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="customRadio2" name="gender" <?php if($data->gender == 1){echo "checked";}?> value="1" class="custom-control-input">
                                                        <label class="custom-control-label text-xs" for="customRadio2">Female</label>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            
                                            <div class="form-group row mb-0">
                                                <div class="col-md-8 offset-md-4">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-1">
                                                        Update
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <script>
                        function myFunction() {
                        var x = document.getElementById("myInput");
                        if (x.type === "password") {
                            x.type = "text";
                        } else {
                            x.type = "password";
                        }
                        var x = document.getElementById("myInput2");
                        if (x.type === "password") {
                            x.type = "text";
                        } else {
                            x.type = "password";
                        }
                        }
                        </script>

<?php $this->load->view('pages/_user_signature', array('profile_user' => $data, 'signature' => $signature)); ?>

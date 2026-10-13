                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">

                                    <div class="page-title-right">
                                        <ol class="breadcrumb p-0 m-0">
                                            <li class="breadcrumb-item"><a href="<?= base_url(); ?>pages/profilelist">Profile</a></li>
                                            <li class="breadcrumb-item active">Add New Entry</li>
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
                                            echo form_open('pages/profile_new', $attributes);
                                        ?>
                                            

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Fullname<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required class="form-control" name="name" oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Document Type<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <select class="form-control" required name="docType">
                                                        <option></option>
                                                        <option value="1">Certificate of Completion</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Description<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required name="description" class="form-control"  oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Document No.<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required name="docNo" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-4 col-form-label">Date Released<span class="text-danger">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="date" required name="dateReleased" class="form-control">
                                                </div>
                                            </div>

                                            
                                            <div class="form-group row mb-0">
                                                <div class="col-md-8 offset-md-4">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light mr-1">
                                                        Submit
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

                   
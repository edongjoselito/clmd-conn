                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">

                                    <div class="page-title-right">
                                        <ol class="breadcrumb p-0 m-0">
                                            <li class="breadcrumb-item"><a href="<?= base_url(); ?>pages/userlist">Manage User</a></li>
                                            <li class="breadcrumb-item active">Add New User</li>
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

                                        <?php $att = array('class' => 'parsley-examples'); ?>
                                        <?= form_open('pages/school_new',$att); ?>

                                            <div class="form-row">


                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label" name="q1">School Name</label>
                                                    <input type="text" required class="form-control" name="schoolName" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label">School ID</label>
                                                    <input type="text" required class="form-control" name="schoolID" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label">Permit No</label>
                                                    <input type="text" required class="form-control" name="permitNo" value="" placeholder="Permit No">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label" name="q1">District</label>
                                                    <select class="form-control" required name="district">
                                                        <option disabled selected>Choose District</option>
                                                        <?php 
                                                        foreach($district as $row){
                                                        ?>
                                                        <option value="<?= $row->id; ?>"><?= $row->description; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="form-row">

                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label" name="q1">Offers</label>
                                                    <select class="form-control" required name='course'>
                                                        <option disabled selected>Choose Offers</option>
                                                        <?php $district = array('Elementary','Integrated','Junior High School','Secondary');

                                                        foreach($district as $row){
                                                        ?>
                                                        <option value="<?= $row; ?>"><?= $row; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-3">
                                                <label for="inputAddress" class="col-form-label" name="q1">School Type</label>
                                                    <select class="form-control" required name='schoolType'>
                                                        <option disabled selected></option>
                                                        <?php $district = array(0 => 'Public',1 => 'Private');

                                                        foreach($district as $row => $key){
                                                        ?>
                                                        <option value="<?= $row; ?>"><?= $key; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label" name="q1">Congressional District</label>
                                                    <select class="form-control" required name='congDist'>
                                                        <option disabled selected></option>
                                                        <?php $district = array(1,2,3,4);

                                                        foreach($district as $row){
                                                        ?>
                                                        <option value="<?= $row; ?>"><?= $row; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <label for="inputAddress" class="col-form-label">year Establish</label>
                                                    <input type="text" required class="form-control" name="yearEstab" value="">
                                                </div>

                                            </div>

                                            

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputEmail4" class="col-form-label">School Head</label>
                                                    <input type="text" required class="form-control" name="adminFName" value="" placeholder="First Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">_</label>
                                                    <input type="text" required class="form-control" name="adminMName" value="" placeholder="Middle Name">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputPassword4" class="col-form-label">_</label>
                                                    <input type="text" required class="form-control" name="adminLName" value="" placeholder="Last Name">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">School Head Designation</label>
                                                    <input type="text" required class="form-control" name="adminDesignation" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label">School Head E-mail</label>
                                                    <input type="text" required class="form-control" name="adminEmail" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label">School E-mail</label>
                                                    <input type="text" required class="form-control" name="schoolEmail" value="">
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">Contact Number/s</label>
                                                    <input type="text" required  class="form-control" name="adminMobile" value="">
                                                </div>
                                            </div>


                                            <div class="form-row">
                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">Province</label>
                                                    <input type="text" required class="form-control" name="province" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">Municipality/City</label>
                                                    <input type="text" required class="form-control" name="city" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label">Barangay</label>
                                                    <input type="text" required class="form-control" name="brgy" value="">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputEmail4" class="col-form-label">Setio</label>
                                                    <input type="text" required class="form-control" name="sitio" value="">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                            <div class="form-group col-md-2">
                                                    <label for="inputEmail4" class="col-form-label">Electricity</label>
                                                    <select class="form-control" name="electricity">
                                                        <option value="0">Yes</option>
                                                        <option  value="1">No</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="inputEmail4" class="col-form-label">Internet Connection</label>
                                                    <select class="form-control" name="internet">
                                                        <option  value="0">Yes</option>
                                                        <option  value="1">No</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">Internet Provider(If Yes)</label>
                                                    <input type="text" class="form-control" name="provider" value="">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="inputPassword4" class="col-form-label">Internet Speed(If Yes)</label>
                                                    <input type="text" class="form-control" name="mb" value="" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="inputPassword4" class="col-form-label">ICT Coordinator ID (Employee Number)</label>
                                                    <input type="text" class="form-control" name="coor" value="" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                                </div>
                                               
                                            </div>


                                            <input type="submit" name="submit" value="Add New" class="btn btn-primary">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#region').on('change', function () {
        let regionId = $(this).val();
        $('#province').html('<option>Loading...</option>');
        $('#district').html('<option>Select District</option>');

        $.ajax({
            url: '<?= base_url("Pages/get_provinces") ?>', // ✅ Corrected controller
            type: 'POST',
            data: { region_id: regionId },
            dataType: 'json',
            success: function (data) {
                let options = '<option value="">Select Province</option>';
                $.each(data, function (i, item) {
                    options += '<option value="' + item.id + '">' + item.description + '</option>';
                });
                $('#province').html(options);
            }
        });
    });

    $('#province').on('change', function () {
        let provinceId = $(this).val();
        $('#district').html('<option>Loading...</option>');

        $.ajax({
            url: '<?= base_url("Pages/get_districts") ?>', // ✅ Corrected controller
            type: 'POST',
            data: { province_id: provinceId },
            dataType: 'json',
            success: function (data) {
                let options = '<option value="">Select District</option>';
                $.each(data, function (i, item) {
                    options += '<option value="' + item.id + '">' + item.description + '</option>';
                });
                $('#district').html(options);
            }
        });
    });
</script>
                    </div>
                    <!-- end container-fluid -->

                </div>
                <!-- end content -->

                
                
                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                &copy; <?= date('Y'); ?> CLMD Learning Management Portal. All Rights Reserved.
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <div id="renren" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Change Password</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="<?= base_url('Pages/change_password_user') ?>" method="post">
                                                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                                            
                                                            <p class="text-muted small">Enter your current password to confirm this change.</p>
                                                            <div class="form-group"><label for="current-password">Current Password</label><input type="password" class="form-control" name="current_password" id="current-password" autocomplete="current-password" required></div>
                                                            <div class="form-group"><label for="new-password">New Password</label><input type="password" class="form-control" name="password" id="new-password" autocomplete="new-password" minlength="8" maxlength="128" required></div>
                                                            <div class="form-group"><label for="confirm-password">Confirm New Password</label><input type="password" class="form-control" name="password_confirm" id="confirm-password" autocomplete="new-password" minlength="8" maxlength="128" required></div>

                                                    </div>

                                                    <div class="modal-footer">

                                                        <button type="submit" 
                                                                class="btn btn-primary waves-effect waves-light">
                                                            Save
                                                        </button>
                                                    </div>

                                                    </form>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal --></div>
                                        

                                        <div id="ivankylecrodua" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">UPLOAD PROFILE IMAGE</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="<?= base_url('Pages/user_profile') ?>" method="post" enctype="multipart/form-data">
                                                            <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                                                            
                                                            <div class="form-group row">
                                                                <div class="col-lg-12">
                                                                   <label>Profile Picture <code>(Accepted file extensions: PNG, JPG, GIF, and JPEG.)</code></label>
                                                                    <input type="file" class="form-control" name="file" required>
                                                                    <p>Limit the size to <span style="color:red; font-weight:bold">1MB only</span>. The recommended size is <span style="color:red; font-weight:bold">128px by 128px</span>.</p> 
                                                                </div>
                                                            </div>

                                                    </div>

                                                    <div class="modal-footer">

                                                        <button type="submit" 
                                                                class="btn btn-primary waves-effect waves-light">
                                                            Save
                                                        </button>
                                                    </div>

                                                    </form>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->

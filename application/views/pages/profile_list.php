                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <a class="btn btn-success" href="<?= base_url(); ?>pages/profile_new">Add New</a>
                                    

                                
                                    <div class="clearfix"></div>

                                    <?php if($this->session->flashdata('success')) : ?>

                                        <?= '<br /><div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>'
                                                .$this->session->flashdata('success'). 
                                            '</div>'; 
                                        ?>
                                        <?php endif; ?>

                                        <?php if($this->session->flashdata('danger')) : ?>
                                        <?= '<br /><div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>'
                                                .$this->session->flashdata('danger'). 
                                            '</div>'; 
                                        ?>
                                        <?php endif;  ?>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->


                       <!-- ============================================================== -->
                       <!-- Main Content here -->
                       <!-- ============================================================== -->



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body table-responsive">
                                        <h4 class="m-t-0 header-title mb-4"><?= $title; ?></h4>

                                        <table id="datatable" class="table table-bordered dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                                            <thead>
                                                <tr>
                                                    <th>Fullname</th>
                                                    <th>Doc. Type</th>
                                                    <th>Description</th>
                                                    <th>Doc. No.</th>
                                                    <th>Date Released</th>
                                                    <th>QR Code</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php foreach($data as $row){?>
                                                <tr>
                                                    <td><?= html_escape($row->name); ?></td>
                                                    <td><?= html_escape($row->docType); ?></td>
                                                    <td><?= html_escape($row->description); ?></td>
                                                    <td><?= html_escape($row->docNo); ?></td>
                                                    <td><?= html_escape($row->dateReleased); ?></td>
                                                    <td>
                                                        <a target="_blank" rel="noopener" href="<?= base_url(); ?>Pages/qr/<?= (int) $row->id; ?>"><i class="mdi mdi-qrcode-scan text-success tooltips" data-placement="top" data-toggle="tooltip" data-original-title="QR Code"></i></a>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->






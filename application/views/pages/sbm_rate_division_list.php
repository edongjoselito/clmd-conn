                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    

                                
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

                                        <table class="table mb-0">
                                            <!-- <table class="table mb-0"> -->
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Division Name </th>
                                                    <th>Count</th>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    $c = 1; 
                                                    foreach ($data as $row) {
                                                        $ivan = $this->Common->three_cond_count_row('sbm','fy',$this->session->fy,'division',$row->id,$this->uri->segment(3),$this->uri->segment(4))->num_rows();
                                                    ?>
                                                    <tr>
                                                        <td><?= $c++; ?></td>
                                                        <td><?= html_escape($row->description); ?></td>
                                                        <td>
                                                            <a href="<?= base_url(); ?>Pages/sbm_rate_list/<?= $this->uri->segment(3); ?>/<?= $this->uri->segment(4); ?>">
                                                                <span class="badge badge-<?= $ivan != 0 ? 'primary' : 'danger'; ?>"><?= $ivan; ?></span>
                                                            </a>
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



                        

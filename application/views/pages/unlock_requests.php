                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?= $title; ?></h4>

                                    <?php if($this->session->flashdata('success')) : ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <i class="mdi mdi-check-circle"></i> <?= $this->session->flashdata('success'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($this->session->flashdata('danger')) : ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <i class="mdi mdi-alert-circle"></i> <?= $this->session->flashdata('danger'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <!-- Main Content -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header-title mb-4">
                                            <h5 class="mb-0"><i class="mdi mdi-lock-open-variant-outline"></i> TA Report Unlock Requests</h5>
                                            <p class="text-muted mb-0">Manage unlock requests from schools in your division.</p>
                                        </div>

                                        <?php if (empty($requests)) : ?>
                                            <div class="empty-state text-center py-5">
                                                <i class="mdi mdi-check-circle-outline text-success" style="font-size: 64px;"></i>
                                                <h4 class="mt-3">No unlock requests</h4>
                                                <p class="text-muted">There are no unlock requests from schools in your division.</p>
                                            </div>
                                        <?php else : ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>School</th>
                                                            <th>Requested By</th>
                                                            <th>Request Date</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($requests as $index => $request) : ?>
                                                            <?php
                                                            $school = $this->Common->one_cond_row('schools', 'schoolID', $request->school_id);
                                                            $school_name = $school ? $school->schoolName : 'Unknown School';
                                                            $status_class = $request->status == 'pending' ? 'badge-warning' : ($request->status == 'approved' ? 'badge-success' : 'badge-secondary');
                                                            ?>
                                                            <tr>
                                                                <td><?= $index + 1; ?></td>
                                                                <td>
                                                                    <strong><?= html_escape($school_name); ?></strong>
                                                                    <br>
                                                                    <small class="text-muted"><?= html_escape($request->school_id); ?></small>
                                                                </td>
                                                                <td><?= html_escape($request->requested_by); ?></td>
                                                                <td><?= date('M d, Y H:i', strtotime($request->request_date)); ?></td>
                                                                <td>
                                                                    <span class="badge <?= $status_class; ?>">
                                                                        <?= ucfirst(html_escape($request->status)); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if ($request->status == 'pending') : ?>
                                                                        <?php $unlock_action = $request->request_type === 'checklist' ? 'Pages/sbm_checklist_unlock' : 'Pages/sbm_ta_unlock'; ?>
                                                                        <?= form_open($unlock_action, array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to unlock this record?');")); ?>
                                                                        <input type="hidden" name="id" value="<?= (int) ($request->request_type === 'checklist' ? $request->checklist_id : $request->ta_id); ?>">
                                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                                            <i class="mdi mdi-lock-open-outline"></i> Unlock
                                                                        </button>
                                                                        <?= form_close(); ?>
                                                                    <?php elseif ($request->status == 'approved') : ?>
                                                                        <span class="text-muted">
                                                                            <i class="mdi mdi-check"></i> Unlocked
                                                                        </span>
                                                                    <?php else : ?>
                                                                        <span class="text-muted">
                                                                            <i class="mdi mdi-eraser"></i> Cleared
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

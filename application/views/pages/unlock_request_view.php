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
                                            <h5 class="mb-0"><i class="mdi mdi-lock-open-variant-outline"></i> <?= $request->request_type == 'ta' ? 'TA Report' : 'Checklist'; ?> Unlock Request</h5>
                                            <p class="text-muted mb-0">Review the unlock request details below before approving.</p>
                                        </div>

                                        <?php if ($request->status == 'approved') : ?>
                                            <div class="alert alert-success">
                                                <i class="mdi mdi-check-circle"></i> This request has already been approved and the <?= $request->request_type == 'ta' ? 'TA report' : 'checklist'; ?> has been unlocked.
                                            </div>
                                        <?php elseif ($request->status == 'cleared') : ?>
                                            <div class="alert alert-secondary">
                                                <i class="mdi mdi-eraser"></i> This request has been cleared.
                                            </div>
                                        <?php else : ?>
                                            <div class="request-details">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>School Name</label>
                                                            <strong><?= $school ? html_escape($school->schoolName) : 'Unknown School'; ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>School ID</label>
                                                            <strong><?= html_escape($request->school_id); ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>Requested By</label>
                                                            <strong><?= html_escape($request->requested_by); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>Request Date</label>
                                                            <strong><?= date('F d, Y - g:i A', strtotime($request->request_date)); ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>Request Type</label>
                                                            <strong><?= $request->request_type == 'ta' ? 'TA Report' : 'Checklist'; ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label>Status</label>
                                                            <span class="badge badge-warning">Pending</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <label><?= $request->request_type == 'ta' ? 'TA Report ID' : 'Checklist ID'; ?></label>
                                                            <strong><?= html_escape($request->request_type == 'ta' ? $request->ta_id : $request->checklist_id); ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="request-actions mt-4">
                                                <?php if ($request->request_type == 'ta') : ?>
                                                    <?= form_open('Pages/sbm_ta_unlock', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to unlock this TA report? The school will be able to edit it again.');")); ?>
                                                    <input type="hidden" name="id" value="<?= (int) $request->ta_id; ?>">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="mdi mdi-lock-open-outline"></i> Unlock TA Report
                                                    </button>
                                                    <?= form_close(); ?>
                                                <?php else : ?>
                                                    <?= form_open('Pages/sbm_checklist_unlock', array('style' => 'display:inline;', 'onsubmit' => "return confirm('Are you sure you want to unlock this checklist? The school will be able to edit it again.');")); ?>
                                                    <input type="hidden" name="id" value="<?= (int) $request->checklist_id; ?>">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="mdi mdi-lock-open-outline"></i> Unlock Checklist
                                                    </button>
                                                    <?= form_close(); ?>
                                                <?php endif; ?>
                                                <a href="<?= base_url(); ?>Pages/unlock_requests" class="btn btn-secondary btn-lg">
                                                    <i class="mdi mdi-arrow-left"></i> Back to Requests
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .detail-item {
                                margin-bottom: 1.5rem;
                            }
                            .detail-item label {
                                display: block;
                                color: #6c757d;
                                font-size: 0.875rem;
                                font-weight: 500;
                                margin-bottom: 0.5rem;
                            }
                            .detail-item strong {
                                color: #495057;
                                font-size: 1rem;
                            }
                            .request-actions {
                                display: flex;
                                gap: 1rem;
                                padding-top: 1.5rem;
                                border-top: 1px solid #e9ecef;
                            }
                        </style>

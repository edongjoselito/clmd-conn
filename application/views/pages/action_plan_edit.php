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

                                    <?= validation_errors(); ?>
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
                                            <h5 class="mb-0"><i class="mdi mdi-clipboard-edit-outline"></i> Edit Action Plan Item</h5>
                                            <p class="text-muted mb-0">Update the details of this action plan item below.</p>
                                        </div>

                                        <?php 
                                            $attributes = array('class' => 'parsley-examples');
                                            echo form_open_multipart('pages/sbm_action_plan_update', $attributes);
                                        ?>
                                            <input type="hidden" name="id" value="<?= (int) $data->id; ?>">
                                            
                                            <div class="form-section">
                                                <h6 class="form-section-title"><i class="mdi mdi-target"></i> Activity & Objectives</h6>
                                                <div class="row">
                                                    <div class="form-group col-lg-12">
                                                        <label for="activity">Activity <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" rows="3" name="activity" id="activity" required placeholder="Describe the main activity..."><?= html_escape($data->activity); ?></textarea>
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        <label for="objective">Objectives</label>
                                                        <textarea class="form-control" rows="3" name="objective" id="objective" placeholder="What are the key objectives of this activity?"><?= html_escape($data->objective); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-section">
                                                <h6 class="form-section-title"><i class="mdi mdi-bullseye"></i> Outputs & Strategy</h6>
                                                <div class="row">
                                                    <div class="form-group col-lg-6">
                                                        <label for="ex_output">Expected Outputs</label>
                                                        <textarea class="form-control" rows="3" name="ex_output" id="ex_output" placeholder="What tangible results do you expect?"><?= html_escape($data->ex_output); ?></textarea>
                                                    </div>
                                                    <div class="form-group col-lg-6">
                                                        <label for="metho_strategy">Methodology Strategy</label>
                                                        <textarea class="form-control" rows="3" name="metho_strategy" id="metho_strategy" placeholder="How will this activity be implemented?"><?= html_escape($data->metho_strategy); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-section">
                                                <h6 class="form-section-title"><i class="mdi mdi-calendar-clock"></i> Timeline & Resources</h6>
                                                <div class="row">
                                                    <div class="form-group col-lg-4">
                                                        <label for="time_frame">Time Frame</label>
                                                        <input type="text" name="time_frame" id="time_frame" class="form-control" value="<?= html_escape($data->time_frame); ?>" placeholder="e.g., January - March 2024">
                                                    </div>
                                                    <div class="form-group col-lg-4">
                                                        <label for="person_involved">Person Involved</label>
                                                        <input type="text" name="person_involved" id="person_involved" class="form-control" value="<?= html_escape($data->person_involved); ?>" placeholder="Who is responsible?">
                                                    </div>
                                                    <div class="form-group col-lg-4">
                                                        <label for="bud_req">Budgetary Requirement</label>
                                                        <input type="text" name="bud_req" id="bud_req" class="form-control" value="<?= html_escape($data->bud_req); ?>" placeholder="e.g., ₱50,000">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-section">
                                                <h6 class="form-section-title"><i class="mdi mdi-note-text"></i> Additional Notes</h6>
                                                <div class="row">
                                                    <div class="form-group col-lg-12">
                                                        <label for="remarks">Remarks</label>
                                                        <textarea class="form-control" rows="3" name="remarks" id="remarks" placeholder="Any additional notes or comments..."><?= html_escape($data->remarks); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-actions">
                                                <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                                    <i class="mdi mdi-content-save"></i> Update Action Plan
                                                </button>
                                                <a href="<?= base_url('pages/sbm_action_plan'); ?>" class="btn btn-secondary btn-lg">
                                                    <i class="mdi mdi-arrow-left"></i> Cancel
                                                </a>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .card-header-title {
                                border-bottom: 1px solid #e9ecef;
                                padding-bottom: 1rem;
                                margin-bottom: 1.5rem;
                            }
                            .card-header-title h5 {
                                color: #495057;
                                font-weight: 600;
                            }
                            .card-header-title p {
                                color: #6c757d;
                                font-size: 0.9rem;
                            }
                            .form-section {
                                background: #f8f9fa;
                                border-radius: 8px;
                                padding: 1.5rem;
                                margin-bottom: 1.5rem;
                                border: 1px solid #e9ecef;
                            }
                            .form-section-title {
                                color: #495057;
                                font-weight: 600;
                                margin-bottom: 1rem;
                                padding-bottom: 0.5rem;
                                border-bottom: 2px solid #dee2e6;
                            }
                            .form-section-title i {
                                margin-right: 0.5rem;
                                color: var(--llcm-blue, #922c43);
                            }
                            .form-group label {
                                font-weight: 500;
                                color: #495057;
                                margin-bottom: 0.5rem;
                            }
                            .form-control {
                                border-radius: 6px;
                                border: 1px solid #ced4da;
                                padding: 0.75rem;
                            }
                            .form-control:focus {
                                border-color: var(--llcm-blue, #922c43);
                                box-shadow: 0 0 0 0.2rem rgba(146, 44, 67, 0.15);
                            }
                            .form-actions {
                                display: flex;
                                gap: 1rem;
                                padding-top: 1rem;
                                border-top: 1px solid #e9ecef;
                                margin-top: 1rem;
                            }
                            .btn-lg {
                                padding: 0.75rem 1.5rem;
                                font-weight: 500;
                            }
                            .text-danger {
                                color: #dc3545 !important;
                            }
                        </style>

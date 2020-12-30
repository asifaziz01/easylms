<div class="row justify-content-center">
	<div class="col-md-12">
		<div class="card">
			 
			<div class="card-body">
				<?php echo form_open('coaching/tests_actions/create_test/'.$coaching_id.'/'.$course_id.'/'.$test_id, array('class'=>'form-horizontal row-border', 'class'=>'validate-form')); ?>
					<div class="form-row">
						<div class="form-group col-md-6 mb-3">
							<?php echo form_label('Title<span class="required">*</span>', 'title', array('class'=>'control-label')); ?>
							<input type="text" class="form-control required" name="title" id="title" value="<?php echo set_value('title', $results['title']); ?>" />
						</div>
						<div class="form-group col-md-6 mb-3">
							<?php echo form_label('Attempts allowed', 'attempts', array('class'=>'control-label')); ?>
							<select name="num_takes" class="form-control select2-single" id="attempts">
								<option value="0" <?php echo set_select ('num_takes', 0, ($results['num_takes'] == 0)); ?> >Unlimited</option>
								<?php for ($i=1; $i<=10; $i++) { ?>
									<option value="<?php echo $i; ?>" <?php echo set_select ('num_takes', $i, ($results['num_takes'] == $i)); ?> ><?php echo $i; ?></option>                    
								<?php } ?>
							</select>
						</div>
						<div class="form-group col-md-6 mb-3 d-none">
							<?php echo form_label('Category', '', array('class'=>'control-label')); ?>
							<select class="form-control required " name="course">
								<option value="0" <?php if ($course_id==0) echo 'selected="selected"'; ?>>Uncategorized</option>
								<?php
								if (! empty($courses)) {
									foreach ($courses as $course) {
										?>
										<option value="<?php echo $course['course_id']; ?>" <?php if ($course_id == $course['course_id']) echo 'selected="selected"'; ?> ><?php echo $course['title']; ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6 mb-3">
							<?php echo form_label('Passing Percentage<span class="required">*</span>', 'pass_marks', array('class'=>'control-label')); ?>
							<div class="input-group">
								<input name="pass_marks" id="pass_marks" type="number" class="form-control digits required" oninput="maxLengthCheck(this)" maxlength="3" step="1" max="100" min="0" value="<?php echo set_value('pass_marks', $results['pass_marks']); ?>" placeholder="Passing Percentage" aria-label="Passing Percentage(to the nearest dollar)" />
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
						</div>
						<div class="form-group col-md-6 mb-3">
							<?php echo form_label('Test Duration (minutes)<span class="required">*</span>', 'time_min', array('class'=>'control-label')); ?>
							<div class="input-group">
								<input name="time_min" id="time_min" type="number" class="form-control digits required" oninput="maxLengthCheck(this)" maxlength="4" step="1" min="0" value="<?php echo set_value('time_min', $results['time_min']); ?>" placeholder="Minutes" aria-label="Test Duration Minutes" />
								<div class="input-group-append">
                                    <span class="input-group-text">Min</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Enter test duration in minutes. Enter 0 (zero) for no limit</small>
                        </div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6 mb-3">
							<?php echo form_label('Release Results', '', array('class'=>'control-label')); ?>
							<div class="mb-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio1" class="custom-control-input" name="release_result" value="<?php echo RELEASE_EXAM_IMMEDIATELY; ?>" <?php if (! isset($results['release_result']) || $results['release_result'] == RELEASE_EXAM_IMMEDIATELY) echo 'checked="checked"'; ?> >
                                    <label class="custom-control-label" for="customRadio1">Immediately after test submissions </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio2" class="custom-control-input" name="release_result" value="<?php echo RELEASE_EXAM_NEVER; ?>" <?php if (isset($results['release_result']) && $results['release_result'] == RELEASE_EXAM_NEVER) echo 'checked="checked"'; ?>>
                                    <label class="custom-control-label" for="customRadio2">Manually at a later date</label>
                                </div>
                            </div>
						</div>
					</div>
					<p class="btn-toolbar border-top pt-3 mb-0">
						<input type="hidden" name="test_type" value="<?php echo TEST_TYPE_PRACTICE; ?>" />
						<input type="submit" name="sub" value="<?php echo ('Save'); ?>" class="btn btn-primary " accesskey="s" />
					</p>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php echo form_open('coaching/courses_actions/create_edit_action/' . $coaching_id . '/' . $cat_id . '/' . $course_id, array('class' => 'card', 'id' => 'validate-1')); ?>
			<div class="card-body">
				<div class="form-group">
					<label for="title">Title</label>
					<input type="text" class="form-control" id="title" name="title" placeholder="Title of the Course"<?php echo (isset($course['title'])) ? ' value="' . $course['title'] . '"' : ' '; ?>/>
				</div>
				
				<div class="form-group">
					<label for="description">Description</label>
					<textarea class="form-control tinyeditor" id="description" name="description" rows="4" placeholder="How you describe this course?"><?php echo (isset($course['description'])) ? $course['description'] : ''; ?></textarea>
				</div>

				<div class="form-group">
					<label for="curriculum">Curriculum</label>
					<textarea class="form-control tinyeditor" id="curriculum" name="curriculum" rows="4" placeholder="Curriculum of this course?"><?php echo (isset($course['curriculum'])) ? $course['curriculum'] : ''; ?></textarea>
				</div>

				<div class="form-group">
					<label for="curriculum">Category</label>
					<select class="form-control select2-single" name="cat_id" id="cat_id">
						<option value="null"<?php echo (isset($course['cat_id']) && $course['cat_id'] == 0 || $cat_id == -1 || $cat_id == 0)?' selected':null; ?>>Uncategorized</option>
						<?php foreach($categories as $category): ?>
						<option value="<?php echo $category['cat_id']; ?>"<?php echo ((isset($course['cat_id']) && ($course['cat_id'] == $category['cat_id'])) || $cat_id == $category['cat_id'])?' selected':null; ?>><?php echo $category['title']; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="batch-type">Course Type</label>
					<div class="custom-control custom-radio<?php echo ($course_id > 0)?' pl-0':null; ?>">
						<?php
						echo ($course_id > 0) ? null : form_radio(
							array(
								'name'=>'enrolment_type',
								'id'=>'enrolment_direct',
								'class'=>'custom-control-input',
								'value'=>COURSE_ENROLMENT_DIRECT,
								'checked'=>set_radio(
									'enrolment_type',
									COURSE_ENROLMENT_DIRECT,
									(isset($course['enrolment_type']) &&  $course['enrolment_type'] == COURSE_ENROLMENT_DIRECT)
								)
							)
						);
						echo ($course_id > 0) ? 
							(isset($course['enrolment_type']) &&  $course['enrolment_type'] == COURSE_ENROLMENT_DIRECT) ? '<strong>Direct Enrolment</strong>' : 'Direct Enrolment'
							: form_label(
							'Direct Enrolment',
							'enrolment_direct',
							array(
								'class'=>'custom-control-label'
							)
						);
						?>
						<div class="text-muted">Students can enrol anytime and from anywhere</div>
					</div>
					<div class="custom-control custom-radio<?php echo ($course_id > 0)?' pl-0':null; ?>">
						<?php
						echo ($course_id > 0) ? null : form_radio(
							array(
								'name'=>'enrolment_type',
								'id'=>'enrolment_batch',
								'class'=>'custom-control-input',
								'value'=>COURSE_ENROLMENT_BATCH,
								'checked'=>set_radio(
									'enrolment_type',
									COURSE_ENROLMENT_BATCH,
									(isset($course['enrolment_type']) &&  $course['enrolment_type'] == COURSE_ENROLMENT_BATCH)
								)
							)
						);
						echo ($course_id > 0) ? 
							(isset($course['enrolment_type']) &&  $course['enrolment_type'] == COURSE_ENROLMENT_BATCH) ? '<strong>Batch Enrolment</strong>' : 'Batch Enrolment'
							: form_label(
							'Batch Enrolment',
							'enrolment_batch',
							array(
								'class'=>'custom-control-label'
							)
						);
						?>
						<div class="text-muted">Students can enrol in available batch </div>
					</div>
				</div>

				<div class="form-group">
					<label for="price">Price</label>
					<div class="input-group mb-2">
						<input type="number" class="form-control" id="price" name="price" min="0" step="1" placeholder="Course Price"<?php echo (isset($course['price'])) ? ' value="' . $course['price'] . '"' : ' '; ?>/>
						<div class="input-group-append">
							<div class="input-group-text">.00</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label>Feauted Image</label>
					<div class="custom-file mb-3">
						<input type="file" class="custom-file-input" id="feat_img" name="feat_img" accept="image/*" />
						<label class="custom-file-label" for="feat_img">Select file to upload...</label>
					</div>
					<?php if (isset($course['feat_img'])) :?>
					<div>
						<img src="<?php echo site_url($course['feat_img']); ?>" class="img-fluid" style="width: 128px;" />
					</div>
					<?php endif;?>
				</div>

			</div>
			<div class="card-footer">
				<input type="submit" name="submit" class="btn btn-primary" value="<?php echo $submit_label; ?>" data-toggle="tooltip" data-placement="right" title="<?php echo $submit_title; ?>">
			</div>
		<?php echo form_close(); ?>
	</div>
</div>
<div class="row mb-4">
	<!-- Prepare -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary">
                	<span><i class="simple-icon-notebook pr-3"></i></span>Prepare <span class="badge badge-primary float-right"></span>
                </h5>
                <div class="separator mb-5"></div>
                <div class="card-body p-0 align-self-center justify-content-between min-width-zero align-items-md-center">
	                <a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/tests/questions/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>">
	                    <i class="simple-icon-question heading-icon"></i>
	                    <span class="align-middle d-inline-block">Questions</span>
	                </a>
					<?php if ($test['finalized'] == 0) { ?>
						<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/tests/question_group_create/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>">
							<i class="simple-icon-plus heading-icon"></i>
	        	            <span class="align-middle d-inline-block">Add Questions</span>
						</a>
						<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/tests/upload_questions/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>" >
							<i class="simple-icon-plus heading-icon"></i>
		                    <span class="align-middle d-inline-block">Upload Questions</span>
						</a>
					<?php } else { ?>
						<div class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block text-muted" >
							<i class="simple-icon-plus heading-icon"></i>
		                    <span class="align-middle d-inline-block">Add Questions</span>
						</div>
					<?php } ?>
                
	            </div>
            </div>
        </div>
    </div>
    
    <!-- Publish -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary">
                	<span><i class="simple-icon-note pr-3"></i></span>Publish <span class="badge badge-primary float-right"></span>
                </h5>
                <div class="separator mb-5"></div>
                <div class="card-body p-0 align-self-center justify-content-between min-width-zero align-items-md-center">
				<?php if ($test['finalized'] == 0) { ?>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="javascript:void(0)" onclick="javascript:show_confirm ('Publish this test?', '<?php echo site_url('coaching/tests_actions/finalise_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>')" title="Publish Test">
	                    <i class="simple-icon-cloud-upload heading-icon"></i>
	                    <span class="align-middle d-inline-block">Publish</span>
	                </a>
					<?php } else { ?>
						<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="javascript:void(0)" onclick="javascript:show_confirm ('Un-publish this test? Test will not be available to users after un-publish.', '<?php echo site_url('coaching/tests_actions/unfinalise_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>')" title="Un-Publish Test">
	                    <i class="simple-icon-cloud-download heading-icon"></i>
	                    <span class="align-middle d-inline-block">Un-Publish</span>
	                </a>
					<?php } ?>
	                <a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/tests/preview_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>" title="Preview Test">
	                    <i class="simple-icon-eye heading-icon"></i>
	                    <span class="align-middle d-inline-block">Preview</span>
	                </a>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-none" href="<?php echo site_url ('coaching/tests/print_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>" target="_blank" title="Print Test">
						<i class="simple-icon-printer heading-icon"></i>
						<span class="align-middle d-inline-block">Print</span>
					</a>
		      		<a class="list-item-heading mb-0 truncate w-100 mt-0 d-none" href="<?php echo site_url ('coaching/tests_actions/export_pdf/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>" target="_blank" title="Export As PDF">
					   <i class="simple-icon-arrow-up-circle heading-icon"></i>
					   <span class="align-middle d-inline-block">Export</span>
					</a>
                
	            </div>
            </div>
        </div>
    </div>
	<!-- Submissions -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary">
                	<span><i class="simple-icon-check pr-3"></i></span>Submissions <span class="badge badge-primary float-right"></span>
                </h5>
                <div class="separator mb-5"></div>
                <div class="card-body p-0 align-self-center justify-content-between min-width-zero align-items-md-center">
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/reports/submissions/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>">
	                    <i class="simple-icon-check heading-icon"></i>
	                    <span class="align-middle d-inline-block">Submissions</span>
	                </a>
	                <?php if ($test['release_result'] == RELEASE_EXAM_NEVER) : ?>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="javascript:void(0)" onclick="javascript:show_confirm ('Release test results for users?', '<?php echo site_url('coaching/tests_actions/release_result/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>')" title="">
			      		<i class="simple-icon-graduation heading-icon"></i>
	                    <span class="align-middle d-inline-block">Release Result</span>
			      	</a>
			      	<?php endif; ?>
	            </div>
            </div>
        </div>
    </div>

	<!-- Setting -->

	<div class="col-md-6 mb-4">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title text-primary">
					<span><i class="simple-icon-settings pr-3"></i></span>Setting
				</h5>
				<div class="separator mb-5"></div>
				<div class="card-body p-0 align-self-center justify-content-between min-width-zero align-items-md-center">
					<?php if ($test['finalized'] == 0): ?>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="<?php echo site_url ('coaching/tests/edit/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>" title="Edit Test">
						<i class="simple-icon-pencil heading-icon"></i>
						<span class="align-middle d-inline-block">Edit</span>
					</a>
					<?php endif; ?>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="javascript:void(0)" onclick="javascript:show_confirm ('This will delete all questions in this test. Continue?', '<?php echo site_url('coaching/tests_actions/reset_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>')" title="Reset Test">
						<i class="simple-icon-wrench heading-icon"></i>
						<span class="align-middle d-inline-block">Reset</span>
					</a>
					<a class="list-item-heading mb-0 truncate w-100 mt-0 d-inline-block" href="javascript:void(0)" onclick="javascript:show_confirm ('Are you sure you want to delete this test?', '<?php echo site_url('coaching/tests_actions/delete_test/'.$coaching_id.'/'.$course_id.'/'.$test_id); ?>')" title="Delete Test">
						<i class="simple-icon-trash heading-icon"></i>
						<span class="align-middle d-inline-block">Delete</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>



<style>
@media (max-width: 1439px){
	.test-mode main.default-transition {
	    margin-top: 40px !important;
	}
}
@media (max-width: 1199px){
	.test-mode main.default-transition {
	    margin-top: 30px !important;
	}
}
@media (max-width: 1199px){
	.test-mode main.default-transition {
	    margin-top: 30px !important;
	}
}
@media(max-width: 767px){
	.test-mode main.default-transition {
	    margin-top: 20px !important;
	}
}
</style>

<h4><?php echo $test['title']; ?></h4>

<div class="card mt-2">
	<div class="card-header text-center">
		<div class="d-flex justify-content-end">
			<div class="p-2">
				<div id="hours"><?php echo gmdate("H", $test_duration); ?></div>
				<div class="mb-0">HOURS</div>
			</div>
			<div class="p-2">
				<div id="minutes"><?php echo gmdate("i", $test_duration); ?></div>
				<div class="mb-0">MINUTES</div>
			</div>
			<div class="p-2">
				<div id="seconds"><?php echo gmdate("s", $test_duration); ?></div>
				<div class="mb-0">SECONDS</div>
			</div>
		</div>
	</div>
</div>

<?php echo form_open_multipart('student/tests_actions/submit_test/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$test_id, array('id'=>'test_form', 'name'=>'forms', 'enctype'=>'multipart/form-data', 'class' => 'h-100') ); ?>
	<div class="row" id="question-block">
		<div class="col-md-12">
			<?php 
			if ( ! empty ($results)) {
				$count_tabs = 1;
				$num_question = 0;
				$num_heading  = 0;
				
				foreach ( $results as $parent_id=>$all_questions) {
					$parent 	= $all_questions['parent'];
					$questions 	= $all_questions['questions'];

					$num_heading++;

					if ( ! empty($questions)) {
						foreach ($questions as $id=>$row) {
							$num_question++;
							?>
							<div style="display: none" id="page<?php echo $num_question; ?>" class="pages page<?php echo $num_question; ?> " >
				
								<div class="card card-default paper-shadow ">
									<div class="card-header justify-content-between">
										<strong>Question <?php echo $num_question; ?> of <?php echo $total_questions; ?></strong>
									</div>
									<div class="card-body ">
										<?php echo $parent['question']; ?>
										<div class="separator"></div>	
										<?php echo $row['question']; ?>
										<?php
											$data['num'] = $num_question; 
											$data['row'] = $row; 
											$this->load->view ('include/answer_choices', $data);
										?> 
									</div>									
								</div>
							</div>
							<?php 
						} // end foreach question_id
					} // end foreach question
					$count_tabs++;
				}
			}
			$confirm_div = $num_question + 1;
			?> 

			<div id="page<?php echo $confirm_div; ?>" class="mt-4 pages page<?php echo $confirm_div; ?>" >
				<div class="card border-danger paper-shadow">
					<div class="card-header">
						<h4>Test Complete</h4>
					</div>
					<div class="card-body">
						<p class="text-danger">No more questions in this test. You can review your questions or sumbit the test.</p>
					</div>
					<div class="card-footer d-flex justify-content-between">
						<button type="button" class="btn btn-danger" onclick="show_first ();">REVIEW TEST</button>
					</div>
				</div>
			</div>

			<input type="hidden" id="num_question" value="1">
			<input type="hidden" id="num-answered" value="0">
			<input type="hidden" id="num-not-answered" value="0">
			<input type="hidden" id="num-for-review" value="0">
			<input type="hidden" id="num-not-visited" value="0">
			<input type="hidden" id="attempt_id" name="attempt_id" value="<?php echo $attempt_id; ?>">
		</div>
	</div>

	<div class="row">	
		<div class="col-md-12">
			<div class="card mt-2" id="quick-links">
		        <div class="card-header">
		            <h4 class="card-title category">Questions</h4>
		        </div>
		    	<div class="card-body max-height-150 height-150-lg overflow-auto">
		    		<?php
		    			$count_tabs = 1;
		    			$y = 0;
	    				for ($i=1; $i<=$num_question; $i++) {
    						$y++;
	    					if (strlen ($y) < 2) {
    							$y_text = '0'.$y;
    						} else {
    							$y_text = $y;
    						}
    						?>
    						<a class="btn btn-sm btn-secondary text-white" href="javascript:void(0)" onclick="display_question (<?php echo $y; ?>)" style="margin-top:5px" id="btn_<?php echo $y; ?>"><?php echo $y_text; ?></a> 
    						<?php
		    			}
		    		?>
		    	</div>
			</div>
		</div>
	</div>

	<div class="card fixed-bottom mt-4">
		<div class="card-body">
			<div class=" d-flex justify-content-between ">				
				<input type="submit" name="submitBtn" id="submit-test" value="Submit Test" class="btn btn-success btn-sm submit-button" >
				<div id="test-nav-buttons">
					<button type="button" class="btn btn-primary btn-sm previous d-none"><i class="fa fa-arrow-left"></i> Previous </button>
					<button type="button" class="btn btn-primary btn-sm next">Next <i class="fa fa-arrow-right"></i> </button>
				</div>
			</div>
		</div>
	</div>

	<div class="card bg-danger pages text-white mt-4">
		<div class="card-body">
			<p class="font-weight-bold">Time is up</p>
			<p class="">Press SUBMIT button to submit test. Please not that test will not be evaluated/checked if not submitted</p>
		</div>
	</div>


<?php echo form_close(); ?>
<?php 
//FUNCTION FOR SHUFFLE THE OPTION OF QUESTION 
function shuffle_assoc($list) { 
	if (!is_array($list)) {
		return $list; 
	}
	$keys = array_keys($list); 
	shuffle($keys); 
	$random = array(); 
	foreach ($keys as $key) { 
		$random[$key] = $list[$key]; 
	}
	return $random; 
}
?>
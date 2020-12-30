<div class="row">
	<div class="col-12 list">
		<?php echo form_open ('coaching/tests_actions/save_upload_questions/'.$coaching_id.'/'.$course_id.'/'.$test_id, ['class'=>'form-vertical']); ?>
			<?php
			$heading_count = 1;
			$marks = 1;
			if (! empty($stack)) {
				foreach ($stack as $heading_stack) {
					$heading = $heading_stack['heading'];
					$question_stack = $heading_stack['questions'];
					$num_headings = count ($heading_stack);
					$num_questions = count ($question_stack);
					?>
                    <div class="card mb-3">
                        <div class="d-flex flex-grow-1 min-width-zero">
                            <label class="mb-1 align-self-center ml-4 d-none">
                                <?php echo $heading_count; ?>
                            </label>
                            <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
                                <div class="mb-0 w-80" >
									<textarea class="form-control" name="headings[]"><?php echo $heading; ?></textarea>
                                </div>
                                <div class="w-15 w-xs-100">
                                    <span class="badge badge-pill badge-secondary"></span>
                                </div>
                            </div>
                        </div>
                    
						<?php
						$question_count = 1;
						if (! empty($question_stack)) {
							foreach ($question_stack as $questions) {
								
								$question = $questions['question'];

								if (isset ($questions['choices'])) {
									$choice_stack = $questions['choices'];
									$num_choices = count($choice_stack);
								} else {
									$choice_stack = [];									
									$num_choices = 0;
								}

								if (isset ($questions['answers'])) {
									$correct_answers = $questions['answers'];
									$num_answers = count($correct_answers);
								} else {
									$correct_answers = [];
									$num_answers = 0;
								}


								/* Rules and Conditions
									if no answer choices, question-type = long_answer
									if two answer choices, question-type = true-false
									if three or more answer choices, question-type = single-choice
									if more than one correct answers, question-type = multi-choice
									if answer choices are given, atleast one correct answer should be given

									if none of the above conditions match, mark question as faulty
								*/

								$type = 0;
								$has_error = false;
								$err_msg = '';
								$type_stack = [0=>'Error', QUESTION_LONG=>'Long Answer', QUESTION_MCSC=>'Multi Choice', QUESTION_MCMC=>'Multi Correct', QUESTION_TF=>'True/False'];

								if ($num_choices == 0) {
									$type = QUESTION_LONG;
								} else if ($num_choices == 2) {
									$type = QUESTION_TF;
								} else if ($num_choices >=3) {
									$type = QUESTION_MCSC;
								}


								if ($num_choices == 0 && $num_answers == 0) {
									// This should be a long-answer type question
									$type = QUESTION_LONG;
								} else if ($num_choices == 1 && $num_answers == 0) {
									$has_error = true;
									$err_msg = 'Invalid question format. Question will be saved as "Long Answer" type';									
								} else if ($num_choices > 1 && $num_answers == 0) {
									$has_error = true;
									$err_msg = 'Select a correct answer';
								} else if ($num_choices == 2 && $num_answers == 1) {
									// This should be true-false type question
									$type = QUESTION_TF;
								} else if ($num_choices >= 3 && $num_answers == 1) {
									// This should be true-false type question
									$type = QUESTION_MCSC;
								} else if ($num_choices >= 3 && $num_answers > 1) {
									// This should be true-false type question
									$type = QUESTION_MCMC;
								} else {
									$has_error = true;
									$err_msg = 'Unkown question format. Question will be saved as "Long Answer" type';
								}
								?>
								<div class="<?php if ($has_error == true) echo 'bg-warning'; ?>">									
			                        <div class="d-flex flex-grow-1 min-width-zero">
			                            <label class=" mb-1 align-self-center ml-4">
			                            	<?php echo $question_count; ?>
			                            </label>
			                            <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
			                                <div class="mb-0 w-70 w-xs-100" >
												<textarea class="form-control" name="questions[<?php echo $question_count; ?>]"><?php echo $question; ?></textarea>
			                                </div>
			                                <p class="mb-0 text-small w-10 w-xs-100">
			                                	<input type="number" name="marks[<?php echo $question_count; ?>]" value="<?php echo $marks;?>" class="form-control" min="1"> <?php echo ' marks'; ?>
			                                </p>
			                                <div class="w-10 w-xs-100">
			                                	<input type="hidden" name="type[<?php echo $question_count; ?>]" value="<?php echo $type;?>"> 

			                                    <span class="badge badge-pill <?php if ($has_error == true) echo 'badge-danger'; else echo 'badge-secondary'; ?>">
			                                    	<?php echo $type_stack[$type]; ?>
			                                    </span>
			                                </div>
			                            </div>
			                        </div>
			                        <?php
									if  (! empty($choice_stack)) {
										foreach ($choice_stack as $i=>$choice) {
											?>
											<div class="d-flex flex-grow-1 min-width-zero">
					                            <label class=" mb-1 align-self-center ml-4">
					                            </label>
					                            <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
					                                <p class="mb-0 mr-2">
					                                	<?php if ($num_answers > 1) { ?>
	      													<input type="checkbox" name="answers[<?php echo $question_count; ?>][<?php echo $i; ?>]" <?php if (in_array($i, $correct_answers)) { echo 'checked="checked" value="'.$i.'"'; }  ?> >
					                                	<?php } else { ?>
	      													<input type="radio" name="answers[<?php echo $question_count; ?>]" <?php if (in_array($i, $correct_answers)) { echo 'checked="checked" value="'.$i.'"'; }  ?> >
					                                	<?php } ?>
					                            	</p>
					                                <div class="mb-0 w-100" >
														<textarea class="form-control" name="choices[<?php echo $question_count; ?>][<?php echo $i; ?>]"><?php echo $choice; ?></textarea>
					                                </div>
					                            </div>
					                        </div>	
											<?php
										}
									}
									?>
									<p class="ml-4 text-danger">
										<?php if ($has_error == true) echo $err_msg; ?>
									</p>
								</div>
							<?php
							$question_count++;
							}
						}
						?>
					</div>
				<?php
				$heading_count++;
				}
			}
		?>

		<div>
			<p class="mx-auto text-danger text-center">
				<?php if ($has_error == true) echo 'Errors have been detected in some questions. You can either fix the errors as per comment or re-upload questions in correct format'; ?>					
			</p>
			<input type="submit" name="submit" value="Save Questions" class="btn btn-primary" disabled>
		</div>
		<?php echo form_close (); ?>
	</div>
</div>
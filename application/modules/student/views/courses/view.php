<div class="row">
	<div class="col-12 col-md-12 col-xl-8 col-left">
		<div class="card mb-4">
		    <div class="lightbox">
			    <?php if( is_file($course['feat_img'])): ?>
			    	<a href="<?php echo site_url( $course['feat_img'] ); ?>">
			    		<img src="<?php echo site_url( $course['feat_img'] ); ?>" class=" border-0 card-img-top mb-3" style="max-height:250px; width: 100%" />
			    	</a>
			    <?php else: ?>
			    	<a href="<?php echo site_url('contents/system/default_course.jpg'); ?>">
			    		<img src="<?php echo site_url('contents/system/default_course.jpg'); ?>" class=" border-0 card-img-top mb-3"  style="max-height:250px; width: 100%"/>
			    	</a>
				<?php endif; ?>
		    </div>
		    <div class="card-body">
				<h3 class="text-center text-primary card-title"><?php echo $course['title']; ?></h3>
    			<div class="separator mb-5"></div>

	    		<div class="description ">
		        	<?php echo ($course['description']); ?>
		        </div>

       			<h3 class="mr-auto ml-auto mb-5">
       				<span class="badge badge-danger">Price: &#8377; <?php echo $course['price']; ?></span>
       			</h3>

			    <?php if ($course['curriculum']): ?>
				    <div class="mb-5">
	        			<h4 class="card-title text-center text-primary">Curriculum</h4>
	        			<div class="separator mb-5"></div>
				    	<?php echo $course['curriculum']; ?>
				    </div>
				<?php endif; ?>

           		<?php if ($enroled == true) { ?>
           			<p class="text-success text-center">You are enroled in this course</p>
        			<div class="text-center mb-5">
            			<a class="btn btn-success btn-block btn-lg shadow-sm" href="<?php echo site_url('student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>">Course Home</a>
            		</div>
           		<?php } else { ?>
	            	<?php if ($course['enrolment_type'] == COURSE_ENROLMENT_DIRECT) { ?> 
            			<div class="text-center mb-5">
		       				<a class="btn btn-secondary btn-lg  mt-2 mr-2" href="<?php echo site_url('student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Take A Demo</span></a>
		       				<a class="btn btn-primary btn-lg mt-2 " href="<?php echo site_url('student/courses/buy_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Enrol Now</span></a>
		       			</div>
	            	<?php } else { ?>
						<?php if ( ! empty ($batches)) { ?>
					    	<h4 class="text-center card-title">Upcoming Batches</h4>
		        			<?php 
		        			foreach ($batches as $batch) { 
								$seats = $batch['max_users'] - $batch['num_users'];
								if ($seats > 0) {
		        					?>
			        				<div class="d-flex flex-row mb-3 pb-3 border-bottom">
			                            <div class="pl-3 w-80">
			                                <p class="font-weight-medium mb-0 ">
			                                	<?php echo $batch['batch_name']; ?><br>
			                                    <?php
												if ($seats <= 5) {
													echo '<span class="text-danger text-small">Hurry! Only '.$seats.' seats left</span>';
												}
												?>
			                                </p>
			                                <p class="text-muted mb-0 text-small">
				                                Starting from <?php echo date ('d M, Y', $batch['start_date']); ?>
			                                </p>
			                                <p class="text-muted mb-0 text-small">
				                                Ending on <?php echo date ('d M, Y', $batch['end_date']); ?>
			                                </p>
			                            </div>
			                            <div class="mt-2">
			                            	<?php
											if ($batch['start_date'] <= $today) {
												echo '<span class="text-success text-small">Ongoing Batch !</span>';
											}
			                            	?>
			                                <?php if ($seats > 0) { ?>
					            				<a class="btn btn-primary btn-xs" href="<?php echo site_url('student/courses_actions/buy_course/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch['batch_id']); ?>">Enrol Now</a>
											<?php } ?>
										</div>
			                        </div>
								<?php 
								}
							}
							?>
						<?php } else { ?>
							<div class="text-danger">There are no new batches in this course right now. Visit this page again after some time</div>
						<?php } ?>
						<div class="text-center mt-5">
		       				<a class="btn btn-secondary btn-lg mr-auto ml-auto " href="<?php echo site_url('student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Take A Demo</span></a>
		       			</div>
					<?php } ?>
	           	<?php } ?>
		    </div>
		</div>

		<?php if ( ! empty ($lessons)): ?>
			<div class="card mb-4">
	            <div class="card-body">
	            	<div class="d-flex justify-content-between mb-2">
            			<h4 class="card-title mb-0"><span class="d-inline-block">Chapters</span></h4>
	           			<small class="badge badge-primary ml-1"><?php echo count($lessons); ?></small>
	            	</div>

	                <div class="separator mb-2"></div>

                	<?php foreach ($lessons as $i => $row): ?>
						<div class="d-flex pb-0">
							<div class="flex-grow-1 pr-3 my-auto">
								<div class="">
									<div class="text-small text-muted">Chapter <?php echo $i+1; ?></div>
									 <div class="font-weight-medium mb-0 "><?php echo $row['title']; ?></div>
								</div>
							</div>
							<div class="flex-shrink-0 my-auto">
								<?php $c_duration = $row['duration'];

								if($c_duration != 0){ ?>

									<div class="course-duration">
									<?php echo $row['duration']; ?>
									<?php 
	                            	if ($row['duration_type'] == LESSON_DURATION_MIN) {
	                            		echo ' mins';
	                            	} else if ($row['duration_type'] == LESSON_DURATION_HOUR) {
	                            		echo ' hours';
	                            	} else if ($row['duration_type'] == LESSON_DURATION_WEEK) {
	                            		echo ' weeks';
	                            	}
	                            	?>
	                            	<?php if ($row['for_demo'] == 1) { ?>
										<a href="<?php echo site_url ('student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id.'/'.$row['lesson_id']); ?>" class=""><i class="fa fa-search"></i></a>
	                            	<?php } else { ?>
	                            		<span><i class="fa fa-lock"></i></span>
	                            	<?php } ?>
	                            </div>

								 <?php } ?>
								
							</div>
						</div>
						<div class="separator mb-1"></div>
					<?php endforeach; ?>
	            </div>
	        </div>
		<?php endif; ?>

		<?php if ( ! empty ($tests)): ?>
			<div class="card mb-4">
	            <div class="card-body">
	            	<div class="d-flex justify-content-between mb-2">
            			<h4 class="card-title mb-0"><span class="d-inline-block">Tests</span></h4>
            			<small class="badge badge-primary ml-1"><?php echo count($tests); ?></small>
	            	</div>
	            	
	                <div class="separator mb-2"></div>

                	<?php foreach ($tests as $i => $row): extract($row); ?>
						<div class="d-flex pb-0">
							<div class="flex-grow-1 pr-3 my-auto">
								<div class="">
									<div class="text-small text-muted">Test <?php echo $i+1; ?></div>
									<div class="font-weight-medium mb-0 "><?php echo $row['title']; ?></div>
								</div>
							</div>
							
							<div class="flex-shrink-0 my-auto">
								<span><?php echo "$time_min Min"; ?></span>
	                           	<?php if ($row['for_demo'] == 1) { ?>
	                           		<a href="<?php echo site_url ('student/tests/take_test/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id.'/'.$row['test_id']); ?>" class=""><i class="fa fa-search"></i></a>
                            	<?php } else { ?>
                            		<span><i class="fa fa-lock"></i></span>
                            	<?php } ?>
							</div>
						</div>
						<div class="separator mb-1"></div>
					<?php endforeach; ?>
	            </div>	           
	        </div>
		<?php endif; ?>
		
	</div>

	<div class="col-12 col-md-12 col-xl-4 col-right">
    	<div class="card mb-4">
    		<div class="card-body">
    			<div class="d-flex mb-3">
            		<div class="flex-grow-1 my-auto">
            			<h4 class="card-title text-primary mb-0">Enrol In Course</h4>
            		</div>
            		<div class="flex-shrink-0 my-auto">
            			<h4 class="card-title text-primary mb-0">
		                	<span class="d-inline-block position-relative">
		                		<i class="iconsminds-conference fa-2x"></i>
		                	</span>
		                </h4>
            		</div>
            	</div>
            	<div class="separator mb-3"></div>
           		<?php if ($enroled == true) { ?>
           			<p class="text-success text-center">You are enroled in this course</p>
        			<div class="text-center mb-5">
            			<a class="btn btn-success btn-block btn-lg shadow-sm" href="<?php echo site_url('student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>">Course Home</a>
            		</div>
           		<?php } else { ?>
	            	<?php if ($course['enrolment_type'] == COURSE_ENROLMENT_DIRECT) { ?> 
            			<div class="text-center mb-5">
		       				<a class="btn btn-secondary btn-lg  mr-2" href="<?php echo site_url('student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Take A Demo</span></a>
		       				<a class="btn btn-primary btn-lg mt-2 " href="<?php echo site_url('student/courses_actions/buy_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Enrol Now</span></a>
		       			</div>
	            	<?php } else { ?>
						<?php if ( ! empty ($batches)) { ?>
					    	<h4 class="text-center card-title">Upcoming Batches</h4>
		        			<?php 
		        			foreach ($batches as $batch) { 
								$seats = $batch['max_users'] - $batch['num_users'];
								if ($seats > 0) {
		        					?>
			        				<div class="d-flex flex-row mb-3 pb-3 border-bottom">
			                            <div class="pl-3 w-80">
			                                <p class="font-weight-medium mb-0 ">
			                                	<?php echo $batch['batch_name']; ?><br>
			                                    <?php
												if ($seats <= 5) {
													echo '<span class="text-danger text-small">Hurry! Only '.$seats.' seats left</span>';
												}
												?>
			                                </p>
			                                <p class="text-muted mb-0 text-small">
				                                Starting from <?php echo date ('d M, Y', $batch['start_date']); ?>
			                                </p>
			                                <p class="text-muted mb-0 text-small">
				                                Ending on <?php echo date ('d M, Y', $batch['end_date']); ?>
			                                </p>
			                            </div>
			                            <div class="mt-2">
			                            	<?php
											if ($batch['start_date'] <= $today) {
												echo '<span class="text-success text-small">Ongoing Batch !</span>';
											}
			                            	?>
			                                <?php if ($seats > 0) { ?>
					            				<a class="btn btn-primary btn-xs" href="<?php echo site_url('student/courses_actions/buy_course/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch['batch_id']); ?>">Enrol Now</a>
											<?php } ?>
										</div>
			                        </div>
								<?php 
								}
							}
							?>
						<?php } else { ?>
							<div class="text-danger">There are no new batches in this course right now. Visit this page again after some time</div>
						<?php } ?>
						<div class="text-center mt-5">
		       				<a class="btn btn-secondary btn-lg mr-auto ml-auto " href="<?php echo site_url('student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id); ?>"><span>Take A Demo</span></a>
		       			</div>
					<?php } ?>
	           	<?php } ?>
			</div>
    	</div>
		

		<?php if ( ! empty ($teachers)): ?>
			<div class="card mb-4">
	            <div class="card-body">
	            	<div class="d-flex justify-content-between mb-3">
            			<h4 class="card-title text-primary mb-0">Instructors</h4>
            			<small class="badge badge-primary ml-1"><?php echo count($teachers); ?></small>
	            	</div>
	            	
	                <div class="separator mb-5"></div>

	                <div class="scroll" style="height: 270px;">
	                	<?php foreach ($teachers as $i => $row): extract($row); ?>
	                		<div class="d-flex pb-2">
	                			<span class="d-inline-block mr-2">
			                		<i class="simple-icon-user heading-icon"></i>
			                	</span>
								<div class="flex-grow-1 pt-1">
									<div class="text-small text-muted"></div>
									<div class="font-weight-medium mb-0 "><?php echo "$first_name $last_name"; ?></div>
									<span><?php //echo "$email"; ?></span>
								</div>
								
								<div class="flex-shrink-0 my-auto">
								</div>
							</div>
							
							<div class="separator mb-3"></div>
						<?php endforeach; ?>
	                </div>
	            </div>
	        </div>
		<?php endif; ?>
	</div>
</div>

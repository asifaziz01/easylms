<div class="row">
	<div class="col-12">	
		<?php 
		$i=1;
		if (! empty ($class)) {
			foreach($class as $row) { 
				?>
				<div class="card d-flex flex-row mb-3">
                    <div class="d-flex flex-grow-1 min-width-zero">
                        <div
                            class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
                            <p class="list-item-heading mb-0 truncate w-40 w-xs-100" >
                                <?php echo $row['class_name']; ?>
                            </p>
                            <p class="mb-0 text-muted text-small w-15 w-xs-100 d-none">
								<?php
								if ($row['start_date'] > 0) {
									echo 'Start Date: '. date ('d F, Y', $row['start_date']);
									echo ' at  '. date ('h:i A', $row['start_date']);
								}
								?>
								<?php
								if ($row['end_date'] > 0) {
									echo 'End Date: '. date ('d F, Y', $row['end_date']);
									echo ' at  '. date ('h:i A', $row['end_date']);
								}
								?>
							</p>
                            <div class="w-15 w-xs-100">
                            	<?php if ($row['running'] == 'true') { ?>
									<span class="badge badge-primary">Class has started</span>
								<?php } else { ?>
									<span class="badge badge-light">Class not started</span>
								<?php } ?>
                            </div>
                            <p class="">
                        </div>
                        <div class=" mb-1 align-self-center pr-4">
                            	<?php 
								if ($row['running'] == 'true') {
									echo anchor ('student/virtual_class/join_class/'.$coaching_id.'/'.$row['class_id'].'/'.$member_id.'/'.$row['course_id'].'/'.$row['batch_id'], '<i class="fa fa-plus"></i> Join ', ['class'=>'btn btn-primary mr-1']);
								} else {
									echo anchor ('student/virtual_class/join_class/'.$coaching_id.'/'.$row['class_id'].'/'.$member_id.'/'.$row['course_id'].'/'.$row['batch_id'], '<i class="fa fa-plus"></i> Join ', ['class'=>'btn btn-outline-primary mr-1']);
								}
								if ($row['recording_for_students'] == 'true') {
									echo anchor ('student/virtual_class/recordings/'.$coaching_id.'/'.$row['class_id'].'/'.$member_id, '<i class="fa fa-play"></i> Recordings', ['class'=>'btn btn-link link-text-color mr-1']);
								}
								?>
                        </div>
                    </div>
                </div>
				<?php
				$i++; 
			}

		} else {
			?>
			<div class="alert alert-danger">
				No classroom created yet
			</div>
			<?php
		}
		?> 
	</div>
</div>
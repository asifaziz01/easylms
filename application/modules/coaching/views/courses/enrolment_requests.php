<div class="col-12 list" data-check-all="checkAll">
	<?php
	$i = 1;
	echo form_open ('coaching/enrolment_actions/approve_enrolment/'.$coaching_id, array ('id'=>'validate-1')); 
		if (! empty($enrol_requests)) {
			foreach ($enrol_requests as $item) {
				?>
				<div class="card mb-0">
		            <div class="d-flex flex-grow-1 min-width-zero">
		                <label class="custom-control custom-checkbox mb-1 align-self-center ml-4">
		                    <input type="checkbox"  name="users[]" value="<?php echo $item['member_id']; ?>" class="custom-control-input">
		                    <span class="custom-control-label">&nbsp;</span>
		                </label>
		                <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
		                    <p class="mb-0 text-muted text-small w-15 w-xs-100 d-none"><?php echo $i; ?>.</p>
		                    <p class="list-item-heading mb-0 truncate w-40 w-xs-100" >
		                    	<?php echo $item ["title"]; ?>
		                    </p>
		                    <p class="mb-0 w-15 w-xs-100">
		                        <?php echo $item["first_name"].' '.$item["last_name"];?><br>
								<?php echo $item["primary_contact"]; ?>
		                    </p>
		                    <p class="mb-0 text-muted text-small w-15 w-xs-100">
		                    	<?php echo date ('d M, Y', $item ["request_date"]); ?>
		                    </p>
					        <div>
					        	<a href="<?php echo site_url ('coaching/enrolment_actions/approve_enrolment/'.$coaching_id.'/'.$item['course_id'].'/'.$item['batch_id'].'/'.$item['member_id']); ?>" class="">Approve</a>
					        </div>
		                </div>
			        </div>

		        </div>
				<?php 
				$i++;
			}
		} else { 
			?>
			<div class="alert alert-danger">
				<span  class="">No request found</span> 
			</div>
			<?php
		}
	echo form_close ();
	?>
</div>
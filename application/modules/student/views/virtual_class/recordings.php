 <div class="mb-2 text-center" id="list-recordings">
	<div class="spinner-border" role="status">
	  <span class="sr-only">Looking for new recordings...</span>
	</div>
</div>
<?php 
if (! empty ($recordings)) {
	foreach ($recordings as $record) {
		$i = 1;
		?>
		<div class="card d-flex flex-row mb-3">
            <div class="d-flex flex-grow-1 min-width-zero">

                <div class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
                    <a class="list-item-heading mb-0 truncate w-40 w-xs-100" href="<?php echo $record['publish_url']; ?>" target="_blank">
                        <?php 
                        if ($record['recording_name'])
                        	$name = $record['recording_name']; 
                        else
                        	$name = $class['class_name'];

                        ?>
                        <?php echo $name; ?>
                    </a>
                    <p class="mb-0 text-muted text-small w-15 w-xs-100"><?php echo date ('d-m-Y \a\t h:i a', $record['publish_date']); ?></p>
                    <p class="mb-0 text-muted text-small w-15 w-xs-100">
                    	<?php echo $duration_mm = round ($record['duration'] / 60,  2) . ' minutes'; ?>
                    </p>
                    <div class="w-15 w-xs-100">
                    </div>
                    <!--
                    <div class="w-15 w-xs-100">
                    	<?php if ($record['status'] == 1) { ?>
                        	<span class="badge badge-pill badge-secondary">Published</span>
                		<?php } else { ?>
                        	<span class="badge badge-pill badge-light">Un-published</span>
                    	<?php } ?>
                    </div>
                	-->
                </div>
                <div class="mb-1 align-self-center pr-4">
					<a class="btn btn-primary" href="<?php echo $record['publish_url']; ?>"  target="_blank"><i class="fa fa-play"></i> </a>
                </div>
            </div>
        </div>
		<?php
		$i++;
	}
} else {
	?>
	<!--
	<div class="card">
		<div class="card-body">
			<p>No recordings found</p>
		</div>
	</div>
	-->
	<?php
}
?>

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        	<?php echo form_open ('coaching/virtual_class_actions/rename_recording/'.$coaching_id.'/'.$class_id.'/'.$meeting_id.'/'.$course_id.'/'.$batch_id, ['class'=>'validate-form']); ?>
	            <div class="modal-header">
	                <h5 class="modal-title" id="renameModalLabel">Rename Recording</h5>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                    <span aria-hidden="true">&times;</span>
	                </button>
	            </div>
	            <div class="modal-body">
	                <input type="hidden" name="id" value="0" id="recordID">
	                <input type="text" name="recording_name" value="" id="recordName">
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-secondary"
	                    data-dismiss="modal">Close</button>
	                <button type="submit" class="btn btn-primary">Rename</button>
	            </div>
            <?php echo form_close (); ?>
        </div>
    </div>
</div>

<div class="card d-none"> 
	<ul class="list-group">
	<?php
	$i = 0;
	if ($response == 'SUCCESS') {
		foreach ($recordings->recording as $recording) {
			$i++;
			$start_time = floatval ($recording->startTime);
			$end_time = floatval ($recording->endTime);
			$duration = ($end_time - $start_time)/1000;
			$duration_mm = round ($duration / 60,  2);
			$url = $recording->playback->format->url;
			$time = '';
			$time .= date ('d F, Y', ($start_time/1000));
			$time .= ' at ';
			$time .= date ('h:i A', ($start_time/1000));
			?>
			<li class="list-group-item media">
				<div class="media-left">
					<?php echo $i; ?>.
				</div>
				<div class="media-body">
					<a href="<?php echo $url; ?>" target="_blank"><?php echo $class['class_name']; ?></a>
					<p>
						<span class="badge badge-default">Time</span> <?php echo $time; ?><br>
						<span class="badge badge-default">Duration</span> <?php echo $duration_mm . ' minutes'; ?>
					</p>
				</div>

			</li>
			<?php
		}
	} 
	if ($i == 0) {
		?>
		<li class="list-group-item text-danger">No rcordings found</li>
		<?php
	}
	?>
	</ul>
</div>
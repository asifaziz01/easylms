<?php 
if (! empty ($result)) {
	foreach ($result as $record) {
		$i = 1;
		?>
		<div class="card d-flex flex-row mb-3">
            <div class="d-flex flex-grow-1 min-width-zero">

				<span class="badge badge-pill badge-danger position-absolute badge-top-left">NEW</span>
                <div
                    class="card-body align-self-center d-flex flex-column flex-md-row justify-content-between min-width-zero align-items-md-center">
                    <a class="list-item-heading mb-0 truncate w-40 w-xs-100" href="<?php echo $record['publish_url']; ?>" target="_blank">
                        <?php 
                        if ($record['recording_name'] != '')
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
}
?>
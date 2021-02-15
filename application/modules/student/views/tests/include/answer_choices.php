<?php 
switch ($row['type']) {
	case QUESTION_MCMC:
		for ($i=1; $i <= 6; $i++) {
			$choice =  ($row['choice_'.$i]);
			if ($choice != '') {
				echo '<div class="custom-control custom-checkbox mb-2">';
					echo form_checkbox ( array('name'=>'ans['.$row['question_id'].']', 'value'=>$i, 'checked'=>false, 'id'=>'choice'.$row['question_id'].'_'.$i, 'class'=>'custom-control-input answer') );
					echo '<label class="custom-control-label d-block" for="choice'.$row['question_id'].'_'.$i.'">';
						echo strip_tags($choice);
					echo '</label>';
				echo '</div>';
			}
		} 
	break;
	
	case QUESTION_TF: 
		echo '<div class="custom-control custom-radio mb-2">';
			echo form_radio ( array('name'=>'ans['.$row['question_id'].']', 'value'=>1, 'checked'=>false, 'id'=>'choice'.$row['question_id'].'_1', 'data-id'=>$num, 'class'=>'custom-control-input answer') );
			echo '<label class="custom-control-label d-block" for="choice'.$row['question_id'].'_1">';
				echo 'True';
			echo '</label>';
		echo '</div>';
		
		echo '<div class="custom-control custom-radio mb-2">';
			echo form_radio ( array('name'=>'ans['.$row['question_id'].']', 'value'=>2, 'checked'=>false, 'id'=>'choice'.$row['question_id'].'_2', 'data-id'=>$num, 'class'=>'custom-control-input answer') );
			echo '<label class="custom-control-label d-block" for="choice'.$row['question_id'].'_2">';
				echo 'False';
			echo '</label>';
		echo '</div>';
	break;
	
	case QUESTION_LONG:
		/*
		for ($i=1; $i <= 2; $i++) {
			$choice =  ($row['choice_'.$i]);
			if ($choice != '') {
				if ($row['answer_'.$i] == $i && !isset ($hide_correct_answer) ) {
					$class = "text-success text-bold";
				} else {
					$class= "";
				}
				echo '<li class="'.$class.'">';
					echo $choice;
				echo '</li>';
			}
		} 
		*/
		echo '<textarea name="ans[1]" class="form-control"></textarea>';

	break;
	
	case QUESTION_MATCH:
		for ($i=1; $i <= 2; $i++) {
			$choice =  ($row['choice_'.$i]);
			$option =  ($row['option_'.$i]);
			if ($choice != '' || $option != '') {
				echo '<div class="row">';
					echo '<div class="col-md-6">';
						echo $choice;
					echo '</div>';
					echo '<div class="col-md-6">';
						echo $option;
					echo '</div>';
				echo '</div>';
			}
		}
	break;
	
	
	default :
		for ($i=1; $i <= 6; $i++) {
			$choice =  ($row['choice_'.$i]);
			if ($choice != '') {
				echo '<div class="custom-control custom-radio mb-2">';
					echo form_radio ( array('name'=>'ans['.$row['question_id'].']', 'value'=>$i, 'checked'=>false, 'id'=>'choice'.$row['question_id'].'_'.$i, 'data-id'=>$num, 'class'=>'custom-control-input answer') );
					echo '<label class="custom-control-label d-block" for="choice'.$row['question_id'].'_'.$i.'">';
						echo strip_tags($choice);
					echo '</label>';
				echo '</div>';
			}
		}
		echo '<div class="custom-control custom-radio">'; 
				echo form_radio ( array('name'=>'ans['.$row['question_id'].']', 'value'=>'', 'checked'=>true, 'class'=>'custom-control-input uniforms leaveblank', 'id'=>'leaveblank_'.$num, 'data-id'=>$num) ); 
				echo '<label class="custom-control-label d-block" for="leaveblank_'.$num.'">';
					echo "<em>Leave blank</em>";
				echo '</label>';
		echo '</div>';
	break;
}
?>
<br>
<div class="custom-control custom-checkbox">
	<?php echo form_checkbox ( array('name'=>'visitlater_'.$row['question_id'], 'value'=>'1', 'checked'=>false, 'class'=>'custom-control-input visitlater', 'id'=>'visitlater_'.$num, 'data-id'=>$num) ); ?>
	<label class="custom-control-label" for="<?php echo 'visitlater_'.$num; ?>">
		<span class=" text-success"><?php echo "Visit Question Later (Mark for review)"; ?> </span>
	</label>
</div>
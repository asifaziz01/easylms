<script>

const outputSelector = document.getElementById ('list-recordings');

fetch ('<?php echo site_url ('student/virtual_class_actions/get_new_recordings/'.$coaching_id.'/'.$class_id.'/'.$meeting_id.'/'.$course_id.'/'.$batch_id); ?>', {
	method: 'GET',
}).then (function (response) {
	return response.json ();
}).then(function(result) {
	if (result.status == true) {
		var output =  result.data;
		outputSelector.innerHTML = output;
	}
});


function update_modal (id, name) {
	$('#recordID').val (id);
	$('#recordName').val (name);
}
</script>
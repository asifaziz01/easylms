<script>
	const loaderSelector = document.getElementById('loader');
	const formSelector = document.getElementById('login-form');
	const errorDiv = document.getElementById('error');
	
	formSelector.addEventListener ('submit', e => {
		e.preventDefault ();
		const formURL = formSelector.getAttribute ('action');
		var formData = new FormData(formSelector);
		loaderSelector.style.display = 'block';		
		toastr.info ('Please wait...');
		fetch (formURL, {
			method : 'POST',
			body: formData,
		}).then (function (response) {
			return response.json ();
		}).then(function(result) {
			toastr.clear ();
			if (result.status == true) {
				if (typeof(Storage) !== "undefined") {
				   localStorage.clear ();
				   localStorage.setItem('user_token', result.user_token);
				} else {
				  // Too bad, no localStorage for us - Set cookie for 10 days
				   setCookie('user_token', result.user_token, 10 );
				}
				toastr.success (result.message);
				document.location = result.redirect;
			} else {
				toastr.error (result.error);
			}
			loaderSelector.style.display = 'none';
		});
	});	

	$(document).ready( function () {
  		var base_url = '<?php echo base_url(); ?>';
		$('.reload-captcha').click(function(event){
			event.preventDefault();
			$.ajax({

				url:base_url+'login/user/refresh_captcha',
				dataType: "text",  
				cache:false,
				success:function(data){
					$('.captcha-img').replaceWith(data);
					
				}
			});            
		});
	});
</script>
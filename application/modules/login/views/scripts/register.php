<script>
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

$('#show-password').on ('click', function () {
	const password = $("#reg-password");
	const password_text = $("#show-password-link");
	const password_icon = $("#password-icon");
	if (password.attr('type') === "password") {
	    password.attr('type','text');
	    password_icon.addClass('fa-eye-slash').removeClass('fa-eye');
	    password_text.text('Hide Password');
	} else {
		password.attr('type','password');
		password_icon.removeClass('fa-eye-slash').addClass('fa-eye');
	    password_text.text('Show Password');
	}
});

//
const formSelector = document.getElementById('validate-user');
var formData = new FormData(formSelector);

$('#btn-send-otp').on ('click', function () {
	var user_name = $('#user_name').val ();
	var user_mobile = $('#user_mobile').val ();
	validate_user_mobile (user_name, user_mobile);	
});


function validate_user_mobile (user_name, user_mobile) {

	toastr.clear ();
	fetch ('<?php echo site_url ('login/login_actions/validate_user_mobile'); ?>/'+user_name+'/'+user_mobile, {
		method : 'POST',
		body: formData,
	}).then (function (response) {
		return response.json ();
	}).then(function(result) {
		if (result.status == true) {
			// Show success message
			toastr.success (result.message, '', {timeOut:3000});
			// Add OTP input box
			$('#otp-field').removeClass ('d-none');
			// Change button text
			$('#btn-send-otp').html ('Resend OTP');
			// Disable button
			$('#btn-send-otp').prop('disabled', true);
			// Enable after 1 minute
			setTimeout (function () { 
				$('#btn-send-otp').prop('disabled', false); 
			}, 60000);
		} else {
			toastr.error (result.error, '', {timeOut:3000});
		}
	});
}

function validate_otp (otp) {
	var n = otp.length;
	var user_mobile = $('#user_mobile').val ();
	toastr.clear ();
	if (n < 6 || n > 6) {
		//toastr.error ('Invalid OTP', '', {timeOut:3000});
	} else {
		fetch ('<?php echo site_url ('login/login_actions/validate_otp'); ?>/'+user_mobile+"/"+otp, {
			method : 'POST',
			body: formData,
		}).then (function (response) {
			return response.json ();
		}).then(function(result) {
			if (result.status == true) {
				// Show success message
				toastr.success (result.message, '', {timeOut:3000});
				// Disable button
				$('#user_mobile').prop ('readonly', true);
				$('#mobile-otp').prop ('disabled', true);
				$('#btn-send-otp').addClass ('d-none');
				// Add OTP input box
				$('#login-field').removeClass ('d-none');
			} else {
				toastr.error (result.error, '', {timeOut:3000});
			}

		});
	}
}


formSelector.addEventListener ('submit', e => {
	e.preventDefault ();
	const formURL = formSelector.getAttribute ('action');
	var formData = new FormData(formSelector);
	toastr.info ('Please wait...');
	fetch (formURL, {
		method : 'POST',
		body: formData,
	}).then (function (response) {
		return response.json ();
	}).then(function(result) {
		toastr.clear ();
		if (result.status == true) {
			// Show success message
			toastr.success (result.message, '', {timeOut:3000});
			if (result.redirect) {
				document.location = result.redirect;
			}
		} else {
			toastr.error (result.error, '', {timeOut:3000});
		}

	});
});




</script>


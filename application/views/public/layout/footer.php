		</div>    	
	</main>

	<div id="loader">
		<img src="<?php echo base_url (THEME_PATH . 'assets/img/loader.gif'); ?>" width="30" height="30">
	</div>

	
    <!-- Core -->
    <script src="<?php echo base_url(THEME_PATH . 'assets/js/vendor/jquery-3.3.1.min.js'); ?>"></script>
    <script src="<?php echo base_url(THEME_PATH . 'assets/js/vendor/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url(THEME_PATH . 'assets/js/dore.script.js'); ?>"></script>

	<!-- Toastr JS for notifications -->
	<script type="text/javascript" src="<?php echo base_url (THEME_PATH . 'assets/js/toastr.min.js'); ?>"></script>
	<!-- Default JS (Must be loaded befaore app.js) -->
	<script type="text/javascript" src="<?php echo base_url (THEME_PATH . 'assets/js/default.js'); ?>"></script>
    <script src="<?php echo base_url(THEME_PATH . 'assets/js/scripts.js'); ?>"></script>
	<!-- Application JS -->
	<script type="text/javascript" src="<?php echo base_url (THEME_PATH . 'assets/js/app.js?ver=1.5'); ?>"></script>
	<!-- Custom JS (Dynamically included) -->
	<?php
	if (isset ($script)) {
		echo $script;
	}

	$this->load->view ('templates/common/scripts');
	?>	
	
  </body>
</html>
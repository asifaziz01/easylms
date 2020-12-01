<div class="row h-100">
    <div class="col-12 col-md-10 mx-auto my-auto">
        <div class="card auth-card shadow">
            <div class="position-relative image-side text-center">
                <div class="d-flex flex-column h-100 align-items-center justify-content-center">
                    <?php if ( read_file ($logo) != false) { ?>
                        <img src="<?php echo $logo; ?>" height="80" title="<?php echo $page_title; ?>" class="text-center mb-4" alt="<?php echo $page_title; ?>">
                    <?php } else { ?>
                        <h2 class="text-white text-center mb-4"><?php echo $page_title; ?></h2>
                    <?php } ?>
                    <p class=" text-white h6">
                        DON'T HAVE AN ACCOUNT? <br>SIGN-UP
                    </p>
                    <p class="white mb-0">
                        <br> 
                        <a href="<?php echo site_url('login/user/register')?>" class="btn btn-light ">Register as a student</a>
                    </p>
                    <?php if($website_link != ''){ ?>

             
                    <p class="white mb-0">
                        <br> 
                        <a href="<?php echo $website_link; ?>" class="text-white">Back to Website</a>
                    </p>
                <?php } ?>
                </div>
            </div>
            <div class="form-side">
               
                <h4 class="text-center mb-4">Sign in with your credentials</h4>
                <?php echo form_open ('login/login_actions/validate_login', array('id'=>'login-form')); ?>
                    <div class="form-group mb-4">
                        <label class="">
                            <span>Mobile/Email/User-id</span>
                        </label>
                        <input class="form-control" placeholder="Mobile No/Email-id/User-ID" type="text" name="username">
                    </div>

                    <div class="form-group mb-4">
                        <label class="">
                            <span>Password</span>
                        </label>
                        <input class="form-control" placeholder="Password" type="password" name="password">
                        <a href="<?php echo site_url ('login/user/reset_password'); ?>" class="text-info">Reset password</a>
                    </div>

                    <div class="form-group mb-4">
                        <?php if ($access_code != '' && $found == true) { ?>
                            <input class="form-control" placeholder="Access Code" type="hidden" name="access_code" value="<?php echo $access_code; ?>" readonly>
                        <?php } else { ?>
                            <label class="">
                                <span>Access Code</span>
                            </label>
                            <input class="form-control" placeholder="Access Code" type="text" name="access_code" value="<?php echo $access_code; ?>" >
                            <a href="<?php echo site_url ('login/user/get_access_code'); ?>" class="text">Get Access Code</a>
                        <?php } ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary btn-lg btn-shadow" type="submit">LOGIN</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="mt-4 text-center ">
            <?php if (isset($coaching['custom_text_login'])) { echo $coaching['custom_text_login']; } ?>
        </div>
    </div>
</div>

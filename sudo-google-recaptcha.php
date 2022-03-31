<?php

/**
 * Plugin Name: SUDO Google-Recaptcha
 */

 /**
 * Registers a new options page under Settings.
 */

add_action( 'admin_menu', 'sudo_add_admin_menu' );
add_action( 'admin_init', 'sudo_settings_init' );
add_action( 'wp_enqueue_scripts', 'sudo_enqueue_script' );

function sudo_enqueue_script(){
  $options = get_option( 'sudo_settings' );
  $site_key = empty($options['sudo_google_recaptcha_site_key']) ? "" : $options['sudo_google_recaptcha_site_key'];
}


function sudo_add_admin_menu(  ) { 
	add_options_page( 'SUDO Google-Recaptcha', 'SUDO Google-Recaptcha', 'manage_options', 'sudo_google-recaptcha', 'sudo_options_page' );
}


function sudo_settings_init(  ) { 

	register_setting( 'pluginPage', 'sudo_settings' );

	add_settings_section(
		'sudo_pluginPage_section', 
		__( 'Settings', 'sudo' ), 
		'sudo_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'sudo_google_recaptcha_site_key', 
		__( 'Repcatcha Site Key', 'sudo' ), 
		'sudo_google_recaptcha_site_key_render', 
		'pluginPage', 
		'sudo_pluginPage_section' 
	);

	add_settings_field( 
		'sudo_google_recaptcha_secret_key', 
		__( 'Repcatcha Secret Key', 'sudo' ), 
		'sudo_google_recaptcha_secret_key_render', 
		'pluginPage', 
		'sudo_pluginPage_section' 
	);


}


function sudo_google_recaptcha_site_key_render(  ) { 

	$options = get_option( 'sudo_settings' );
  $value = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];
	?>
	<input type='text' name='sudo_settings[sudo_google_recaptcha_site_key]' value='<?= $value ?>'>
	<?php

}


function sudo_google_recaptcha_secret_key_render(  ) { 

	$options = get_option( 'sudo_settings' );
  $value = empty($options['sudo_google_recaptcha_secret_key']) ? '' : $options['sudo_google_recaptcha_secret_key'];
	?>
	<input type='text' name='sudo_settings[sudo_google_recaptcha_secret_key]' value='<?= $value ?>'>
	<?php

}


function sudo_settings_section_callback(  ) { 

	echo __( 'Find/create your recaptcha keys here: <a href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a>', 'sudo' );

}


function sudo_options_page(  ) { 

		?>
		<form action='options.php' method='post'>

			<h2>SUDO Google-Recaptcha</h2>

			<?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
			?>

		</form>
		<?php

}

add_action( 'wp_body_open', 'sudo_at_callback_to_body');

function sudo_at_callback_to_body(){
	?>
	<script type="text/javascript">
		var onloadCallback = function(token) {
			var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

			jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'sudo_verify_grecaptcha',
						token
        },
        type: 'POST'
    });
		};
	</script>
	<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
    async defer>
	</script>
	<?php
}


add_action( 'woocommerce_review_order_before_submit', 'sudo_add_recaptcha_to_checkout', 10);
 
function sudo_add_recaptcha_to_checkout(){
  $options = get_option( 'sudo_settings' );
  $site_key = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];

  printf('<div class="g-recaptcha" data-callback="onloadCallback" data-sitekey="%s"></div>', $site_key);
  
  ?>
  <script>
    grecaptcha.render( document.querySelector('.g-recaptcha') );
  </script>
  <?php
}

add_action('wp_ajax_sudo_verify_grecaptcha', 'sudo_verify_grecaptcha');
add_action('wp_ajax_nopriv_sudo_verify_grecaptcha', 'sudo_verify_grecaptcha');

function sudo_verify_grecaptcha(){
	$token = isset($_POST['token']) ? $_POST['token'] : false;

	$url = 'https://www.google.com/recaptcha/api/siteverify';

	$options = get_option( 'sudo_settings' );

  $secret = empty($options['sudo_google_recaptcha_secret_key']) ? '' : $options['sudo_google_recaptcha_secret_key'];

	$body = sprintf('secret=%s&response=%s', $secret, $response);

	if( $token ){
		$response = wp_remote_post($url, [
			'body' => $body,
			'headers'     => [
        "Content-Type" => "application/x-www-form-urlencoded"
    	],
		]);

		
		$body = stripslashes($response['body']);
		$data = json_decode($body);

		if( $data->success ){
			intval($data->success);
			sudo_get_grecaptcha_success_status(intval($data->success));
			$GLOBALS['grecaptcha_success_status'] = intval($data->success);
		}

	}

	die();
}

function sudo_get_grecaptcha_success_status($response=''){
		$a = foo1($id);
		$b = foo2($a);
		return $b;
}

add_action( 'woocommerce_after_checkout_validation', 'sudo_validate_recaptcha', 10, 2);
 
function sudo_validate_recaptcha( $fields, $errors ){
	$success = sudo_get_grecaptcha_success_status();

	if ( empty( $success ) ){
		$errors->add( 'validation', print_r($success) );
	}
}

add_action('wp_body_open', 'sudo_test');
function sudo_test(){
	$success = ! isset($GLOBALS['grecaptcha_success_status']) ? false : $GLOBALS['grecaptcha_success_status'];

	var_dump($success);
}
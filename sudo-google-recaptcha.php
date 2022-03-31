<?php

/**
 * Plugin Name: SUDO Google-Recaptcha
 */

 /**
 * Registers a new options page under Settings.
 */


class SudoGoogleRecaptcha{
	private $sudo_grecaptcha_status;

	function __construct(){
		$this->sudo_grecaptcha_status = false;

		// admin
		add_action( 'admin_menu', array($this, 'sudo_add_admin_menu') );
		add_action( 'admin_init', array($this, 'sudo_settings_init') );
	
		// test
		// add_action('wp_body_open', array($this, 'sudo_test'));
	
		// add callback when recaptcha is entered 
		add_action( 'wp_body_open', array($this, 'sudo_at_callback_to_body') );
	
	
		// add recaptcha form to checkout
		add_action( 'woocommerce_after_order_notes', array($this, 'sudo_grecaptcha_input_html') );
		add_action( 'woocommerce_review_order_before_submit', array($this, 'sudo_add_recaptcha_to_checkout'), 10);
	
		// ajax to verify
		add_action('wp_ajax_sudo_verify_grecaptcha', array($this, 'sudo_verify_grecaptcha') );
		add_action('wp_ajax_nopriv_sudo_verify_grecaptcha', array($this, 'sudo_verify_grecaptcha') );


		add_action( 'woocommerce_after_checkout_validation', array($this, 'sudo_woocommerce_validate_recaptcha'), 10, 2);

	}

	public function sudo_add_admin_menu(  ) { 
		add_options_page( 'SUDO Google-Recaptcha', 'SUDO Google-Recaptcha', 'manage_options', 'sudo_google-recaptcha', array($this, 'sudo_options_page') );
	}
	
	
	public function sudo_settings_init(  ) { 
	
		register_setting( 'pluginPage', 'sudo_settings' );
	
		add_settings_section(
			'sudo_pluginPage_section', 
			__( 'Settings', 'sudo' ), 
			array($this, 'sudo_settings_section_callback'), 
			'pluginPage'
		);
	
		add_settings_field( 
			'sudo_google_recaptcha_site_key', 
			__( 'Repcatcha Site Key', 'sudo' ), 
			array($this, 'sudo_google_recaptcha_site_key_render'), 
			'pluginPage', 
			'sudo_pluginPage_section' 
		);
	
		add_settings_field( 
			'sudo_google_recaptcha_secret_key', 
			__( 'Repcatcha Secret Key', 'sudo' ), 
			array($this, 'sudo_google_recaptcha_secret_key_render'), 
			'pluginPage', 
			'sudo_pluginPage_section' 
		);
	}
	
	
	public function sudo_google_recaptcha_site_key_render(  ) { 
	
		$options = get_option( 'sudo_settings' );
		$value = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];
		?>
		<input type='text' name='sudo_settings[sudo_google_recaptcha_site_key]' value='<?= $value ?>'>
		<?php
	
	}
	
	
	public function sudo_google_recaptcha_secret_key_render(  ) { 
	
		$options = get_option( 'sudo_settings' );
		$value = empty($options['sudo_google_recaptcha_secret_key']) ? '' : $options['sudo_google_recaptcha_secret_key'];
		?>
		<input type='text' name='sudo_settings[sudo_google_recaptcha_secret_key]' value='<?= $value ?>'>
		<?php
	
	}
	
	
	public function sudo_settings_section_callback(  ) { 
		echo __( 'Find/create your recaptcha keys here: <a href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a>', 'sudo' );
	}
	
	
	public function sudo_options_page(  ) { 
	
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
	
	
	public function sudo_at_callback_to_body(){
		?>
		<script type="text/javascript">
			var onloadCallback = function(token = false) {
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
				const field = document.querySelector('#grecaptcha_response_field');
				field.setAttribute('token', token);
				// jQuery.ajax({
				// 	url: ajaxurl,
				// 	data: {
				// 			action: 'sudo_verify_grecaptcha',
				// 			token
				// 	},
				// 	type: 'POST'
				// });
			};
		</script>
		<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
			async defer>
		</script>
		<?php
	}
	
	
	 
	public function sudo_add_recaptcha_to_checkout(){
		$options = get_option( 'sudo_settings' );
		$site_key = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];
	
		printf('<div class="g-recaptcha" data-callback="onloadCallback" data-sitekey="%s"></div>', $site_key);
		
		?>
		<script>
			grecaptcha.render( document.querySelector('.g-recaptcha') );
		</script>
		<?php
	}
	
	
	
	public function sudo_verify_grecaptcha(){
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
				// Find how we can pass this information to the validation message
			}

	
		}
	
		die();
	}


	public function sudo_grecaptcha_input_html($checkout){
		// woocommerce_form_field( 'grecaptcha_response', array(
		// 	'type'	=> 'hidden',
		// 	'class'	=> array('sudo-grecaptcha-response'),
		// 	), $checkout->get_value( 'sudo-grecaptcha-response' ) );
	}
	
	
	public function sudo_woocommerce_validate_recaptcha($fields, $errors){

		$token = $_POST['g-recaptcha-response'];

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

		}

		if( ! $data->success ){
			wc_add_notice( 'Please fill in the captcha', 'error' );
		}
	}
	
}

new SudoGoogleRecaptcha; 




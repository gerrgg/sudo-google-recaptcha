<?php

/**
 * Plugin Name: SUDO Google-Recaptcha
 * Plugin URI: https://github.com/gerrgg/woocommerce-net-30-terms
 * Description: Adds V2 google recaptcha support to woocommerce
 * Version: 1.2
 * Author: Greg Bastianelli   
 * Author URI: http://gerrg.com/
 * Text Domain: sudo
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

class SudoGoogleRecaptcha{
	private $sudo_site_key;
	private $sudo_secret_key;
	private $debug = false;

	function __construct(){
		$options = get_option( 'sudo_settings' );
		$this->sudo_site_key = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];
		$this->sudo_secret_key = empty($options['sudo_google_recaptcha_secret_key']) ? '' : $options['sudo_google_recaptcha_secret_key'];
		$this->debug = empty($options['sudo_google_recaptcha_debug']) ? '' : $options['sudo_google_recaptcha_secret_key'];

		// setup options page
		add_action( 'admin_menu', array($this, 'sudo_add_admin_menu') );
		add_action( 'admin_init', array($this, 'sudo_settings_init') );
	
		// add callback for recaptcha 
		add_action( 'wp_head', array($this, 'sudo_at_callback_to_body') );
	
		// add recaptcha form to checkout
		add_action( 'woocommerce_review_order_before_submit', array($this, 'sudo_add_recaptcha_to_checkout'), 10);
	
		// validate recaptcha on checkout
		add_action( 'woocommerce_after_checkout_validation', array($this, 'sudo_woocommerce_validate_recaptcha'), 10, 2);

	}

	// Add options page
	public function sudo_add_admin_menu(  ) { 
		add_options_page( 'SUDO Google-Recaptcha', 'SUDO Google-Recaptcha', 'manage_options', 'sudo_google-recaptcha', array($this, 'sudo_options_page') );
	}
	
	// init options
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

		add_settings_field( 
			'sudo_google_recaptcha_debug', 
			__( 'Debug', 'sudo' ), 
			array($this, 'sudo_google_recaptcha_debug_render'), 
			'pluginPage', 
			'sudo_pluginPage_section' 
		);
	}
	
	
	public function sudo_google_recaptcha_site_key_render(  ) { 
		?>
		<input type='text' name='sudo_settings[sudo_google_recaptcha_site_key]' value='<?= $this->sudo_site_key  ?>'>
		<?php
	}
	
	
	public function sudo_google_recaptcha_secret_key_render(  ) { 
		?>
		<input type='text' name='sudo_settings[sudo_google_recaptcha_secret_key]' value='<?= $this->sudo_secret_key ?>'>
		<?php
	}
	
	public function sudo_google_recaptcha_debug_render(  ) { 
		?>
		<input type='checkbox' value="1" name='sudo_settings[sudo_google_recaptcha_debug]' <?= $this->debug ? 'checked' : '' ?>>
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
	
	// Adds callback and script to DOM
	public function sudo_at_callback_to_body(){
		?>
		<script src="https://www.google.com/recaptcha/api.js"
			async defer>
		</script>
		<?php
	}
	
	
	 
	public function sudo_add_recaptcha_to_checkout(){
		printf('<div class="g-recaptcha" data-sitekey="%s"></div>', $this->sudo_site_key);
		?>
		<script>
			grecaptcha.render( document.querySelector('.g-recaptcha') );
		</script>
		<?php
	}
	
	public function sudo_woocommerce_validate_recaptcha($fields, $errors){
		// get the token from POST
		$token = $_POST['g-recaptcha-response'];

		$url = 'https://www.google.com/recaptcha/api/siteverify';

		// setup request body
		$request = sprintf('secret=%s&response=%s', $this->sudo_secret_key, $token);
	
		// send token to google to verify token
		if( $token ){
			$response = wp_remote_post($url, [
				'body' => $request,
				'headers'     => [
					"Content-Type" => "application/x-www-form-urlencoded"
				],
			]);
	
			// clean data
			$body = stripslashes($response['body']);
			$data = json_decode($body);
		}

		// if the token fails, cancel the checkout
		if( ! $data->success ){
			if( $this->debug ){
				$html = "";
				ob_start();
        var_dump($token);
				var_dump($data);
				$html = ob_get_clean();
				wc_add_notice( $html, 'error' );
			} else {
				wc_add_notice( 'Please pass the recaptcha before checking out.', 'error' );
			}
		}
	}
	
}

new SudoGoogleRecaptcha; 

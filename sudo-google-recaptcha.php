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


  wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', false );

  wp_enqueue_script( 'script', plugins_url( 'script.js', __FILE__ ), false );
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


add_action( 'woocommerce_review_order_before_submit', 'sudo_add_recaptcha_to_checkout', 10);
 
function sudo_add_recaptcha_to_checkout(){

  $options = get_option( 'sudo_settings' );
  $site_key = empty($options['sudo_google_recaptcha_site_key']) ? '' : $options['sudo_google_recaptcha_site_key'];

  printf('<div class="g-recaptcha" data-sitekey="%s"></div>', $site_key);
}
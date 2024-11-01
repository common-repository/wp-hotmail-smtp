<?php

class WPHotmail_Settings {

	var $options;

	function __construct() {
		$this->options = get_option( 'wp_hotmail_smtp_option' );
	}

	/**
	 * Register and add settings
	 */
	function settings() {
		register_setting(
				'wp_hotmail_smtp_option_group', // Option group
				'wp_hotmail_smtp_option', // Option name
				array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
				'wp_hotmail_setting_id', // ID
				__( 'Email Options', 'hotmailsmtp' ), // Title
				array( $this, 'print_section_info' ), // Callback
				'hotmailsmtp' // Page
		);
		add_settings_field(
				'is_active', // ID
				sprintf( '<label for="is_active">%s</label>', __( 'Is Active?', 'hotmailsmtp' ) ), // Title 
				array( $this, 'is_active_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);

		add_settings_field(
				'from_email', // ID
				sprintf( '<label for="from_email">%s</label>', __( 'From Email', 'hotmailsmtp' ) ), // Title 
				array( $this, 'from_email_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);

		add_settings_field(
				'from_name', sprintf( '<label for="from_name">%s</label>', __( 'From Name', 'hotmailsmtp' ) ), // Title 
				array( $this, 'from_name_callback' ), 'hotmailsmtp', 'wp_hotmail_setting_id'
		);
		add_settings_field(
				'mail_set_return_path', __( 'Return Path', 'hotmailsmtp' ), // Title 
				array( $this, 'return_path_callback' ), 'hotmailsmtp', 'wp_hotmail_setting_id'
		);
		add_settings_section(
				'wp_hotmail_setting_id', // ID
				__( 'SMTP Options', 'hotmailsmtp' ), // Title
				array( $this, 'print_smtp_section_info' ), // Callback
				'hotmailsmtp' // Page
		);
		add_settings_field(
				'smtp_encryption', // ID
				sprintf( '<label for="smtp_encryption">%s</label>', __( 'Encryption', 'hotmailsmtp' ) ), // Title 
				array( $this, 'smtp_encryption_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);
		add_settings_field(
				'smtp_authentication', // ID
				sprintf( '<label for="smtp_authentication">%s</label>', __( 'Authentication', 'hotmailsmtp' ) ), // Title 
				array( $this, 'smtp_authentication_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);
		add_settings_field(
				'smtp_username', // ID
				sprintf( '<label for="smtp_username">%s</label>', __( 'Username', 'hotmailsmtp' ) ), // Title 
				array( $this, 'smtp_username_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);
		add_settings_field(
				'smtp_password', // ID
				sprintf( '<label for="smtp_password">%s</label>', __( 'Password', 'hotmailsmtp' ) ), // Title 
				array( $this, 'smtp_password_callback' ), // Callback
				'hotmailsmtp', // Page
				'wp_hotmail_setting_id' // Section           
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['from_email'] ) ) {
			$new_input['from_email'] = sanitize_email( $input['from_email'] );
		}
		if ( isset( $input['is_active'] ) ) {
			$new_input['is_active'] = sanitize_text_field( $input['is_active'] );
		}
		if ( isset( $input['from_name'] ) ) {
			$new_input['from_name'] = sanitize_text_field( $input['from_name'] );
		}
		if ( isset( $input['mail_set_return_path'] ) ) {
			$new_input['mail_set_return_path'] = sanitize_text_field( $input['mail_set_return_path'] );
		}
		if ( isset( $input['smtp_encryption'] ) ) {
			$new_input['smtp_encryption'] = sanitize_text_field( $input['smtp_encryption'] );
		}
		if ( isset( $input['smtp_authentication'] ) ) {
			$new_input['smtp_authentication'] = sanitize_text_field( $input['smtp_authentication'] );
		}
		if ( isset( $input['smtp_username'] ) ) {
			$new_input['smtp_username'] = sanitize_text_field( $input['smtp_username'] );
		}
		if ( isset( $input['smtp_password'] ) ) {
			$new_input['smtp_password'] = sanitize_text_field( $input['smtp_password'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		
	}

	public function print_smtp_section_info() {
		print __( 'These options only apply if you have chosen to send mail by SMTP above.', 'hotmailsmtp' );
	}

	public function is_active_callback() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e( 'Encryption', 'hotmailsmtp' ); ?></span></legend>
			<p><input <?php echo!isset( $this->options['is_active'] ) ? 'checked="checked"' : ''; ?> id="yes" type="radio" name="wp_hotmail_smtp_option[is_active]" value="yes" <?php checked( 'yes', isset( $this->options['is_active'] ) ? $this->options['is_active'] : ''  ); ?> />
				<label for="yes"><?php _e( 'Yes', 'hotmailsmtp' ); ?></label></p>			
			<p><input id="no" type="radio" name="wp_hotmail_smtp_option[is_active]" value="no" <?php checked( 'no', isset( $this->options['is_active'] ) ? $this->options['is_active'] : ''  ); ?> />
				<label for="no"><?php _e( 'No', 'hotmailsmtp' ); ?></label></p>
			<span class="description"><?php _e( "Check no if you don't want to use this plugin feature.", 'hotmailsmtp' ); ?></span>
		</fieldset>
		<?php
	}

	public function return_path_callback() {
		?>
		<fieldset><legend class="screen-reader-text"><span><?php _e( 'Return Path', 'hotmailsmtp' ); ?></span></legend><label for="mail_set_return_path">
				<input name="wp_hotmail_smtp_option[mail_set_return_path]" type="checkbox" id="mail_set_return_path" value="true" <?php checked( 'true', isset( $this->options['mail_set_return_path'] ) ? $this->options['mail_set_return_path'] : ''  ); ?>>
				<?php _e( 'Set the return-path to match the From Email', 'hotmailsmtp' ); ?></label>
		</fieldset>
		<?php
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function from_email_callback() {
		printf(
				'<input type="email" id="from_email" class="regular-text" size="40" name="wp_hotmail_smtp_option[from_email]" value="%s" /><span class="description">%s</span>', isset( $this->options['from_email'] ) ? esc_attr( $this->options['from_email'] ) : '', __( 'You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', 'hotmailsmtp' )
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function from_name_callback() {
		printf(
				'<input type="text" id="from_name" class="regular-text" size="40" name="wp_hotmail_smtp_option[from_name]" value="%s" /><span class="description">%s</span>', isset( $this->options['from_name'] ) ? esc_attr( $this->options['from_name'] ) : '', __( 'You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from WordPress.', 'hotmailsmtp' )
		);
	}

	public function smtp_encryption_callback() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e( 'Encryption', 'hotmailsmtp' ); ?></span></legend>
			<p><input <?php echo!isset( $this->options['smtp_encryption'] ) ? "checked" : ''; ?> id="smtp_ssl_none" type="radio" name="wp_hotmail_smtp_option[smtp_encryption]" value="none" <?php checked( 'none', isset( $this->options['smtp_encryption'] ) ? $this->options['smtp_encryption'] : ''  ); ?> />
				<label for="smtp_ssl_none"><?php _e( 'No encryption.', 'hotmailsmtp' ); ?></label></p>
			<p><input id="smtp_ssl_tls" type="radio" name="wp_hotmail_smtp_option[smtp_encryption]" value="tls" <?php checked( 'tls', isset( $this->options['smtp_encryption'] ) ? $this->options['smtp_encryption'] : ''  ); ?> />
				<label for="smtp_ssl_tls"><?php _e( 'Use TLS encryption.', 'hotmailsmtp' ); ?></label></p>
		</fieldset>
		<?php
	}

	public function smtp_authentication_callback() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e( 'Authentication', 'hotmailsmtp' ); ?></span></legend>
			<p><input id="smtp_auth_true" <?php echo!isset( $this->options['smtp_authentication'] ) ? 'checked="checked"' : ''; ?> type="radio" name="wp_hotmail_smtp_option[smtp_authentication]" value="true" <?php checked( 'true', isset( $this->options['smtp_authentication'] ) ? $this->options['smtp_authentication'] : ''  ); ?> />
				<label for="smtp_auth_true"><?php _e( 'Yes: Use SMTP authentication.', 'hotmailsmtp' ); ?></label></p>	
			<p><input <?php //echo!isset( $this->options['smtp_authentication'] ) ? "checked" : '';                                                                  ?> id="smtp_auth_false" type="radio" name="wp_hotmail_smtp_option[smtp_authentication]" value="false" <?php checked( 'false', isset( $this->options['smtp_authentication'] ) ? $this->options['smtp_authentication'] : ''  ); ?> />
				<label for="smtp_auth_false"><?php _e( 'No: Do not use SMTP authentication.', 'hotmailsmtp' ); ?></label></p>			
			<span class="description"><?php _e( 'If this is set to no, the values below are ignored.', 'hotmailsmtp' ); ?></span>
		</fieldset>
		<?php
	}

	public function smtp_username_callback() {
		printf(
				'<input type="text" id="smtp_username" class="regular-text" size="40" name="wp_hotmail_smtp_option[smtp_username]" value="%s" /><span class="description">%s</span>', isset( $this->options['smtp_username'] ) ? esc_attr( $this->options['smtp_username'] ) : '', __( 'Enter your SMTP username here. (Hotmail user email address)', 'hotmailsmtp' )
		);
	}

	public function smtp_password_callback() {
		printf(
				'<input type="text" id="smtp_password" class="regular-text" size="40" name="wp_hotmail_smtp_option[smtp_password]" value="%s" /><span class="description">%s</span>', isset( $this->options['smtp_password'] ) ? esc_attr( $this->options['smtp_password'] ) : '', __( 'Enter your SMTP password here. (Hotmail login password)', 'hotmailsmtp' )
		);
	}
	
	public function wp_hotmail_tracking_admin_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		/* Check that the user hasn't already clicked to ignore the message */
		if (!get_user_meta($user_id, 'wp_email_tracking_ignore_notice')) {
			?>
			<div class="updated um-admin-notice"><p><?php _e('Allow WP Hotmail Plugin to send you setup guide? Opt-in to our newsletter and we will immediately e-mail you a setup guide along with 20% discount which you can use to purchase any theme.', 'hotmailsmtp'); ?></p><p><a href="<?php echo plugin_dir_url(__FILE__) . 'smtp.php?wp_email_tracking=email_smtp_allow_tracking'; ?>" class="button button-primary"><?php _e('Allow Sending', 'hotmailsmtp'); ?></a>&nbsp;<a href="<?php echo plugin_dir_url(__FILE__) . 'smtp.php?wp_email_tracking=email_smtp_hide_tracking'; ?>" class="button-secondary"><?php _e('Do not allow', 'hotmailsmtp'); ?></a></p></div>
			<?php
		}
   }

}

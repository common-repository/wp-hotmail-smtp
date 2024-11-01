<?php
/*
  Plugin Name: WP Hotmail SMTP
  Description: WP Hotmail SMTP allows you to send all outgoing emails via Hotmail SMTP server from your WordPress site.
  Version: 1.0.5
  Author: InkThemes
  Author URI: https://www.inkthemes.com/
  License: GPLv2
 */

/*
  Copyright (C) 2016 InkThemes

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

class WPHotmail_SMTP {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;
	private $dir;

	function __construct() {
		$this->dir = plugin_dir_path( __FILE__ );
	}

	/**
	 * Initialize the plugin modules
	 */
	static function Init() {
		$obj = new WPHotmail_SMTP();
		add_action( 'admin_menu', array( $obj, 'add_plugin_page' ) );
		add_filter( 'plugin_action_links', array( $obj, 'plugin_action_links' ), 10, 2 );

		$obj->includes();

		$settings = new WPHotmail_Settings();
		$obj->options = $settings->options;
		add_action( 'admin_init', array( $settings, 'settings' ) );
		WPHotmail_Mailer::Init( $settings->options );
		add_action('admin_notices', array( $settings,'wp_hotmail_tracking_admin_notice'));
		register_deactivation_hook(__FILE__,  array( $obj,'wp_hotmail_smtp_delete_meta'));
	}

	/**
	 * Include necessary modules
	 */
	function includes() {
		include_once($this->dir . 'includes/class-smtp-settings.php');
		include_once($this->dir . 'includes/class-smtp-mailer.php');
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		add_menu_page( __( 'Hotmail SMTP', 'hotmailsmtp' ), __( 'Hotmail SMTP', 'hotmailsmtp' ), 'manage_options', 'hotmailsmtp', array( $this, 'create_admin_page' ), 'dashicons-email-alt' );
	}

	/**
	 * Add plugin setting link
	 */
	function plugin_action_links( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ ) )
			return $links;

		$settings_link = '<a href="options-general.php?page=hotmailsmtp">' . __( 'Settings', 'hotmailsmtp' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {

		function set_html_mail_content_type() {
			return 'text/html';
		}

		if ( !isset( $_REQUEST['settings-updated'] ) )
			$_REQUEST['settings-updated'] = false;

		// Load the options
		global $phpmailer;

		// Make sure the PHPMailer class has been instantiated 
		// (copied verbatim from wp-includes/pluggable.php)
		// (Re)create it, if it's gone missing
		if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new PHPMailer( true );
		}

		// Send a test mail if necessary
		if ( isset( $_POST['mgs_action'] ) && $_POST['mgs_action'] == __( 'Send Test', 'hotmailsmtp' ) && is_email( $_POST['to'] ) ) {

			check_admin_referer( 'test-email' );

			// Set up the mail variables
			$to = $_POST['to'];
			if ( isset( $_POST['subject'] ) && $_POST['subject'] != '' ) {
				$subject = sanitize_text_field( $_POST['subject'] );
			} else {
				$subject = 'WP Hotmail SMTP: ' . __( 'Test mail to ', 'hotmailsmtp' ) . $to;
			}
			if ( isset( $_POST['smtp_test_message'] ) && $_POST['smtp_test_message'] != '' ) {
				$message = wp_kses_post( $_POST['smtp_test_message'] );
			} else {
				$message = __( 'This is a test email generated by the WP Hotmail SMTP WordPress plugin.', 'hotmailsmtp' );
			}

			// Set SMTPDebug to true
			$phpmailer->SMTPDebug = true;

			// Start output buffering to grab smtp debugging output
			ob_start();
			add_filter( 'wp_mail_content_type', 'set_html_mail_content_type' );
			// Send the test mail
			$result = wp_mail( $to, $subject, $message );
			remove_filter( 'wp_mail_content_type', 'set_html_mail_content_type' );
			// Grab the smtp debugging output
			$smtp_debug = ob_get_clean();

			// Output the response
			?>
			<div id="message" class="updated fade"><p><strong><?php _e( 'Test Message Sent', 'hotmailsmtp' ); ?></strong></p>
				<p><?php _e( 'The result was:', 'hotmailsmtp' ); ?></p>
				<pre><?php var_dump( $result ); ?></pre>
				<p><?php _e( 'The full debugging output is shown below:', 'hotmailsmtp' ); ?></p>
				<pre><?php var_dump( $phpmailer ); ?></pre>
				<p><?php _e( 'The SMTP debugging output is shown below:', 'hotmailsmtp' ); ?></p>
				<pre><?php echo $smtp_debug ?></pre>
			</div>
			<?php
			// Destroy $phpmailer so it doesn't cause issues later
			unset( $phpmailer );
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'Hotmail SMTP Settings', 'hotmailsmtp' ); ?></h1>
			<div>
			   <a href="https://www.formget.com/smtp/" target="_blank">
				<img src="<?php echo plugins_url('m_bolt_img.png', __FILE__); ?>"/>
			   </a>
		   </div>
			<?php
			if ( isset( $_GET['tab'] ) ) {
				$this->admin_tabs( $_GET['tab'] );
			} else {
				$this->admin_tabs( 'smtp-option' );
			}
			if ( isset( $_GET['tab'] ) )
				$tab = $_GET['tab'];
			else
				$tab = 'smtp-option';

			switch ( $tab ) {
				case 'smtp-option':
					?>
					<form method="post" action="options.php">
						<?php
						// This prints out all hidden setting fields
						settings_fields( 'wp_hotmail_smtp_option_group' );
						do_settings_sections( 'hotmailsmtp' );	
                        echo '<p class="description">Want to send emails to your customers in bulk. <a href="https://www.formget.com/mailget-bolt/">Try MailGet here</a></p>';							
						submit_button();
						?>
					</form>
					<?php
					break;
				case 'test-email':
					$settings = array(
						'textarea_rows' => 20,
						'media_buttons' => false
					);
					$message = __( 'This is a test email generated by the WP Hotmail SMTP WordPress plugin.', 'hotmailsmtp' );
					?>
					<form method="POST">
						<?php wp_nonce_field( 'test-email' ); ?>
						<table class="optiontable form-table">
							<tr valign="top">
								<th scope="row"><label for="to"><?php _e( 'To:', 'hotmailsmtp' ); ?></label></th>
								<td>
									<input name="to" type="email" id="to" value="" size="40" class="code" />
									<span class="description"><?php _e( 'Enter an email address here and then click Send Test to generate a test email.', 'hotmailsmtp' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="subject"><?php _e( 'Subject:', 'hotmailsmtp' ); ?></label></th>
								<td>
									<input name="subject" type="text" id="subject" value="" size="40" class="code" />
									<span class="description"><?php _e( 'Enter the subject for the test email.', 'hotmailsmtp' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="message"><?php _e( 'Message:', 'hotmailsmtp' ); ?></label></th>
								<td>
									<?php wp_editor( $message, 'smtp_test_message', $settings ); ?>
									<span class="description"><?php _e( 'Enter the email description for test email.', 'hotmailsmtp' ); ?></span>
								</td>
							</tr>
						</table>
						<p class="submit"><input type="submit" name="mgs_action" id="mgs_action" class="button-primary" value="<?php _e( 'Send Test', 'hotmailsmtp' ); ?>" /></p>
					</form>
					<?php
					break;
			}
			?>
		</div>
		<?php
	}

	function admin_tabs( $current = 'smtp-option' ) {
		$tabs = array( 'smtp-option' => __( 'Email Options', 'hotmailsmtp' ), 'test-email' => __( 'Test Email', 'hotmailsmtp' ) );
		$links = array();
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=hotmailsmtp&tab=$tab'>$name</a>";
		}
		echo '</h2>';
	}
	
	public function wp_hotmail_smtp_delete_meta(){
		 global $current_user;
		 $user_id = $current_user->ID;
		 delete_user_meta($user_id, 'wp_email_tracking_ignore_notice', 'true', true);
    }

}

WPHotmail_SMTP::Init();

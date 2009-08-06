<?php
/*
Plugin Name: Social Follow
Plugin URI: http://www.socialfollow.com/
Description: An easy way to implement your Social Follow button into your sidebar.
Version: 1.0
Author: SocialFollow.com
Author URI: http://www.socialfollow.com/
Text Domain: social-follow
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================
*/
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'social-follow', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

define('SF_FOLDER', trailingslashit(ABSPATH.PLUGINDIR) . 'social-follow/');
define('SF_FILE', SF_FOLDER . 'social-follow.php');
define('SF_CSS', SF_FOLDER . 'css/');
define('SF_JS', SF_FOLDER . 'js/');

class SocialFollow {
	function SocialFollow() {
		$this->options = array(
			'button_code'
			, 'install_date'
		);
		$this->api = 'button_code';
		// not included in options
		$this->install_date = '';
		$this->version = '1.0';
	}

	function get_settings() {
		foreach ( $this->options as $option ) $this->$option = get_option( 'sf_' . $option );
	}

	// puts post fields into object propps
	function populate_settings() {
		foreach ( $this->options as $option ) {
			if ( isset( $_POST['sf_' . $option] ) ) $this->$option = stripslashes( $_POST['sf_' . $option] );
		}
	}
	
	// puts object props into wp option storage
	function update_settings() {
		if ( current_user_can( 'manage_options' ) ) {
			foreach ( $this->options as $option ) {
				update_option( 'sf_' . $option, $this->$option );
			}
			if( empty( $this->install_date ) ) update_option( 'sf_install_date', current_time( 'mysql' ) );
		}
	}
}

function sf_init() {
	global $sf;	
	$sf = new SocialFollow();
	$sf->get_settings();
	
	global $wp_version;
	if ( isset( $wp_version ) && version_compare( $wp_version, '2.5', '>=' ) && empty ( $sf->install_date ) ) {
		$updateText = array(
			__('Please update your ', 'social-follow'),
			__('Social Follow settings', 'social-follow')
		);
		add_action( 'admin_notices', create_function( '', 'echo \'<div class="error"><p>' . $updateText[0] . '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=social-follow.php" title="' . $updateText[0] . $updateText[1] . '">' . $updateText[1] . '</a>.</p></div>\';' ) );
	}
}
add_action( 'init', 'sf_init' );

function sf_menu() {
	add_options_page(
		__('Social Follow Settings', 'social-follow')
		, __('Social Follow', 'social-follow')
		, 8
		, 'social-follow.php'
		, 'sf_options'
	);
}
add_action( 'admin_menu', 'sf_menu' );

function sf_options() {
	global $sf;	
	print('
			<div class="wrap" id="sf_options_page">
				<h2>' . __('Social Follow Settings', 'social-follow') . '</h2>
				<form id="sf_settings" name="sf_settings" action="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php" method="post">
					<input type="hidden" name="sf_action" value="sf_update_settings" />
					<fieldset class="options">
						<div class="option">
							<label for="sf_button_code">' . __('Button Code', 'social-follow') . '</label>
							<textarea cols="50" rows="5" name="sf_button_code" id="sf_button_code">' . $sf->button_code . '</textarea>
							<input type="button" name="sf_button_code_check" id="sf_button_code_check" value="' . __('Verify Button Code', 'social-follow') . '" onclick="sf_checkButtonCode(); return false;" />
							<br clear="all" />
							<span id="sf_button_code_check_result"></span>
							<span class="intText" id="jsChecking">' . __('Checking button code...', 'social-follow') . '</span>
							<span class="intText" id="jsSuccess">' . __('<span class="green nomargin bold">Success!</span>', 'social-follow') . '</span>
							<span class="intText" id="jsFailure">' . __('<span class="red nomargin bold">Failed</span>', 'social-follow') . '</span>
						</div>
					</fieldset>
					<p class="submit">
						<input type="submit" name="submit" value="' . __('Update Social Follow Settings', 'social-follow') . '" />
					</p>
				</form>
			</div>
	');
}

function sf_head_admin() {
	print('
		<link rel="stylesheet" type="text/css" href="' . get_bloginfo( 'wpurl' ) . '/index.php?sf_action=sf_css_admin" />
		<script type="text/javascript" src="' . get_bloginfo( 'wpurl' ) . '/index.php?sf_action=sf_js_admin"></script>
	');
}
add_action( 'admin_head', 'sf_head_admin' );

function sf_request_handler() {
	global $sf;
	if ( !empty( $_GET['sf_action'] ) ) {
		switch( $_GET['sf_action'] ) {
			case 'sf_js_admin':
				header( "Content-Type: text/javascript" );
				readfile( SF_JS . "admin.js" );
				die();
				break;
			
			case 'sf_css_admin':
				header( "Content-Type: text/css" );
				readfile( SF_CSS . "admin.css" );
				die();
				break;
		}
	}
	
	if ( !empty( $_POST['sf_action'] ) ) {
		switch( $_POST['sf_action'] ) {
			case 'sf_update_settings':
				$sf->populate_settings(); // Populate settings variables
				$sf->update_settings(); // Update database based on populated variables
				wp_redirect( get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=social-follow.php&updated=true' );
				die();
				break;
		}
	}
}
add_action( 'init', 'sf_request_handler', 10 );

function sf_install() {
	$sf_install = new SocialFollow;
	foreach ( $sf_install->options as $option ) {
		add_option( 'sf_' . $option, $sf_install->$option );
	}
}
register_activation_hook( SF_FILE, 'sf_install' );

function sf_widget_init() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}
	function sf_widget( $args ) {
		global $sf;
		extract( $args );
		echo $before_widget;
		print( $before_title . __('Social Follow', 'social-follow') . $after_title ); 
		echo $sf->button_code;
		echo $after_widget;
	}
	register_sidebar_widget( array(__('Social Follow', 'social-follow'), 'widgets'), 'sf_widget' );
}
add_action('widgets_init', 'sf_widget_init');
?>
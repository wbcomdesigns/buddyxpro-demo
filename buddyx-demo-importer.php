<?php
/**
 * Plugin Name: BuddyX Pro Demo Importer
 * Plugin URI: https://wbcomdesigns.com/
 * Description: BuddyX Theme Demo Importer
 * Version: 3.1.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com/
 * Requires at least: 4.0
 * Tested up to: 6.8.1
 *
 * Text Domain: buddyx-demo-Importer
 * Domain Path: /i18n/languages/
 *
 * @package BuddyX_Theme_Demo_Importer
 * @category Core
 * @author Wbcom Designs
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'BDI_DIR', dirname( __FILE__ ) );
define( 'BDI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BDI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include required files for the plugin functionality
 *
 * @since 3.0.0
 * @return void
 */
function bdi_file_includes() {
	require_once BDI_PLUGIN_PATH . 'vendor/autoload.php';		
	require_once BDI_PLUGIN_PATH . 'includes/buddyx-demo-functions.php';		
}

// Include files on init
if ( ! function_exists( 'bdi_file_includes' ) ) {
	add_action( 'init', 'bdi_file_includes' );
}

// Include One Click Demo Import if not already active
if ( is_admin() ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( !class_exists( 'OCDI_Plugin' ) && ! is_plugin_active( 'one-click-demo-import/one-click-demo-import.php' )) {
		require_once BDI_PLUGIN_PATH . 'includes/one-click-demo-import/one-click-demo-import.php';
	}
}

// Include configuration file
if ( file_exists( BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php' ) ) {				
	require_once BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php';			
}

/**
 * Redirect to demo import page after plugin activation
 *
 * @since 3.0.0
 * @param string $plugin Activated plugin basename
 * @return void
 */
function bdi_activated_plugin_redirect( $plugin ) {
	$theme_name = wp_get_theme();
	if ( 'buddyx-pro' !== $theme_name->template  ) {
		return;
	}
	
	if ( $plugin == plugin_basename( __FILE__ ) ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'tgmpa-install-plugins' ) {
			wp_safe_redirect( admin_url( 'themes.php?page=one-click-demo-import' ) );
			exit;
		} else {
			wp_redirect( admin_url( 'themes.php?page=one-click-demo-import' ) );
			exit;
		}
	}
}
add_action( 'activated_plugin', 'bdi_activated_plugin_redirect' );

/**
 * Add demo-specific plugins to the plugin installer list
 *
 * @since 3.0.0
 * @param array $plugins Existing plugins array
 * @return array Modified plugins array
 */
function buddyx_demo_plugin_installer( $plugins ) {
	if ( ( isset( $_GET['page'] ) && ( $_GET['page'] == 'buddyx-sample-demo-import' || $_GET['page'] == 'tgmpa-install-plugins' ) ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX == true ) ) {
		$plugins[] = array(
			'name'     => 'BuddyPress',
			'slug'     => 'buddypress',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'BuddyBoss Platform',
			'slug'     => 'buddyboss-platform',
			'source'   => 'https://github.com/buddyboss/buddyboss-platform/releases/download/2.8.80/buddyboss-platform-plugin.zip',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Wbcom Essential',
			'slug'     => 'wbcom-essential',
			'source'   => 'https://demos.wbcomdesigns.com/exporter/plugins/wbcom-essential/3.9.4/wbcom-essential.zip',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Dokan',
			'slug'     => 'dokan-lite',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'ElementsKit Lite',
			'slug'     => 'elementskit-lite',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Tutor LMS',
			'slug'     => 'tutor',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'LearnPress',
			'slug'     => 'learnpress',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'LifterLMS',
			'slug'     => 'lifterlms',
			'required' => false,
		);
	}
	return $plugins;
}
add_filter( 'buddyx_plugin_install', 'buddyx_demo_plugin_installer' );

/**
 * Activate default BuddyPress components on new install
 *
 * @since 3.0.0
 * @param array $components Default components
 * @return array Modified components array
 */
function buddyxpro_demo_bp_default_components( $components ) {
	$components['groups']   = 1;
	$components['friends']  = 1;
	$components['messages'] = 1;
	return $components;
}
add_action( 'bp_new_install_default_components', 'buddyxpro_demo_bp_default_components', 99, 1 );

// Initialize plugin update checker
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://demos.wbcomdesigns.com/exporter/free-plugins/buddyxpro-demo-importer.json',
	__FILE__,
	'buddyxpro-demo-importer'
);

/**
 * Check if BuddyX Pro theme is active
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_reactions_requires_buddyx() {
	$theme_name = wp_get_theme();
		
	if ( 'buddyx-pro' !== $theme_name->template ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'buddyx_demo_reactions_required_theme_admin_notice' );
		unset( $_GET['activate'] );
	}
}
add_action( 'admin_init', 'buddyx_demo_reactions_requires_buddyx' );

/**
 * Display admin notice when BuddyX Pro theme is not active
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_reactions_required_theme_admin_notice() {
	$bpreaction_plugin = esc_html__( ' BuddyX Pro Demo Importer', 'buddyx-demo-Importer' );
	$bp_theme = esc_html__( 'BuddyX Pro ', 'buddyx-demo-Importer' );
	echo '<div class="error"><p>';
	echo sprintf( 
		esc_html__( '%1$s is ineffective now as it requires %2$s theme to be installed and active.', 'buddyx-demo-Importer' ), 
		'<strong>' . esc_html( $bpreaction_plugin ) . '</strong>', 
		'<strong>' . esc_html( $bp_theme ) . '</strong>' 
	);
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Register and enqueue admin styles
 *
 * @since 3.0.0
 * @param string $hook Current admin page hook
 * @return void
 */
function bdi_admin_enqueue_scripts( $hook ) {
	// Only load on our admin pages
	if ( ! in_array( $hook, array( 'themes_page_one-click-demo-import', 'themes_page_buddyx-demo-delete-data' ) ) ) {
		return;
	}
	
	$plugin_version = '3.0.0';
	wp_enqueue_style(
		'buddyxpro-demo',
		plugin_dir_url( __FILE__ ) . 'assets/css/buddyxpro-demo.css',
		array(),
		$plugin_version
	);
}
add_action( 'admin_enqueue_scripts', 'bdi_admin_enqueue_scripts' );

/**
 * Add delete demo data submenu page
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_add_admin_menu() {
	add_submenu_page(
		'themes.php',
		'Delete Demo Data',
		'Delete Demo Data',
		'manage_options',
		'buddyx-demo-delete-data',
		'buddyx_demo_data_delete' 
	);
}
add_action( 'admin_menu', 'buddyx_demo_add_admin_menu' );

/**
 * Display delete demo data admin page
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_data_delete() {
	// Verify nonce if form was submitted
	if ( ! empty( $_POST['buddyx-admin-clear'] ) ) {
		check_admin_referer( 'buddyx-admin' );
		
		if ( class_exists( 'buddypress' ) ) {
			buddyx_bp_clear_db();
		}
		buddyx_demo_clear_db();
		echo '<div id="message" class="updated fade"><p>' . esc_html__( 'Everything created by this plugin was successfully deleted.', 'buddyx-demo-Importer' ) . '</p></div>';
	}
	?>
	<div class="wrap" id="buddyx-default-data-page">
		<h1><?php esc_html_e( 'Delete BuddyX Default Data', 'buddyx-demo-Importer' ); ?></h1>
		<form action="" method="post" id="buddyx-admin-form">
			<p class="submit">
				<input class="button" type="submit" name="buddyx-admin-clear" id="buddyx-admin-clear" value="<?php esc_attr_e( 'Clear BuddyX Default Data', 'buddyx-demo-Importer' ); ?>" />
			</p>
			<?php wp_nonce_field( 'buddyx-admin' ); ?>
		</form>
	</div>
	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {			
			jQuery( '#buddyx-admin-clear' ).click( function() {
				if ( confirm( '<?php echo esc_js( esc_html__( 'Are you sure you want to delete all *imported* content - users, groups, messages, activities, forum topics etc? Content, that was created by you and others, and not by this plugin, will not be deleted.', 'buddyx-demo-Importer' ) ); ?>' ) ) {
					return true;
				}
				return false;
			} );			
		} );
	</script>
	<?php
}

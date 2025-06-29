<?php
/**
 * BuddyX Demo Importer Configuration
 *
 * @package BuddyX_Theme_Demo_Importer
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove admin init functions that interfere with demo import
 *
 * @since 3.0.0
 * @return void
 */
function bdi_remove_admin_init() {
	// Handle WooCommerce activation during demo import
	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ocdi_install_plugin' && isset( $_REQUEST['slug'] ) && $_REQUEST['slug'] == 'woocommerce') {
		update_option( 'woocommerce_onboarding_profile', [ 'completed'=> true, 'skipped' => true ]);
		// Remove the redirect transient set by WooCommerce after activation
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
		}
	}
	
	// Pass activate multi plugin in get request
	if( isset( $_REQUEST['action'] ) && ( $_REQUEST['action'] == 'ocdi_install_plugin' || $_REQUEST['action'] == 'ocdi_import_demo_data' ) ) {
		$_GET['activate-multi'] = true;
		if( isset( $_REQUEST['slug'] ) && $_REQUEST['slug'] == 'dokan-lite' ) {
			update_option( 'dokan_theme_version', true );
		}
	}
	
	// Prevent plugin activation redirects during demo import
	if ( ( isset( $_GET['page'] ) 
		&& ( 
			$_GET['page'] == 'buddyx-sample-demo-import' 
			|| $_GET['page'] == 'tgmpa-install-plugins' 
			|| $_GET['page'] == 'one-click-demo-import' 
			) )
			
			|| ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ocdi_install_plugin')
	
		) {
		// Remove the redirect transient set by WooCommerce after activation
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
		}
		
		remove_action( 'admin_init', 'is_admin_init' );
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false');		
		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_false' );
		
		update_option( 'wpforms_activation_redirect', true );
		if ( did_action( 'elementor/loaded' ) ) {
			remove_action( 'admin_init', array( \Elementor\Plugin::$instance->admin, 'maybe_redirect_to_getting_started' ) );
		}
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			remove_action( '_admin_menu', 'tribe_remove_activation_page', 20 );
		}
	}
}
add_action( 'admin_init', 'bdi_remove_admin_init', 0 );

/**
 * Disable LearnDash setup wizard during demo import
 *
 * @since 3.0.0
 * @return void
 */
function bdi_ocdi_lms_redirect_flag() {
	if ( ( isset( $_GET['page'] )
		&& (
			$_GET['page'] == 'buddyx-sample-demo-import'
			|| $_GET['page'] == 'tgmpa-install-plugins'
			|| $_GET['page'] == 'one-click-demo-import'
			) )

			|| ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ocdi_install_plugin' )

		) {
		add_filter( 'learndash_setup_wizard_should_display', '__return_false', 99 );
	}
}
add_action( 'learndash_activated', 'bdi_ocdi_lms_redirect_flag', 0 );

/**
 * Define available demo imports
 *
 * @since 3.0.0
 * @return array Array of demo configurations
 */
function bdi_ocdi_import_files() {	
	return [
		[
			'import_file_name'             	=> 'BuddyX Pro with BuddyPress',
			'categories'                   	=> [],		
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/buddypress/demo-content.xml',		
			'local_import_products_file'	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/buddypress/demo-products-content.xml',
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/buddypress/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/buddypress/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/buddy-demo-bp.jpg',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://pro.buddyxtheme.com/',
			'import_plugins'             	=> [ 'elementor', 'kirki', 'buddypress', 'woocommerce', 'wbcom-essential' ],
		],
		[
			'import_file_name'           	=> 'BuddyX Pro with BuddyBoss',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/bb-platform/demo-content.xml',
			'local_import_page_file'    	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/bb-platform/demo-bb-page-content.xml',
			'local_import_products_file'	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/bb-platform/demo-products-content.xml',
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/bb-platform/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/bb-platform/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/probuddyx-demo.jpg',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://bb-pro.buddyxtheme.com/',
			'import_plugins'             	=> [  'elementor', 'classic-widgets', 'kirki', 'buddyboss-platform', 'woocommerce', 'wbcom-essential' ],
		],	
		[
			'import_file_name'           	=> 'BuddyX Pro with Dokan',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/dokan/demo-content.xml',		
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/dokan/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/dokan/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-Pro-with-Dokan-Pro.png',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/dokan-pro/',
			'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'dokan-lite', 'woocommerce' ],
		],	
		[
			'import_file_name'           	=> 'BuddyX Pro with LearnDash',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learndash/demo-content.xml',		
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learndash/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learndash/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-Pro-with-LearnDash.png',
			'import_notice'              	=> __( 'Please install and activate LearnDash LMS plugin before import demo.', 'buddyx-demo-Importer' ),
			'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/pro-learndash/',
			'import_plugins'             	=> [ 'elementor', 'elementskit-lite', 'classic-widgets', 'kirki', 'wbcom-essential' ],
			'required_plugins'  			=> [
				'sfwd-lms/sfwd_lms.php' => 'LearnDash LMS',
			],
		],	
		[
			'import_file_name'           	=> 'BuddyX Pro with LifterLMS',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/lifterlms/demo-content.xml',		
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/lifterlms/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/lifterlms/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-Pro-with-LifterLMS.png',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/pro-lifterlms',
			'import_plugins'             	=> [ 'elementor', 'elementskit-lite', 'classic-widgets', 'kirki', 'lifterlms', 'wbcom-essential' ],
		],	
		[
			'import_file_name'           	=> 'BuddyX Pro with TutorLMS',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/tutorlms/demo-content.xml',		
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/tutorlms/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/tutorlms/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-Pro-With-TutorLMS.png',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/pro-tutorlms/',
			'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'tutor', 'wbcom-essential' ],
		],	
		[
			'import_file_name'           	=> 'BuddyX Pro with LearnPress',
			'categories'                   	=> [],
			'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learnpress/demo-content.xml',		
			'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learnpress/widgets.json',
			'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyxpro/learnpress/customizer.dat',
			'local_import_redux'            => [],
			'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-Pro-With-LearnPress.png',
			'import_notice'              	=> '',
			'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/pro-learnpress/',
			'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'learnpress', 'wbcom-essential' ],
		],
	];
}
add_filter( 'ocdi/import_files', 'bdi_ocdi_import_files' );

/**
 * Register required plugins for demo imports
 *
 * @since 3.0.0
 * @param array $plugins Existing plugins array
 * @return array Modified plugins array with demo-specific requirements
 */
function bdi_ocdi_register_plugins( $plugins ) {

	// Required: List of plugins used by all theme demos.
	$theme_plugins = array();
	
	$theme_plugins[] = array(
		'name'     => 'Elementor',
		'slug'     => 'elementor',
		'required' => true,
	);
	$theme_plugins[] = array(
		'name'     => 'Kirki',
		'slug'     => 'kirki',
		'required' => true,
	);
	$theme_plugins[] = array(
		'name'     => 'Classic Widgets',
		'slug'     => 'classic-widgets',
		'required' => true,
	);

	// Check if user is on the theme recommended plugins step and a demo was selected.
	if ( ( isset( $_GET['step'] ) && $_GET['step'] === 'import' && isset( $_GET['import'] ) ) || ( isset( $_POST['slug'] ) && $_POST['slug'] != '' ) ) {

		// BuddyPress demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '0' ) {
			$theme_plugins[] = array(
				'name'     => 'BuddyPress',
				'slug'     => 'buddypress',
				'required' => true,
			);
		}

		// LearnDash demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '3' ) {
			$theme_plugins[] = array(
				'name'     => 'LearnDash LMS',
				'slug'     => 'sfwd-lms',
				'required' => true,
			);
		}

		// Wbcom Essential for multiple demos
		if ( ( isset( $_GET['import'] ) && in_array( $_GET['import'], array( '0', '1', '2', '3', '4', '5', '6' ) ) ) || ( isset( $_POST['slug'] ) && $_POST['slug'] === 'wbcom-essential' ) ) {
			$theme_plugins[] = array(
				'name'     => 'Wbcom Essential',
				'slug'     => 'wbcom-essential',
				'source'   => 'https://demos.wbcomdesigns.com/exporter/plugins/wbcom-essential/3.9.4/wbcom-essential.zip',
				'required' => true,
			);
		}

		// BuddyBoss Platform demo
		if ( ( isset( $_GET['import'] ) && $_GET['import'] === '1' ) || ( isset( $_POST['slug'] ) && $_POST['slug'] === 'buddyboss-platform' ) ) {
			$theme_plugins[] = array(
				'name'     => 'BuddyBoss Platform',
				'slug'     => 'buddyboss-platform',
				'source'   => 'https://github.com/buddyboss/buddyboss-platform/releases/download/2.8.80/buddyboss-platform-plugin.zip',
				'required' => true,
			);
		}

		// ElementsKit for LearnDash demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '3' ) {
			$theme_plugins[] = array(
				'name'     => 'ElementsKit Lite',
				'slug'     => 'elementskit-lite',
				'required' => true,
			);
		}

		// LearnPress demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '6' ) {
			$theme_plugins[] = array(
				'name'     => 'ElementsKit Lite',
				'slug'     => 'elementskit-lite',
				'required' => true,
			);
			$theme_plugins[] = array(
				'name'     => 'LearnPress',
				'slug'     => 'learnpress',
				'required' => true,
			);
		}

		// LifterLMS demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '4' ) {
			$theme_plugins[] = array(
				'name'     => 'ElementsKit Lite',
				'slug'     => 'elementskit-lite',
				'required' => true,
			);
			$theme_plugins[] = array(
				'name'     => 'LifterLMS',
				'slug'     => 'lifterlms',
				'required' => true,
			);
		}

		// TutorLMS demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '5' ) {
			$theme_plugins[] = array(
				'name'     => 'Tutor LMS',
				'slug'     => 'tutor',
				'required' => true,
			);
		}

		// WooCommerce for BuddyPress and BuddyBoss demos
		if ( isset( $_GET['import'] ) && in_array( $_GET['import'], array( '0', '1' ) ) ) {
			$theme_plugins[] = array(
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => false,
			);
		}

		// WooCommerce for Dokan demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '2' ) {
			$theme_plugins[] = array(
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
			);
		}

		// Dokan demo
		if ( isset( $_GET['import'] ) && $_GET['import'] === '2' ) {
			$theme_plugins[] = array(
				'name'     => 'Dokan',
				'slug'     => 'dokan-lite',
				'required' => true,
			);
		}
	}

	return $theme_plugins;
}
add_filter( 'ocdi/register_plugins', 'bdi_ocdi_register_plugins' );

/**
 * Setup after import completion
 *
 * @since 3.0.0
 * @param array $selected_import Selected import data
 * @return void
 */
function bdi_ocdi_after_import_setup( $selected_import ) {
	global $wpdb;
	
	// Set static homepage
	$homepage = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Homepage' ) );
	if ( $homepage ) {
		update_option( 'page_on_front', $homepage->ID );
		update_option( 'show_on_front', 'page' );
	}

	$home = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Home' ) );
	if ( $home ) {
		update_option( 'page_on_front', $home->ID );
		update_option( 'show_on_front', 'page' );
	}

	// Set static blog page
	$blogpage = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Blog' ) );
	if ( $blogpage ) {
		update_option( 'page_for_posts', $blogpage->ID );
		update_option( 'show_on_front', 'page' );
	}

	// Assign Import Menus
	$locations = get_theme_mod( 'nav_menu_locations' );
	$registered_menus = wp_get_nav_menus();
	
	// Assign Menu Name to Registered menus as array keys
	foreach( $registered_menus as $menu ) {
		if ( $menu->slug == 'main-menu' && strtolower($menu->name) == strtolower('Main Menu') ) {
			$locations['primary'] = $menu->term_id;
		}
		if ( $menu->slug == 'primary-menu' && strtolower($menu->name) == strtolower('Primary Menu') ) {
			$locations['primary'] = $menu->term_id;
		}
		if ( $menu->slug == 'user-menu' && strtolower($menu->name) == strtolower('User Menu') ) {
			$locations['user_menu'] = $menu->term_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	
	// Update Custom URL in menu
	if ( isset( $selected_import['preview_url'] ) && ! empty( $selected_import['preview_url'] ) ) {
		$preview_url = trailingslashit( $selected_import['preview_url'] );
		$replace_string = trailingslashit( get_site_url() );
		
		// Use prepared statement for security
		$query = $wpdb->prepare( 
			"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = %s", 
			'_menu_item_url' 
		);
		$results = $wpdb->get_results( $query );
		
		if ( ! empty( $results ) ) {
			foreach( $results as $res ) {		
				if ( trim( $res->meta_value ) != '' && strpos( $res->meta_value, $preview_url ) !== false ) {
					$meta_value = str_replace( $preview_url, $replace_string, $res->meta_value );
					$wpdb->update( 
						"{$wpdb->prefix}postmeta",
						array( 'meta_value' => $meta_value ),
						array( 'meta_id' => $res->meta_id ),
						array( '%s' ),
						array( '%d' )
					);
				}
			}
		}
	}
}
add_action( 'ocdi/after_import', 'bdi_ocdi_after_import_setup' );

/**
 * Import BuddyPress demo data after content import
 *
 * @since 3.0.0
 * @param array $selected_import_files Selected import files
 * @param array $import_files All import files
 * @param int   $selected_index Selected demo index
 * @return void
 */
function bdi_ocdi_after_content_import_execution( $selected_import_files, $import_files, $selected_index ) {
	// Import BuddyPress data for relevant demos
	if( in_array( $selected_index, array( 0, 1, 7 ) ) ) {
		bdi_import_buddypress_demo_data();
	}
}
add_action( 'ocdi/after_content_import_execution', 'bdi_ocdi_after_content_import_execution', 10, 3);

/**
 * Import BuddyPress demo data
 *
 * @since 3.0.0
 * @return bool True on success
 */
function bdi_import_buddypress_demo_data() {
	// Count what we have just imported
	$imported = array();

	// Include BuddyPress processing functions
	include_once BDI_PLUGIN_PATH . '/bp-process.php';

	// Import users
	if ( ! buddyx_bp_is_imported( 'users', 'users' ) ) {
		$users = buddyx_bp_import_users();
		$imported['users'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s new users', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $users ) )
		);
		buddyx_bp_update_import( 'users', 'users' );
	}

	// Import xProfile data
	if ( bp_is_active( 'xprofile' ) && ! buddyx_bp_is_imported( 'users', 'xprofile' ) ) {
		$profile = buddyx_bp_import_users_profile();
		$imported['profile'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s profile entries', 'buddyx-demo-Importer' ),
			number_format_i18n( $profile )
		);
		buddyx_bp_update_import( 'users', 'xprofile' );
	}

	// Import friends connections
	if ( bp_is_active( 'friends' ) && ! buddyx_bp_is_imported( 'users', 'friends' ) ) {
		$friends = buddyx_bp_import_users_friends();
		$imported['friends'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s friends connections', 'buddyx-demo-Importer' ),
			number_format_i18n( $friends )
		);
		buddyx_bp_update_import( 'users', 'friends' );
	}

	// Import activity
	if ( bp_is_active( 'activity' ) && ! buddyx_bp_is_imported( 'users', 'activity' ) ) {
		$activity = buddyx_bp_import_users_activity();
		$imported['activity'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s personal activity items', 'buddyx-demo-Importer' ),
			number_format_i18n( $activity )
		);
		buddyx_bp_update_import( 'users', 'activity' );
	}

	// Import groups
	if ( bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'groups' ) ) {
		$groups = buddyx_bp_import_groups();
		$imported['groups'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s new groups', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $groups ) )
		);
		buddyx_bp_update_import( 'groups', 'groups' );
	}

	// Import group members
	if ( bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'members' ) ) {
		$g_members = buddyx_bp_import_groups_members();
		$imported['g_members'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s groups members (1 user can be in several groups)', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $g_members ) )
		);
		buddyx_bp_update_import( 'groups', 'members' );
	}

	// Import group activity
	if ( bp_is_active( 'activity' ) && bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'activity' ) ) {
		$g_activity = buddyx_bp_import_groups_activity();
		$imported['g_activity'] = sprintf( 
			/* translators: formatted number. */
			esc_html__( '%s groups activity items', 'buddyx-demo-Importer' ),
			number_format_i18n( $g_activity )
		);
		buddyx_bp_update_import( 'groups', 'activity' );
	}

	return true;
}

/**
 * Customize the plugin page title
 *
 * @since 3.0.0
 * @return string HTML for the plugin page title
 */
function bdi_ocdi_plugin_page_title() {
	ob_start(); ?>
	<div class="ocdi__title-container">
		<h1 class="ocdi__title-container-title"><?php esc_html_e( 'BuddyX Pro Demo Import', 'one-click-demo-import' ); ?></h1>
		<a href="https://ocdi.com/user-guide/" target="_blank" rel="noopener noreferrer">
			<img class="ocdi__title-container-icon" src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/question-circle.svg' ); ?>" alt="<?php esc_attr_e( 'Questionmark icon', 'one-click-demo-import' ); ?>">
		</a>
	</div>
	<?php
	return ob_get_clean();
}
add_filter( 'ocdi/plugin_page_title', 'bdi_ocdi_plugin_page_title');
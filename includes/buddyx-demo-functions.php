<?php
/**
 * BuddyX Demo Functions
 *
 * @package BuddyX_Theme_Demo_Importer
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin admin area root page
 *
 * @since 3.0.0
 * @return string Admin page slug (settings.php for multisite, tools.php for single site)
 */
function buddyx_bp_get_root_admin_page() {
	return is_multisite() ? 'settings.php' : 'tools.php';
}

/**
 * Delete all imported BuddyPress data
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_bp_clear_db() {
	global $wpdb;
	$bp = buddypress();

	// Delete imported groups
	$groups = bp_get_option( 'buddyx_bp_imported_group_ids' );
	if ( ! empty( $groups ) ) {
		foreach ( (array) $groups as $group_id ) {
			groups_delete_group( $group_id );
		}
	}

	// Delete imported users and all their data
	$users = bp_get_option( 'buddyx_bp_imported_user_ids' );
	if ( ! empty( $users ) ) {
		foreach ( (array) $users as $user_id ) {
			bp_core_delete_account( $user_id );
		}
	}

	// Delete xProfile groups and fields
	$xprofile_ids = bp_get_option( 'buddyx_bp_imported_user_xprofile_ids' );
	if ( ! empty( $xprofile_ids ) ) {
		foreach ( (array) $xprofile_ids as $xprofile_id ) {
			$group = new BP_XProfile_Group( $xprofile_id );
			$group->delete();
		}
	}

	// Clean up import records
	buddyx_bp_delete_import_records();
}

/**
 * Delete all imported demo content
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_clear_db() {
	// Process posts in batches to avoid memory issues
	$batch_size = 100;
	$offset = 0;
	
	while ( true ) {
		$args = array(
			'post_type'      => 'any',
			'posts_per_page' => $batch_size,
			'offset'         => $offset,
			'post_status'    => 'any',
			'order'          => 'ASC',
			'meta_key'       => '_demo_data_imported',
			'meta_value'     => 1,
			'fields'         => 'ids'
		);
		
		$post_ids = get_posts( $args );
		
		if ( empty( $post_ids ) ) {
			break;
		}
		
		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		
		$offset += $batch_size;
		
		// Clear memory
		wp_cache_flush();
	}
	
	// Delete Nav Menu items in batches
	$offset = 0;
	while ( true ) {
		$args = array(
			'post_type'      => array( 'nav_menu_item', 'bp-email', 'wp_navigation', 'wp_global_styles' ),
			'posts_per_page' => $batch_size,
			'offset'         => $offset,
			'post_status'    => 'any',
			'meta_key'       => '_demo_data_imported',
			'meta_value'     => 1,
			'fields'         => 'ids'
		);
		
		$post_ids = get_posts( $args );
		
		if ( empty( $post_ids ) ) {
			break;
		}
		
		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		
		$offset += $batch_size;
		
		// Clear memory
		wp_cache_flush();
	}
}

/**
 * Fix date for group join activities
 *
 * @since 3.0.0
 * @param array $args Arguments passed to bp_activity_add()
 * @return array Modified arguments
 * @throws Exception
 */
function buddyx_bp_groups_join_group_date_fix( $args ) {
	if ( $args['type'] === 'joined_group' && $args['component'] === 'groups' ) {
		$args['recorded_time'] = buddyx_bp_get_random_date( 25, 1 );
	}
	return $args;
}

/**
 * Fix date for friend connections
 *
 * @since 3.0.0
 * @param string $current_time Default BuddyPress current timestamp
 * @return int Modified timestamp
 * @throws Exception
 */
function buddyx_bp_friends_add_friend_date_fix( $current_time ) {
	return strtotime( buddyx_bp_get_random_date( 43 ) );
}

/**
 * Get random group IDs
 *
 * @since 3.0.0
 * @param int    $count  Number of groups to get (0 for all)
 * @param string $output Return format ('array' or 'string')
 * @return array|string Array of group IDs or comma-separated string
 */
function buddyx_bp_get_random_groups_ids( $count = 1, $output = 'array' ) {
	$groups_arr = (array) bp_get_option( 'buddyx_bp_imported_group_ids' );

	if ( ! empty( $groups_arr ) ) {
		$total_groups = count( $groups_arr );
		if ( $count <= 0 || $count > $total_groups ) {
			$count = $total_groups;
		}

		// Get random groups
		$random_keys = (array) array_rand( $groups_arr, $count );
		$groups = array();
		foreach ( $groups_arr as $key => $value ) {
			if ( in_array( $key, $random_keys, true ) ) {
				$groups[] = $value;
			}
		}
	} else {
		global $wpdb;
		$bp = buddypress();

		$limit = '';
		if ( $count > 0 ) {
			$limit = 'LIMIT ' . (int) $count;
		}

		$query = $wpdb->prepare( 
			"SELECT id FROM {$bp->groups->table_name} ORDER BY RAND() %s", 
			$limit 
		);
		$groups = $wpdb->get_col( $query );
	}

	// Convert to integers
	$groups = array_map( 'intval', $groups );

	if ( $output === 'string' ) {
		return implode( ',', $groups );
	}

	return $groups;
}

/**
 * Get random user IDs
 *
 * @since 3.0.0
 * @param int    $count  Number of users to get (0 for all)
 * @param string $output Return format ('array' or 'string')
 * @return array|string Array of user IDs or comma-separated string
 */
function buddyx_bp_get_random_users_ids( $count = 1, $output = 'array' ) {
	$users_arr = (array) bp_get_option( 'buddyx_bp_imported_user_ids' );

	if ( ! empty( $users_arr ) ) {
		$total_members = count( $users_arr );
		if ( $count <= 0 || $count > $total_members ) {
			$count = $total_members;
		}

		// Get random users
		$random_keys = (array) array_rand( $users_arr, $count );
		$users = array();
		foreach ( $users_arr as $key => $value ) {
			if ( in_array( $key, $random_keys, true ) ) {
				$users[] = $value;
			}
		}
	} else {
		// Get all registered users if no imported users found
		$users = get_users( array( 'fields' => 'ID' ) );
	}

	// Convert to integers
	$users = array_map( 'intval', $users );

	if ( $output === 'string' ) {
		return implode( ',', $users );
	}

	return $users;
}

/**
 * Get random date in the past
 *
 * @since 3.0.0
 * @param int $days_from Maximum days in the past
 * @param int $days_to   Minimum days in the past
 * @return string Random date in 'Y-m-d H:i:s' format
 */
function buddyx_bp_get_random_date( $days_from = 30, $days_to = 0 ) {
	// Ensure $days_from is always greater than $days_to
	if ( $days_to > $days_from ) {
		$days_to = $days_from - 1;
	}

	try {
		$date_from = new DateTime( 'now - ' . $days_from . ' days' );
		$date_to   = new DateTime( 'now - ' . $days_to . ' days' );
		$date = date( 'Y-m-d H:i:s', wp_rand( $date_from->getTimestamp(), $date_to->getTimestamp() ) );
	} catch ( Exception $e ) {
		$date = date( 'Y-m-d H:i:s' );
	}

	return $date;
}

/**
 * Get current timestamp using blog timezone
 *
 * @since 3.0.0
 * @return int Current timestamp
 */
function buddyx_bp_get_time() {
	return (int) current_time( 'timestamp' );
}

/**
 * Check if specific data was already imported
 *
 * @since 3.0.0
 * @param string $group  Import group ('users' or 'groups')
 * @param string $import Import type
 * @return bool True if already imported
 */
function buddyx_bp_is_imported( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	if ( ! in_array( $group, array( 'users', 'groups' ), true ) ) {
		return false;
	}

	$imported = (array) bp_get_option( 'buddyx_bp_import_' . $group );
	return array_key_exists( $import, $imported );
}

/**
 * Output disabled attribute if already imported
 *
 * @since 3.0.0
 * @param string $group  Import group
 * @param string $import Import type
 * @return void
 */
function buddyx_bp_imported_disabled( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	echo buddyx_bp_is_imported( $group, $import ) ? 'disabled="disabled" checked="checked"' : 'checked="checked"';
}

/**
 * Mark data as imported
 *
 * @since 3.0.0
 * @param string $group  Import group
 * @param string $import Import type
 * @return bool True on success
 */
function buddyx_bp_update_import( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	$values = bp_get_option( 'buddyx_bp_import_' . $group, array() );
	$values[ $import ] = buddyx_bp_get_time();

	return bp_update_option( 'buddyx_bp_import_' . $group, $values );
}

/**
 * Delete all import tracking records
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_bp_delete_import_records() {
	bp_delete_option( 'buddyx_bp_import_users' );
	bp_delete_option( 'buddyx_bp_import_groups' );
	bp_delete_option( 'buddyx_bp_imported_user_ids' );
	bp_delete_option( 'buddyx_bp_imported_group_ids' );
	bp_delete_option( 'buddyx_bp_imported_user_xprofile_ids' );
}
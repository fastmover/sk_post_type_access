<?php
/**
 * Plugin Name: SK Post Type Access
 * Plugin URI: http://StevenKohlmeyer.com/sk_post_type_access_plugin
 * Description: This restricts content types to a specific role
 * Version: 0.0.1
 * Author: Fastmover
 * Author URI: http://StevenKohlmeyer.com
 * License: GPLv2 or later
 */



class SK_PostTypeAccess {

  public static $optionsPageSlug = "sk_post_type_access_options_page";

  function __construct() {

    add_action( 'pre_get_posts', 'SK_PostTypeAccess::post_access' );

  }

  public static function post_access($query = '', $arg2 = '', $arg3 = '') {

    if( count($query->query) < 1 )
      return;

    if(!$query->is_main_query())
      return;


    global $userdata; // null if not logged in

    if(null == $userdata) {

      $userCapabilities = self::getRoleCapabilities('anonymous');

    } else {

      $userCapabilities = $userdata->allcaps;

    }

    $thisPostType = $query->query['post_type'];
    $thisPostTypeReadCap = 'read_' . $thisPostType;
    $userCapabilitiesKeys = array_keys($userCapabilities);
    $userGranted = in_array($thisPostTypeReadCap, $userCapabilitiesKeys);

    if(!$userGranted) {

      $query->query['post_type'] = null;
      $query->query_vars['post_type'] = null;
      $query->is_404 = true;

    }


  }

  public static function getRoleCapabilities($role) {

    global $wp_roles;
    $roleCapabilities = $wp_roles->roles[strtolower($role)]['capabilities'];

    return $roleCapabilities;

  }

  public static function getRoles() {

    global $wp_roles;

    if ( ! isset( $wp_roles ) )

      $wp_roles = new WP_Roles();

    return $wp_roles;

  }

}

$SK_PTA = new SK_PostTypeAccess();

register_activation_hook( __FILE__, function() {

  $wp_roles = SK_PostTypeAccess::getRoles();
  $roles = array_keys($wp_roles->role_names);

  if( in_array( 'anonymous', $roles ) )
    return;

  add_role(
    'anonymous',
    'Anonymous',
    array(
      'read'              => true,
      'read_page'         => true,
      'read_post'         => true,
      'read_attachment'   => true
    )
  );

});


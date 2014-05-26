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

  public static $optionsPageSlug              = "sk_post_type_access_options_page";
  public static $optionPageContentTypeGroup   = "sk-role-content-type-group";

  function __construct() {

    add_action( 'pre_get_posts', 'SK_PostTypeAccess::post_access' );

    if ( is_admin() ){

      add_action( 'admin_init',       'SK_PostTypeAccess::registerSettings' );
      add_action( 'admin_menu',       'SK_PostTypeAccess::adminMenu' );

    }

  }

  public static function post_access($query = '', $arg2 = '', $arg3 = '') {

    if("checked" === get_option('disable_plugin'))
      return;

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

  public static function registerSettings() {

    register_setting( SK_PostTypeAccess::$optionPageContentTypeGroup, 'disable_plugin' );

    if(SK_PostTypeAccess::$optionPageContentTypeGroup === $_POST['option_page'] and "update" === $_POST['action'] ) {
      self::updatePermissions();
    }

    global $plugin_page;

    $asdf = 'asdf';

    if($plugin_page == SK_PostTypeAccess::$optionsPageSlug) {

      add_action('admin_enqueue_scripts', function() {

        wp_register_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_style( 'jquery-ui-css' );
        wp_register_script( 'sk-post-type-access', plugin_dir_url( __FILE__ ) . 'script.js' );

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('sk-post-type-access');

      });


    }


  }

  public static function adminMenu() {

    add_users_page(
      'Post Type Access',
      'Post Type Access',
      'activate_plugins',
      SK_PostTypeAccess::$optionsPageSlug,
      'SK_PostTypeAccess::optionsPage'
    );

  }

  public static function getPostTypeObjects() {

    global $optionsPageSlug;
    $postTypes = get_post_types();
    $postTypeObjects = array();
    foreach($postTypes as $postType => $pType) {

      $postTypeObjects[] = get_post_type_object($pType);

    }
    return $postTypeObjects;

  }

  public static function listCustomPostTypes() {

    $roles      = SK_PostTypeAccess::getRoles();
    $postTypes  = SK_PostTypeAccess::getPostTypeObjects();
    $lastPostType = '';

    ?>
    <div class="accordion" id="accordion">
      <?php

      foreach($postTypes as $postType) {

        ?>
        <h3 class="parent">
          <?=$postType->labels->name; ?>
        </h3>
        <div>
          <div class="accordion2" id="accordion-nested">
            <?php

            foreach($roles->roles as $role) {

              $roleCapabilities   = self::getRoleCapabilities($role['name']);
              $roleCapabilityKeys = array_keys($roleCapabilities);

              ?>

              <h4 class="child">
                <?=$role['name']; ?>
              </h4>
              <div>
                <p>
                  <?php

                  foreach($postType->cap as $capability) {

                    if( false === strpos($capability, 'read_') ) {
                      continue;
                    }

                    $checked = '';

                    if( in_array( $capability, $roleCapabilityKeys ) ) {

                      $checked = 'checked';

                    }

                    ?>
                    <input type="checkbox" id="<?=$capability; ?>" name="skRoles[<?=strtolower($role['name']); ?>_<?=strtolower($postType->labels->name); ?>_<?=$capability; ?>]" value="true" <?=$checked; ?>/>
                    <label for="<?=$capability; ?>"><?=$capability; ?></label>
                    <br />
                  <?php

                  }

                  ?>
                </p>
              </div>

            <?php

            }

            ?>
          </div>
        </div>
        <?php

      }

      ?>
    </div>
    <?php

  }

  public static function optionsPage($arg1 = '', $arg2 = '', $arg3 = '') {

    if(!is_admin) {

      return;

    }

//    $roleNames = getAllRoles();



    // get_role( $role );

    // http://codex.wordpress.org/Creating_Options_Pages
    ?>
    <div class="wrap">
      <h2>Post Type Access</h2>
      <form method="post" action="options.php">

        <?php

        settings_fields( SK_PostTypeAccess::$optionPageContentTypeGroup );
        do_settings_sections( SK_PostTypeAccess::$optionPageContentTypeGroup );

        SK_PostTypeAccess::listCustomPostTypes();

        ?>
        <br />
        <br />

        <table class="form-table">
          <tr valign="top">
            <th scope="row">Disable Plugin</th>
            <td><input type="checkbox" name="disable_plugin" value="checked" <?=get_option('disable_plugin'); ?>/></td>
          </tr>
        </table>

        <?php submit_button(); ?>

      </form>
    </div>
  <?php
  }

  public static function getRolesCapsReadOnly($roles) {

    $readOnly = array();

    foreach( $roles->roles as $role ) {

      foreach( $role['capabilities'] as $capability => $enabled) {
        if( false !== strpos( $capability, 'read_' ) ) {
          $readOnly[$role['name']][] = $capability;
        }
      }

    }

    return $readOnly;

  }

  public static function updatePermissions() {

    $allRoles = self::getRoles();
    $allRoles = self::getRolesCapsReadOnly($allRoles);

    $roles = $_POST['skRoles'];
    foreach($roles as $role => $enabled) {
      $exploded = explode('_', $role);
      $thisRole       = array_shift($exploded);
      $thisPostType   = array_shift($exploded);
      $capability     = implode('_', $exploded);
//      $allRoles[$thisRole]
      $ffff = 'ffff';
    }
    $asdf = 'asdf';
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


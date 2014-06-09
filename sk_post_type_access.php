<?php
/**
 * Plugin Name: SK Post Type Access
 * Plugin URI: http://StevenKohlmeyer.com/sk_post_type_access_plugin
 * Description: This restricts content types to a specific role
 * Version: 0.0.3
 * Author: Fastmover
 * Author URI: http://StevenKohlmeyer.com
 * License: GPLv2 or later
 */



class SK_PostTypeAccess {

  public static $optionsPageSlug              = "sk_post_type_access_options_page";
  public static $optionPageContentTypeGroup   = "sk-role-content-type-group";

  public static $userCaps = array();
  public static $excludePostTypes = array();

  function __construct() {

//    add_action( 'pre_get_posts',  'SK_PostTypeAccess::post_access' );
    add_action('init', 'SK_PostTypeAccess::getUserPermissions');

    if ( is_admin() ){

      add_action( 'admin_init',       'SK_PostTypeAccess::registerSettings' );
      add_action( 'admin_menu',       'SK_PostTypeAccess::adminMenu' );

    }

    add_filter( 'posts_where',    'SK_PostTypeAccess::postsWhere' );

  }

  public static function test_action($arg1 = '', $arg2 = '', $arg3 = '') {

    return;

  }

  public static function postsWhere($where) {

    if( is_admin() )
      return where;

    if("checked" === get_option('disable_plugin'))
      return $where;


    $excludeTypes = array();
    $postTypes = get_post_types();

    foreach($postTypes as $postType) {

      $pTO = get_post_type_object($postType);

      if(!in_array($pTO->cap->read_post, self::$userCaps)) {

        $excludeTypes[] = $postType;

      }

    }

    self::$excludePostTypes = $excludeTypes;

    $whereTypes = implode('","', $excludeTypes);
    $whereTypes = '"' . $whereTypes . '"';
    $where .= " AND wp_posts.post_type NOT IN (" . $whereTypes . ") ";
    return $where;

  }

  public static function getUserPermissions() {

    self::$userCaps = self::initUserCaps(true);

  }

  public static function initUserCaps($keys = false) {

    global $userdata; // null if not logged in

    if(null == $userdata) {

      $userCapabilities = self::getRoleCapabilities('anonymous');

    } else {

      $userCapabilities = $userdata->allcaps;

    }

    if($keys)
      return array_keys($userCapabilities);

    return $userCapabilities;

  }

  public static function post_access($query = '') {

    if(is_admin() || is_single())
      return;

    if("checked" === get_option('disable_plugin'))
      return;

//    if( count($query->query) < 1 )
//      return;

    if(!$query->is_main_query())
      return;


    if(isset($query->query['post_type'])) {
      $thisPostType = $query->query['post_type'];
    } else {

    }

    $thisPostTypeReadCap = 'read_' . $thisPostType;
    $userGranted = in_array($thisPostTypeReadCap, self::$userCaps);

    if(!$userGranted) {

      self::render404();

    }


  }

  public static function render404() {
      $query->query['post_type'] = null;
      $query->query_vars['post_type'] = null;
      $query->is_404 = true;
  }

  public static function getRoleCapabilities($role) {

    global $wp_roles;

    $role = strtolower(str_replace(" ","_",$role));

    $roleCapabilities = $wp_roles->roles[$role]['capabilities'];

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

        wp_register_style( 'jquery-ui-css',  plugin_dir_url( __FILE__ ) . 'jquery-ui.css');
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

    $rolesObj      = SK_PostTypeAccess::getRoles();
    $roles = array_keys($rolesObj->roles);
    $postTypes  = SK_PostTypeAccess::getPostTypeObjects();
    $lastPostType = '';

    ?>
    <div class="accordion" id="accordion">
      <?php

      $usedCapabilities = array();

      foreach($postTypes as $postType) {

        ?>
        <h3 class="parent">
          <?=$postType->labels->name; ?>
        </h3>
        <div>
          <div class="accordion2" id="accordion-nested">
            <?php

            foreach($roles as $role) {

              $roleName = $rolesObj->roles[$role]['name'];

              $roleCapabilities   = self::getRoleCapabilities($role);
              $roleCapabilityKeys = array_keys($roleCapabilities);

              ?>

              <h4 class="child">
                <?=$roleName; ?>
              </h4>
              <div>
                <p>
                  <?php

                  foreach($postType->cap as $capability) {

                    if( false === strpos($capability, 'read_') ) {

                      continue;

                    }

                    if(!in_array($role . '_' . $capability, $usedCapabilities)) {

                      $usedCapabilities[] = $role . '_' . $capability;
                      $checked = '';

                      if( in_array( $capability, $roleCapabilityKeys ) ) {

                        $checked = 'checked';

                      }

                      ?>
                      <input type="checkbox" id="<?=$role; ?>_<?=$capability; ?>" name="skRoles[<?=$role; ?>_<?=$capability; ?>]" value="checked" <?=$checked; ?>/>
                      <input type="hidden" name="skRoles2[<?=$role; ?>_<?=$capability; ?>]" value="<?=$checked; ?>"/>
                      <label for="<?=$role; ?>_<?=$capability; ?>"><?=$capability; ?></label>
                      <br />
                    <?php

                    } else {

                      ?>
                      <?=$capability; ?><br />
                      <?php

                    }



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
        <p>
          Checkboxes will not appear for post types using other post types capabilities.
        </p>

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

    $allRoles       = self::getRoles();
    $allRoles       = self::getRolesCapsReadOnly($allRoles);
    $roles          = $_POST['skRoles'];
    $rolesBefore    = $_POST['skRoles2'];
    ksort($roles);
    ksort($rolesBefore);

    foreach($rolesBefore as $role => $enabled) {

      if($enabled === "") {

        // This role is disabled, check to see if it got enabled
        if(array_key_exists($role, $roles)) {

          // Enable this role
          self::enableCapability($role);

        }

      } elseif( $enabled === "checked") {

        // check to see if this was disabled
        if(!array_key_exists($role, $roles)) {

          self::disableCapability($role);

        }

      }

      $exploded = explode('_', $role);
      $thisRole       = array_shift($exploded);
      $capability     = implode('_', $exploded);

    }

  }

  public static function enableCapability($role) {

    $exploded       = explode( '_', $role );
    $thisRole       = array_shift( $exploded );
    $capability     = implode( '_', $exploded );
    $currentRole    = get_role( $thisRole );
    $currentRole->add_cap( $capability );

  }

  public static function disableCapability($role) {

    $exploded       = explode( '_', $role );
    $thisRole       = array_shift( $exploded );
    $capability     = implode( '_', $exploded );
    $currentRole    = get_role( $thisRole );
    $currentRole->remove_cap( $capability );

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


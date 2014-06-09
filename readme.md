##SK_Post_Type_Access
================================

This is a WordPress plugin to set read access restrictions to the front end of WordPress.  This is done on a capability basis, and can enable or disable read of an entire post type to specified roles. Caveats being post types using other post type capabilities.  This can be viewed on the plugin's options page.

This plugin locks down front end post reading, so you'll need to go into the plugin options and modify capabilities on a post_type basis for each role.  After enabling this plugin, all post types will be locked down.

When enabled, this plugin will search for a role named 'anonymous', if this doesn't currently exist, it will create it.  If this plugin creates the anonymous role, it will have the folow capabilities defaulted to it:
 - read
 - read_attachment
 - read_page
 - read_post

After enabling this plugin, you will want to add the read capabilities to each role for each post types. For instance, a custom post type of movie, would need read_movie capability added to roles to actually see this, unless the capabilities of this post_type were set to post (default unless otherwise specified).
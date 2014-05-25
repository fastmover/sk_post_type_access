##SK_Post_Type_Access
================================

This is a WordPress plugin to set read access restrictions to the front end of WordPress.  This is done on a capability basis, and can enable or disable read of an entire post type to specified roles.

A separate plugin is required to edit the capabilities of roles. A good plugin for this is: [Members](https://wordpress.org/plugins/members/)

When enabled, this plugin will search for a role named 'anonimous', if this doesn't currently exist, it will create it.  If this plugin creates the anonimous role, it will have the folow capabilities:
 - read
 - read_attachment
 - read_page
 - read_post

After enabling this plugin, you will want to add the read capabilities to each role for custom content types.
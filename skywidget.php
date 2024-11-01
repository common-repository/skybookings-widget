<?php
/**
 * Plugin Name: SkyBookings Widget
 * Plugin URI: https://skybookings.com
 * Description: A powerful Online Booking System & appointment scheduling platform for your website including online bookings, payments, sms reminders and much more.
 * Version: 1.0.0
 * Author: SkyBookings
 * Author URI: https://skybookings.com/
 * Text Domain: www.skybookings.com
 * Domain Path: www.skybookings.com
 * License: GPL2
 */
/*
  SkyBookings Widget
  Copyright (C) 2019, SkyBookings <support@skybookings.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('SKY_PLUGIN_DIR', str_replace('\\', '/', dirname(__FILE__)));

if (!class_exists('skybookingsWidget')) {

    class skybookingsWidget  {

        function __construct() {

            add_action('init', array(&$this, 'init'));
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('wp_footer', array(&$this, 'wp_footer'));
        }

        function init() {

            load_plugin_textdomain('skybookings', false, dirname(plugin_basename(__FILE__)) . '/lang');
        }

        function admin_init() {

            // register settings for sitewide script
            register_setting('skybookings', 'sky_insert_footer', 'trim');

            $sky_insert_footer = $_POST['sky_insert_footer'];
            if(!empty($sky_insert_footer))
            {
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://skybookings.com/api/V_1/plugin/checkClientId",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n".$sky_insert_footer."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
                ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                $result = json_decode($response,true);
                if(!empty($result))
                {
                    $error_code = $result['error_code'];
                    $status = $result['status'];
                    
                    if($error_code == 0 && $status == 'false'){
                        
                        wp_redirect( admin_url('admin.php?page=skybookings/skywidget.php&update=0') );
                        exit;
                    }
                }
            }

            // add meta box to all post types
            foreach (get_post_types('', 'names') as $type) 
            {
                $sky_insert_footer = $_POST['sky_insert_footer'];

                add_meta_box('shfs_all_post_meta', esc_html__('Insert Script to &lt;head&gt;', 'skybookings'), 'shfs_meta_setup', $type, 'normal', 'high');

                add_action('save_post', 'sky_post_meta_save');
            }
        }

        // adds menu item to wordpress admin dashboard
        function admin_menu() {
            $page = add_menu_page(__('SkyBookings Widget', 'skybookings'), __('SkyBookings Widget', 'skybookings'), 'manage_options', __FILE__, array(&$this, 'sky_options_panel'),'dashicons-calendar-alt');
        }

        function wp_footer() {
            if (!is_admin() && !is_feed() && !is_robots() && !is_trackback()) {
                $text = get_option('sky_insert_footer', '');
                $text = convert_smilies($text);
                $text = do_shortcode($text);

                if ($text != '') 
                {
                    ?>
                    <script type="text/javascript">
                        (function () {
                            var wgt = document.createElement("script");
                            wgt.type = "text/javascript";
                            wgt.async = true;
                            wgt.id = "widgetJs";
                            wgt.src = "https://skybookings.com/themes/widget/js/widget.js?clientId=<?php echo $text; ?>";
                            var s = document.getElementsByTagName("script")[0];
                            s.parentNode.insertBefore(wgt, s);
                        })();
                    </script>
                    <?php
                }
            }
        }

        function sky_options_panel() {
            // Load options page
            require_once(SKY_PLUGIN_DIR . '/inc/options.php');
        }

    }

    
    function sky_post_meta_save($post_id) {
        
        // check user permissions
        if ($_POST['post_type'] == 'page') {

            if (!current_user_can('edit_page', $post_id))
                return $post_id;
        } else {

            if (!current_user_can('edit_post', $post_id))
                return $post_id;
        }

        $current_data = get_post_meta($post_id, '_inpost_head_script', TRUE);

        $new_data = $_POST['_inpost_head_script'];
        
        sky_post_meta_clean($new_data);
        
        if ($current_data) {
            
            if (is_null($new_data))
                delete_post_meta($post_id, '_inpost_head_script');
            else
                update_post_meta($post_id, '_inpost_head_script', $new_data);
        } elseif (!is_null($new_data)) {

            add_post_meta($post_id, '_inpost_head_script', $new_data, TRUE);
        }
        
        return $post_id;
    }

    function sky_post_meta_clean(&$arr) {

        if (is_array($arr)) {

            foreach ($arr as $i => $v) {

                if (is_array($arr[$i])) {
                    sky_post_meta_clean($arr[$i]);

                    if (!count($arr[$i])) {
                        unset($arr[$i]);
                    }
                } else {

                    if (trim($arr[$i]) == '') {
                        unset($arr[$i]);
                    }
                }
            }

            if (!count($arr)) {
                $arr = NULL;
            }
        }
    }

    // display default admin notice
    function your_admin_notices_action() {
        if($_GET['update'] == '0' && $_GET['settings-updated'] != 'true')
        {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Sorry, Client Id does not match. Please try again.', 'shapeSpace'); ?></p>
            </div>
            <?php
        }
    }
    add_action( 'admin_notices', 'your_admin_notices_action');


    register_uninstall_hook( __FILE__, 'my_plugin_remove_database' );
    function my_plugin_remove_database() {
        global $wpdb;
        delete_option("sky_insert_footer");
    }

    $sky_header_and_footer_scripts = new skybookingsWidget();
}

<?php /**
 * Plugin Options page
 *
 * @package    SkyBookings Widget
 * @author     SkyBookings <support@skybookings.com>
 * @copyright  Copyright (c) 2019, SkyBookings
 * @link       https://skybookings.com/
 * @license    GPL
 */ ?>
<?php 
wp_register_style( 'style_css', plugins_url('style.css',__FILE__ )); 
wp_enqueue_style('style_css');
?>
<div class="wrap">
    <h2><?php _e('SkyBookings - Configure your Widget', 'skybookings'); ?> </h2>
    <hr />
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="postbox">
                    <div class="inside">
                        <?php if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ) { ?>
                            <div class="notice notice-success is-dismissible">
                                <p><?php _e('Client Id save successfully.', 'shapeSpace'); ?></p>
                            </div>
                        <?php } ?>

                        <form class="validate" name="dofollow" action="options.php" method="post">

                            <?php settings_fields('skybookings'); ?>

                            <h3 class="shfs-labels footerlabel" for="sky_insert_footer"><?php _e('Client ID:', 'skybookings'); ?></h3>

                            <div class="form-required term-name-wrap">
                                <input style="width:98%;" rows="10" cols="57" id="sky_insert_footer" name="sky_insert_footer" value="<?php echo esc_html(get_option('sky_insert_footer')); ?>" aria-required="true" required>
                            </div>
                            
                            <p><?php _e('Please enter your SkyBookings Client ID.', 'skybookings'); ?></p>
                            <p><?php _e('- To get this value, please sign up for a free account at skybookings.com or login to your SkyBookings Business Panel > tap on "Online Bookings Configuration" tab > tap on the "Wordpress Widget" tab from that popup.', 'skybookings'); ?></p>

                            <p class="submit">
                                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save', 'skybookings'); ?>" />
                            </p>

                        </form>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</div>

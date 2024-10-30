<?php
/**
 *
 * @link              #
 * @since             1.0.0
 * @package           wc_coming_soon
 *
 * @wordpress-plugin
 * Plugin Name:       Coming soon for products
 * Plugin URI:        #
 * Description:       Add coming soon button on product listing and product detail page.
 * Version:           1.0.2
 * Author:            MageiNIC
 * Author URI:        #
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       coming-soon-for-products
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
		exit;
}

define('WC_CS_URL', plugin_dir_url(__FILE__));
define('WC_CS_PUBLIC_URL', WC_CS_URL . 'public/');

register_activation_hook( __FILE__, 'wc_cs_activation' );

/**
 * Activation hook
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */

function wc_cs_activation() {
    if ( ! ( is_plugin_active('woocommerce/woocommerce.php' ) ) ) {
        echo _e('<div class="error"><p>WooCommerce is not activated. To activate coming soon plugin you must need to install or activate the WooCommerce plugin.</p></div>','coming-soon-for-products');
    }
}

/**
 * Deactivation hook
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
register_deactivation_hook( __FILE__, 'wc_cs_deactivation' );
function wc_cs_deactivation() {
  // Deactivation rules here
}

/**
 * Add meta-box for coming soon checkbox.
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
add_action( 'add_meta_boxes', 'wc_cs_meta_box' );
function wc_cs_meta_box() {
    add_meta_box(
        'coming-soon-checkbox',
        __( 'Coming soon checkbox', 'coming-soon-for-products' ),
        'wc_cs_meta_box_callback',
        'product',
        'side'
    );
}
/**
 * Callback function for meta-box.
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
function wc_cs_meta_box_callback(){
    global $post;
    $postId = $post->ID;
    $isChecked = get_post_meta($postId, 'coming-soon-checkbox', true);
    ?>
    <label for="coming-soon-checkbox-id">
        <input type="checkbox" id="coming-soon-checkbox-id" name="coming-soon-checkbox" <?php echo ($isChecked)?'checked':''; ?> value="1">
        <?php _e( 'Coming soon checkbox', 'coming-soon-for-products' ); ?>
    </label>
    <?php
}

/**
 * Save meta-box value as post-meta.
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
add_action( 'save_post', 'wc_cs_save_meta_box' );
function wc_cs_save_meta_box( $post_id ) {
    if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && ('product' === $_POST['post_type']) ) {
        if($_POST['coming-soon-checkbox']){
            $comingsooncheckbox = intval($_POST['coming-soon-checkbox']);
            update_post_meta($post_id,'coming-soon-checkbox',$comingsooncheckbox);
        }else{
            update_post_meta($post_id,'coming-soon-checkbox',0);
        }
    }
}

/**
 * Replace custom coming soon button insted of add to cart button in product listing page.
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
add_filter( 'woocommerce_loop_add_to_cart_link', 'wc_cs_replace_default_button', 10, 2 );
function wc_cs_replace_default_button( $button, $product ){
    if ( $product->get_meta('coming-soon-checkbox') ){
        $button = '<a href="javascript:void(0)" class="button wp-element-button add_to_cart_button btn btn-primary">' . __( "Coming Soon", "coming-soon-for-products" ) . '</a>';
    }
    return $button;
}

/**
 * Replace custom coming soon button insted of add to cart button in product detail page.
 *
 * @since           1.0.0
 * @param       string    $plugin_name          Coming soon for products.
 * @param       string    $version                  1.0.0.
 */
add_action( 'woocommerce_single_product_summary', 'wc_cs_single_product_summary_callback', 1 );
function wc_cs_single_product_summary_callback() {
    global $product;
    if ( $product->get_meta('coming-soon-checkbox') ) {
        if( $product->is_type( 'variable' ) ) {
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
            add_action( 'woocommerce_single_variation', 'wc_cs_add_to_cart_replacement_button', 20 );
        } else {
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
            add_action( 'woocommerce_single_product_summary', 'wc_cs_add_to_cart_replacement_button', 30 );
        }
    }
}
function wc_cs_add_to_cart_replacement_button(){
    echo '<a href="#" class="button wp-element-button add_to_cart_button btn btn-primary">' . __( "Coming Soon", "coming-soon-for-products" ) . '</a>';
}
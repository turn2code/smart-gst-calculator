<?php
/**
 * Plugin Name: Smart GST Calculator
 * Plugin URI: https://wordpress.org/plugins/smart-gst-calculator/
 * Description: Calculate GST for Indian products/services with CGST/SGST/IGST breakdown. Supports 5%, 12%, 18%, 28% slabs.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Tested up to: 6.8
 * Author: TurnToCode
 * Author URI: https://github.com/turn2code/gst-calculator-plugin.git
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-gst-calculator
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Enqueue scripts and styles
function gst_calculator_enqueue_scripts() {
    wp_enqueue_style(
        'gst-calculator-style',
        plugins_url('css/style.css', __FILE__),
        array(),
        '1.0.0'
    );
    
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );
    
    wp_enqueue_script(
        'gst-calculator-script',
        plugins_url('js/script.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script(
        'gst-calculator-script',
        'gst_calculator_ajax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('gst_calculator_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'gst_calculator_enqueue_scripts');

// Shortcode implementation
function gst_calculator_shortcode() {
    ob_start();
    ?>
    <div class="gst-calculator-container">
        <h3><i class="fas fa-calculator"></i> <?php esc_html_e('GST Calculator', 'gst-calculator-india'); ?></h3>
        <div class="gst-calculator-form">
            <!-- Form fields with proper escaping -->
            <input type="number" id="original_price" class="form-control" placeholder="<?php esc_attr_e('Enter amount', 'gst-calculator-india'); ?>" min="0" step="0.01">
            <!-- ... rest of your HTML ... -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gst_calculator', 'gst_calculator_shortcode');

// AJAX handler
function gst_calculator_ajax_handler() {
    check_ajax_referer('gst_calculator_nonce', 'nonce');
    
    $original_price = isset($_POST['original_price']) ? floatval($_POST['original_price']) : 0;
    $gst_rate       = isset($_POST['gst_rate']) ? floatval($_POST['gst_rate']) : 18;
    $transaction_type = isset($_POST['transaction_type']) ? sanitize_text_field($_POST['transaction_type']) : 'intra';
    
    // Calculations...
    
    wp_send_json_success(array(
        'original_price' => number_format_i18n($original_price, 2),
        'gst_amount'     => number_format_i18n($gst_amount, 2),
        // ... other data
    ));
}
add_action('wp_ajax_gst_calculator_calculate', 'gst_calculator_ajax_handler');
add_action('wp_ajax_nopriv_gst_calculator_calculate', 'gst_calculator_ajax_handler');
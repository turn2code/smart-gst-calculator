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
 * Author URI: https://github.com/turn2code/smart-gst-calculator
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-gst-calculator
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue scripts and styles
function gst_calculator_enqueue_scripts() {
    // CSS
    wp_enqueue_style(
        'gst-calculator-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );
    
    // Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        array(),
        '6.0.0'
    );
    
    // JS
    wp_enqueue_script(
        'gst-calculator-script',
        plugin_dir_url(__FILE__) . 'js/script.js',
        array('jquery'),
        '1.0',
        true
    );
    
    // Localize script for AJAX
    wp_localize_script(
        'gst-calculator-script',
        'gst_calculator_ajax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gst_calculator_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'gst_calculator_enqueue_scripts');

// Shortcode for the calculator
function gst_calculator_shortcode() {
    ob_start();
    ?>
    <div class="gst-calculator-container">
        <h3><i class="fas fa-calculator"></i> GST Calculator</h3>
        <div class="gst-calculator-form">
            <div class="form-group">
                <label for="original_price"><i class="fas fa-rupee-sign"></i> Original Price (₹):</label>
                <input type="number" id="original_price" class="form-control" placeholder="Enter amount" min="0" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="gst_rate"><i class="fas fa-percent"></i> GST Rate (%):</label>
                <select id="gst_rate" class="form-control">
                    <option value="5">5%</option>
                    <option value="12">12%</option>
                    <option value="18" selected>18%</option>
                    <option value="28">28%</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="transaction_type"><i class="fas fa-exchange-alt"></i> Transaction Type:</label>
                <select id="transaction_type" class="form-control">
                    <option value="inter">Inter-State (IGST)</option>
                    <option value="intra">Intra-State (CGST + SGST)</option>
                </select>
            </div>
            
            <button id="calculate_gst" class="btn-calculate"><i class="fas fa-calculator"></i> Calculate GST</button>
            
            <div class="gst-results" style="display: none;">
                <h4><i class="fas fa-receipt"></i> GST Calculation Results</h4>
                <div class="result-item">
                    <span class="result-label">Original Price:</span>
                    <span class="result-value" id="original_price_result">₹0.00</span>
                </div>
                
                <div class="result-item">
                    <span class="result-label">GST Rate:</span>
                    <span class="result-value" id="gst_rate_result">0%</span>
                </div>
                
                <div class="result-item">
                    <span class="result-label">GST Amount:</span>
                    <span class="result-value" id="gst_amount">₹0.00</span>
                </div>
                
                <div class="result-item" id="cgst_container">
                    <span class="result-label">CGST (50%):</span>
                    <span class="result-value" id="cgst_amount">₹0.00</span>
                </div>
                
                <div class="result-item" id="sgst_container">
                    <span class="result-label">SGST (50%):</span>
                    <span class="result-value" id="sgst_amount">₹0.00</span>
                </div>
                
                <div class="result-item" id="igst_container">
                    <span class="result-label">IGST:</span>
                    <span class="result-value" id="igst_amount">₹0.00</span>
                </div>
                
                <div class="result-item total-price">
                    <span class="result-label">Total Price:</span>
                    <span class="result-value" id="total_price">₹0.00</span>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('gst_calculator', 'gst_calculator_shortcode');

// AJAX handler for calculation
function gst_calculator_ajax_handler() {
    check_ajax_referer('gst_calculator_nonce', 'nonce');
    
    $original_price = floatval($_POST['original_price']);
    $gst_rate = floatval($_POST['gst_rate']);
    $transaction_type = sanitize_text_field($_POST['transaction_type']);
    
    $gst_amount = ($original_price * $gst_rate) / 100;
    $total_price = $original_price + $gst_amount;
    
    if ($transaction_type === 'intra') {
        $cgst_amount = $sgst_amount = $gst_amount / 2;
        $igst_amount = 0;
    } else {
        $cgst_amount = $sgst_amount = 0;
        $igst_amount = $gst_amount;
    }
    
    wp_send_json(array(
        'success' => true,
        'original_price' => number_format($original_price, 2),
        'gst_rate' => $gst_rate,
        'gst_amount' => number_format($gst_amount, 2),
        'cgst_amount' => number_format($cgst_amount, 2),
        'sgst_amount' => number_format($sgst_amount, 2),
        'igst_amount' => number_format($igst_amount, 2),
        'total_price' => number_format($total_price, 2)
    ));
}
add_action('wp_ajax_gst_calculator_calculate', 'gst_calculator_ajax_handler');
add_action('wp_ajax_nopriv_gst_calculator_calculate', 'gst_calculator_ajax_handler');
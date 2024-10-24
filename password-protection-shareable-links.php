<?php
/**
 * Plugin Name: Password Protection with Shareable Links
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 1.2.7
 * Author: klausbreyer
 * Text Domain: password-protection-shareable-links
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'options.php';
include_once plugin_dir_path(__FILE__) . 'crypto.php';
include_once plugin_dir_path(__FILE__) . 'meta.php';
include_once plugin_dir_path(__FILE__) . 'gate.php';

register_activation_hook(__FILE__, 'ppsl_activate');
function ppsl_activate()
{
    // Check if a salt is already stored
    if (!get_option('ppsl_salt')) {
        // Generate a random salt
        $salt = bin2hex(openssl_random_pseudo_bytes(16));
        // Store the salt in WordPress options
        update_option('ppsl_salt', $salt);
    }
}

// Load your text domain in the init hook
add_action('init', 'ppsl_load_textdomain');
function ppsl_load_textdomain()
{
    load_plugin_textdomain('password-protection-shareable-links', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('template_redirect', 'ppsl_protect_content');
function ppsl_protect_content()
{
    // Disable caching HTTP headers
    header("Cache-Control: no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

    if (is_admin() || current_user_can('manage_options')) {
        return;
    }

    $options = get_option('ppsl_settings');
    if (!is_array($options)) {
        $options = array('ppsl_text_field_0' => '');
    }

    if (!isset($options['ppsl_password_protect']) || !$options['ppsl_password_protect']) {
        return; // If not enabled, do not perform any further actions
    }

    $savedPassword = $options['ppsl_text_field_0'];
    $salt = get_option('ppsl_salt');

    // Confirm
    if (isset($_POST['ppsl_password_confirm']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'ppsl_nonce')) {
        if (isset($_GET['password'])) {
            $decryptedPassword = ppsl_decrypt(sanitize_text_field(wp_unslash($_GET['password'])), $salt);
        } else {
            $decryptedPassword = '';
        }
        if ($decryptedPassword === $savedPassword) {
            $duration = isset($_POST['ppsl_duration']) ? (int) $_POST['ppsl_duration'] : 3600;
            setcookie('ppsl_pass', ppsl_encrypt($savedPassword, $salt), time() + $duration, '/');
            wp_redirect(remove_query_arg('password'));
            exit;
        } else {
            ppsl_password_confirm_alert();
            exit;
        }
    }

    // Send password
    if (isset($_POST['ppsl_password']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'ppsl_nonce')) {
        $passFromUser = ppsl_encrypt(sanitize_text_field(wp_unslash($_POST['ppsl_password'])), $salt);
        if ($passFromUser === ppsl_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['ppsl_duration']) ? (int) $_POST['ppsl_duration'] : 3600; // Default is 1 hour
            setcookie('ppsl_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // Clean up URL
            exit;
        } else {
            ppsl_show_password_form(true);
            exit;
        }
    }

    // URL password handling
    if (isset($_GET['password'])) {
        $encryptedPassword = sanitize_text_field(wp_unslash($_GET['password']));
        $decryptedPassword = ppsl_decrypt($encryptedPassword, $salt);

        if ($decryptedPassword === $savedPassword) {
            ppsl_show_password_form_with_notice();
            exit;
        } else {
            ppsl_password_confirm_alert();
            exit;
        }
    }

    // Cookie check
    $passFromCookie = isset($_COOKIE['ppsl_pass']) ? ppsl_decrypt(sanitize_text_field(wp_unslash($_COOKIE['ppsl_pass'])), $salt) : '';
    if ($passFromCookie !== $savedPassword) {
        ppsl_show_password_form();
        exit;
    }
}

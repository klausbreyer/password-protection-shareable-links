<?php
/**
 * Plugin Name: Password Protection with Shareable Links
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 1.2
 * Author: klausbreyer
 * Text Domain: ppsl
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


include_once plugin_dir_path(__FILE__) . 'options.php';
include_once plugin_dir_path(__FILE__) . 'crypto.php';
include_once plugin_dir_path(__FILE__) . 'meta.php';
include_once plugin_dir_path(__FILE__) . 'gate.php';

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


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
    load_plugin_textdomain('ppsl', false, dirname(plugin_basename(__FILE__)) . '/languages/');

}


add_action('template_redirect', 'ppsl_protect_content');
function ppsl_protect_content()
{

    // Disable caching HTTP headers to ensure that the password gate is displayed when it should be (e.g., after changing the password) and that the correct content is displayed when it shouldn't.
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

    // confirm
    if (isset($_POST['ppsl_password_confirm']) && wp_verify_nonce($_POST['_wpnonce'], 'ppsl_nonce')) {
        $decryptedPassword = ppsl_decrypt($_GET['password'], $salt);
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
    // send pw.
    if (isset($_POST['ppsl_password']) && wp_verify_nonce($_POST['_wpnonce'], 'ppsl_nonce')) {
        $passFromUser = ppsl_encrypt($_POST['ppsl_password'], $salt);
        if ($passFromUser === ppsl_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['ppsl_duration']) ? (int) $_POST['ppsl_duration'] : 3600; // Default is 1 hour
            setcookie('ppsl_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // Clean up URL
        } else {
            ppsl_show_password_form(true);
            exit;
        }
    }

    // url
    if (isset($_GET['password'])) {
        $encryptedPassword = $_GET['password'];
        $decryptedPassword = ppsl_decrypt($encryptedPassword, $salt);

        // Compare the decrypted password with the saved password
        if ($decryptedPassword === $savedPassword) {
            // If the password matches, display the confirmation form
            ppsl_show_password_form_with_notice();
            exit;
        } else {
            // If the password does not match, display an error message
            ppsl_password_confirm_alert();
            exit;
        }
    }

    $passFromCookie = isset($_COOKIE['ppsl_pass']) ? ppsl_decrypt($_COOKIE['ppsl_pass'], $salt) : '';
    if ($passFromCookie !== $savedPassword) {
        ppsl_show_password_form();
        exit;
    }
}

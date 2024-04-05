<?php
/**
 * Plugin Name: Password Protection with Shareable Links
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 1.1
 * Author: klausbreyer
 */


include_once plugin_dir_path(__FILE__) . 'options.php';
include_once plugin_dir_path(__FILE__) . 'crypto.php';
include_once plugin_dir_path(__FILE__) . 'meta.php';
include_once plugin_dir_path(__FILE__) . 'gate.php';

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


register_activation_hook(__FILE__, 'ppwsl_activate');
function ppwsl_activate()
{
    // Check if a salt is already stored
    if (!get_option('ppwsl_salt')) {
        // Generate a random salt
        $salt = bin2hex(openssl_random_pseudo_bytes(16));
        // Store the salt in WordPress options
        update_option('ppwsl_salt', $salt);
    }
}

// Load your text domain in the init hook
add_action('init', 'ppwsl_load_textdomain');
function ppwsl_load_textdomain()
{
    load_plugin_textdomain('ppwsl', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}


add_action('template_redirect', 'ppwsl_protect_content');
function ppwsl_protect_content()
{

    // Disable caching HTTP headers to ensure that the password gate is displayed when it should be (e.g., after changing the password) and that the correct content is displayed when it shouldn't.
    header("Cache-Control: no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

    if (is_admin() || current_user_can('manage_options')) {
        return;
    }

    $options = get_option('ppwsl_settings');
    if (!is_array($options)) {
        $options = array('ppwsl_text_field_0' => '');
    }

    if (!isset($options['ppwsl_password_protect']) || !$options['ppwsl_password_protect']) {
        return; // If not enabled, do not perform any further actions
    }

    $savedPassword = $options['ppwsl_text_field_0'];
    $salt = get_option('ppwsl_salt');

    // confirm
    if (isset($_POST['ppwsl_password_confirm']) && wp_verify_nonce($_POST['_wpnonce'], 'ppwsl_nonce')) {
        $decryptedPassword = ppwsl_decrypt($_GET['password'], $salt);
        if ($decryptedPassword === $savedPassword) {
            $duration = isset($_POST['ppwsl_duration']) ? (int) $_POST['ppwsl_duration'] : 3600;
            setcookie('ppwsl_pass', ppwsl_encrypt($savedPassword, $salt), time() + $duration, '/');
            wp_redirect(remove_query_arg('password'));
            exit;
        } else {
            ppwsl_password_confirm_alert();
            exit;
        }
    }
    // send pw.
    if (isset($_POST['ppwsl_password']) && wp_verify_nonce($_POST['_wpnonce'], 'ppwsl_nonce')) {
        $passFromUser = ppwsl_encrypt($_POST['ppwsl_password'], $salt);
        if ($passFromUser === ppwsl_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['ppwsl_duration']) ? (int) $_POST['ppwsl_duration'] : 3600; // Default is 1 hour
            setcookie('ppwsl_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // Clean up URL
        } else {
            ppwsl_show_password_form(true);
            exit;
        }
    }

    // url
    if (isset($_GET['password'])) {
        $encryptedPassword = $_GET['password'];
        $decryptedPassword = ppwsl_decrypt($encryptedPassword, $salt);

        // Compare the decrypted password with the saved password
        if ($decryptedPassword === $savedPassword) {
            // If the password matches, display the confirmation form
            ppwsl_show_password_form_with_notice();
            exit;
        } else {
            // If the password does not match, display an error message
            ppwsl_password_confirm_alert();
            exit;
        }
    }

    $passFromCookie = isset($_COOKIE['ppwsl_pass']) ? ppwsl_decrypt($_COOKIE['ppwsl_pass'], $salt) : '';
    if ($passFromCookie !== $savedPassword) {
        ppwsl_show_password_form();
        exit;
    }
}

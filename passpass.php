<?php
/**
 * Plugin Name: PassPass - Password Protection with Shareable Links
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


register_activation_hook(__FILE__, 'passpass_activate');
function passpass_activate()
{
    // Check if a salt is already stored
    if (!get_option('passpass_salt')) {
        // Generate a random salt
        $salt = bin2hex(openssl_random_pseudo_bytes(16));
        // Store the salt in WordPress options
        update_option('passpass_salt', $salt);
    }
}

// Load your text domain in the init hook
add_action('init', 'passpass_load_textdomain');
function passpass_load_textdomain()
{
    load_plugin_textdomain('passpass', false, dirname(plugin_basename(__FILE__)) . '/languages/');

}


add_action('template_redirect', 'passpass_protect_content');
function passpass_protect_content()
{

    // Disable caching HTTP headers to ensure that the password gate is displayed when it should be (e.g., after changing the password) and that the correct content is displayed when it shouldn't.
    header("Cache-Control: no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

    if (is_admin() || current_user_can('manage_options')) {
        return;
    }

    $options = get_option('passpass_settings');
    if (!is_array($options)) {
        $options = array('passpass_text_field_0' => '');
    }

    if (!isset($options['passpass_password_protect']) || !$options['passpass_password_protect']) {
        return; // If not enabled, do not perform any further actions
    }

    $savedPassword = $options['passpass_text_field_0'];
    $salt = get_option('passpass_salt');

    // confirm
    if (isset($_POST['passpass_password_confirm']) && wp_verify_nonce($_POST['_wpnonce'], 'passpass_nonce')) {
        $decryptedPassword = passpass_decrypt($_GET['password'], $salt);
        if ($decryptedPassword === $savedPassword) {
            $duration = isset($_POST['passpass_duration']) ? (int) $_POST['passpass_duration'] : 3600;
            setcookie('passpass_pass', passpass_encrypt($savedPassword, $salt), time() + $duration, '/');
            wp_redirect(remove_query_arg('password'));
            exit;
        } else {
            passpass_password_confirm_alert();
            exit;
        }
    }
    // send pw.
    if (isset($_POST['passpass_password']) && wp_verify_nonce($_POST['_wpnonce'], 'passpass_nonce')) {
        $passFromUser = passpass_encrypt($_POST['passpass_password'], $salt);
        if ($passFromUser === passpass_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['passpass_duration']) ? (int) $_POST['passpass_duration'] : 3600; // Default is 1 hour
            setcookie('passpass_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // Clean up URL
        } else {
            passpass_show_password_form(true);
            exit;
        }
    }

    // url
    if (isset($_GET['password'])) {
        $encryptedPassword = $_GET['password'];
        $decryptedPassword = passpass_decrypt($encryptedPassword, $salt);

        // Compare the decrypted password with the saved password
        if ($decryptedPassword === $savedPassword) {
            // If the password matches, display the confirmation form
            passpass_show_password_form_with_notice();
            exit;
        } else {
            // If the password does not match, display an error message
            passpass_password_confirm_alert();
            exit;
        }
    }

    $passFromCookie = isset($_COOKIE['passpass_pass']) ? passpass_decrypt($_COOKIE['passpass_pass'], $salt) : '';
    if ($passFromCookie !== $savedPassword) {
        passpass_show_password_form();
        exit;
    }
}

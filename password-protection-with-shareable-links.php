<?php
/**
 * Plugin Name: Password Protection with Shareable Links
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 1.0
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
    // Prüfen, ob bereits ein Salt gespeichert ist
    if (!get_option('ppwsl_salt')) {
        // Ein zufälliges Salt generieren
        $salt = bin2hex(openssl_random_pseudo_bytes(16));
        // Das Salt in den WordPress-Optionen speichern
        update_option('ppwsl_salt', $salt);
    }
}

// Laden Sie Ihre Textdomain im init Hook
add_action('init', 'ppwsl_load_textdomain');
function ppwsl_load_textdomain()
{
    load_plugin_textdomain('ppwsl', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}




add_action('template_redirect', 'ppwsl_protect_content');
function ppwsl_protect_content()
{
    if (is_admin() || current_user_can('manage_options'))
        return;

    $options = get_option('ppwsl_settings');
    if (!is_array($options)) {
        $options = array('ppwsl_text_field_0' => '');
    }

    if (!isset($options['ppwsl_password_protect']) || !$options['ppwsl_password_protect']) {
        return; // Wenn nicht aktiviert, führen Sie keine weiteren Aktionen aus
    }

    $savedPassword = $options['ppwsl_text_field_0'];
    $salt = get_option('ppwsl_salt');


    // Formularverarbeitung
    if (isset($_POST['ppwsl_password_confirm'])) {
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
    // Überprüfung und Verarbeitung des Formulars
    if (isset($_POST['ppwsl_password'])) {
        $passFromUser = ppwsl_encrypt($_POST['ppwsl_password'], $salt);
        if ($passFromUser === ppwsl_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['ppwsl_duration']) ? (int) $_POST['ppwsl_duration'] : 3600; // Standardmäßig 1 Stunde
            setcookie('ppwsl_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // URL bereinigen
            exit;
        } else {

            ppwsl_show_password_form(true);
            exit;

        }
    }
    // url
    if (isset($_GET['password'])) {
        $encryptedPassword = $_GET['password'];
        $decryptedPassword = ppwsl_decrypt($encryptedPassword, $salt);

        // Vergleichen Sie das entschlüsselte Passwort mit dem gespeicherten Passwort
        if ($decryptedPassword === $savedPassword) {
            // Wenn das Passwort übereinstimmt, zeigen Sie das Bestätigungsformular an
            ppwsl_show_password_form_with_notice();
            exit;
        } else {
            // Wenn das Passwort nicht übereinstimmt, zeigen Sie eine Fehlermeldung an
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


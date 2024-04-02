<?php
/**
 * Plugin Name: Password Protection with Shareable Links
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 0.1
 * Author: Klaus Breyer
 */


include_once plugin_dir_path(__FILE__) . 'options.php';
include_once plugin_dir_path(__FILE__) . 'crypto.php';
include_once plugin_dir_path(__FILE__) . 'meta.php';
include_once plugin_dir_path(__FILE__) . 'gate.php';

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
register_activation_hook(__FILE__, 'safepasslink_activate');
function safepasslink_activate()
{
    // Prüfen, ob bereits ein Salt gespeichert ist
    if (!get_option('safepasslink_salt')) {
        // Ein zufälliges Salt generieren
        $salt = bin2hex(openssl_random_pseudo_bytes(16));
        // Das Salt in den WordPress-Optionen speichern
        update_option('safepasslink_salt', $salt);
    }
}
add_action('template_redirect', 'safepasslink_protect_content');
function safepasslink_protect_content()
{
    if (is_admin() || current_user_can('manage_options'))
        return;

    $options = get_option('safepasslink_settings');
    if (!is_array($options)) {
        $options = array('safepasslink_text_field_0' => '');
    }

    if (!isset($options['safepasslink_password_protect']) || !$options['safepasslink_password_protect']) {
        return; // Wenn nicht aktiviert, führen Sie keine weiteren Aktionen aus
    }

    $savedPassword = $options['safepasslink_text_field_0'];
    $salt = get_option('safepasslink_salt');


    // Formularverarbeitung
    if (isset($_POST['safepasslink_password_confirm'])) {
        $decryptedPassword = safepasslink_decrypt($_GET['password'], $salt);
        if ($decryptedPassword === $savedPassword) {
            $duration = isset($_POST['safepasslink_duration']) ? (int) $_POST['safepasslink_duration'] : 3600;
            setcookie('safepasslink_pass', safepasslink_encrypt($savedPassword, $salt), time() + $duration, '/');
            wp_redirect(remove_query_arg('password'));
            exit;
        } else {
            echo '<p>Das Passwort in Ihrem Link ist veraltet oder falsch. Bitte bitten Sie um einen neuen Link oder kontaktieren Sie den Administrator der Website.</p>';
            exit;
        }
    }
    // Überprüfung und Verarbeitung des Formulars
    if (isset($_POST['safepasslink_password'])) {
        $passFromUser = safepasslink_encrypt($_POST['safepasslink_password'], $salt);
        if ($passFromUser === safepasslink_encrypt($savedPassword, $salt)) {
            $duration = isset($_POST['safepasslink_duration']) ? (int) $_POST['safepasslink_duration'] : 3600; // Standardmäßig 1 Stunde
            setcookie('safepasslink_pass', $passFromUser, time() + $duration, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect(remove_query_arg(array('pass'))); // URL bereinigen
            exit;
        } else {
            echo '<p>Das eingegebene Passwort ist falsch. Bitte versuchen Sie es erneut.</p>';
            safepasslink_show_password_form();
            exit;

        }
    }
    // url
    if (isset($_GET['password'])) {
        $encryptedPassword = $_GET['password'];
        $decryptedPassword = safepasslink_decrypt($encryptedPassword, $salt);

        // Vergleichen Sie das entschlüsselte Passwort mit dem gespeicherten Passwort
        if ($decryptedPassword === $savedPassword) {
            // Wenn das Passwort übereinstimmt, zeigen Sie das Bestätigungsformular an
            safepasslink_show_password_form_with_notice();
            exit;
        } else {
            // Wenn das Passwort nicht übereinstimmt, zeigen Sie eine Fehlermeldung an
            echo '<p>Das Passwort in Ihrem Link ist veraltet oder falsch. Bitte bitten Sie um einen neuen Link oder kontaktieren Sie den Administrator der Website.</p>';
            exit;
        }
    }

    $passFromCookie = isset($_COOKIE['safepasslink_pass']) ? safepasslink_decrypt($_COOKIE['safepasslink_pass'], $salt) : '';
    if ($passFromCookie !== $savedPassword) {
        safepasslink_show_password_form();
        exit;
    }
}


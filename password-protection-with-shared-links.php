<?php
/**
 * Plugin Name: SafePassLink
 * Description: Password protection with seamless sharing and secure link generation.
 * Version: 0.1
 * Author: Klaus Breyer
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_action('template_redirect', 'safepasslink_protect_content');
function safepasslink_protect_content()
{
    if (is_admin() || current_user_can('manage_options'))
        return;

    $options = get_option('safepasslink_settings');
    $savedPassword = isset($options['safepasslink_text_field_0']) ? $options['safepasslink_text_field_0'] : '';
    $encryptedPass = isset($_GET['pass']) ? $_GET['pass'] : '';
    $salt = 'EIN_SICHERER_SALT'; // Ändern Sie dies in einen sicheren, eindeutigen Wert.

    if (!empty($encryptedPass)) {
        $decryptedPass = safepasslink_decrypt($encryptedPass, $salt);
        if ($decryptedPass === $savedPassword) {
            setcookie('safepasslink_pass', $encryptedPass, time() + 3600); // 1 Stunde Gültigkeit
            wp_redirect(remove_query_arg('pass')); // URL bereinigen
            exit;
        }
    }

    $pass = isset($_COOKIE['safepasslink_pass']) ? safepasslink_decrypt($_COOKIE['safepasslink_pass'], $salt) : false;
    if ($pass !== $savedPassword) {
        // Leiten Sie hier zur Passworteingabe-Seite um oder zeigen Sie ein Eingabefeld an
    }
}

function safepasslink_encrypt($text, $salt)
{
    return base64_encode(openssl_encrypt($text, 'AES-128-ECB', $salt));
}

function safepasslink_decrypt($text, $salt)
{
    return openssl_decrypt(base64_decode($text), 'AES-128-ECB', $salt);
}
add_action('admin_menu', 'safepasslink_add_admin_menu');
add_action('admin_init', 'safepasslink_settings_init');

function safepasslink_add_admin_menu()
{
    add_options_page('SafePassLink', 'SafePassLink', 'manage_options', 'safepasslink', 'safepasslink_options_page');
}

function safepasslink_settings_init()
{
    register_setting('safepasslink', 'safepasslink_settings');
    add_settings_section('safepasslink_safepasslink_section', __('Your section description here', 'safepasslink'), 'safepasslink_settings_section_callback', 'safepasslink');

    add_settings_field('safepasslink_text_field_0', __('Password', 'safepasslink'), 'safepasslink_text_field_0_render', 'safepasslink', 'safepasslink_safepasslink_section');
}

function safepasslink_text_field_0_render()
{
    $options = get_option('safepasslink_settings');
    if (!is_array($options)) {
        $options = array('safepasslink_text_field_0' => '');
    }

    ?>
    <input type='text' name='safepasslink_settings[safepasslink_text_field_0]' value='<?php echo $options['safepasslink_text_field_0']; ?>'>
    <?php
}

function safepasslink_options_page()
{
    ?>
    <form action='options.php' method='post'>
        <h2>SafePassLink</h2>
        <?php
        settings_fields('safepasslink');
        do_settings_sections('safepasslink');
        submit_button();
        ?>
    </form>
    <?php
}
function safepasslink_settings_section_callback()
{
    echo 'Geben Sie das Passwort ein, das zum Schutz Ihrer Website verwendet werden soll.';
}

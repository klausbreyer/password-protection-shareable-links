<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly



add_action('admin_menu', 'ppwsl_add_admin_menu');
add_action('admin_init', 'ppwsl_settings_init');

function ppwsl_add_admin_menu()
{
	add_options_page('Password Protection with Shareable Links', 'Password Protection with Shareable Links', 'manage_options', 'password-protection-with-shareable-links', 'ppwsl_options_page');
}

function ppwsl_settings_init()
{
	register_setting('password-protection-with-shareable-links', 'ppwsl_settings');

	add_settings_section(
		'ppwsl_ppwsl_section',
		__('Einstellungen für Password Protection with Shareable Links', 'password-protection-with-shareable-links'),
		'ppwsl_settings_section_callback',
		'password-protection-with-shareable-links'
	);

	add_settings_field(
		'ppwsl_password_protect',
		__('Passwortschutz aktivieren', 'password-protection-with-shareable-links'),
		'ppwsl_password_protect_render',
		'password-protection-with-shareable-links',
		'ppwsl_ppwsl_section'
	);

	add_settings_field(
		'ppwsl_text_field_0',
		__('Password', 'password-protection-with-shareable-links'),
		'ppwsl_text_field_0_render',
		'password-protection-with-shareable-links',
		'ppwsl_ppwsl_section'
	);
}

function ppwsl_password_protect_render()
{
	$options = get_option('ppwsl_settings');
	?>
	<input type='checkbox' name='ppwsl_settings[ppwsl_password_protect]' <?php checked(isset($options['ppwsl_password_protect']) && $options['ppwsl_password_protect']); ?> value='1'>
	<?php
}

function ppwsl_text_field_0_render()
{
	$options = get_option('ppwsl_settings');
	if (!is_array($options)) {
		$options = array('ppwsl_text_field_0' => '');
	}

	?>
	<input type='text' name='ppwsl_settings[ppwsl_text_field_0]' value='<?php echo $options['ppwsl_text_field_0']; ?>'>
	<?php
}

function ppwsl_options_page()
{
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields('password-protection-with-shareable-links');
		do_settings_sections('password-protection-with-shareable-links');
		submit_button();
		?>
	</form>
	<?php
}
function ppwsl_settings_section_callback()
{
	echo __('Geben Sie hier Ihre Einstellungen für den Passwortschutz ein.', 'password-protection-with-shareable-links');
}

add_action('admin_notices', 'ppwsl_check_configuration');
function ppwsl_check_configuration()
{
	$options = get_option('ppwsl_settings');
	if (!is_array($options)) {
		$options = array('ppwsl_text_field_0' => '');
	}

	$isPasswordProtectionEnabled = isset($options['ppwsl_password_protect']) && $options['ppwsl_password_protect'];
	$savedPassword = $options['ppwsl_text_field_0'] ?? '';

	// Überprüfen, ob der Passwortschutz aktiviert ist und das Passwort gesetzt wurde
	if (!$isPasswordProtectionEnabled || strlen($savedPassword) === 0) {
		// Plugin ist nicht korrekt konfiguriert, zeige Warnung
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>Password Protection with Shareable Links</strong> ist installiert, aber noch nicht korrekt konfiguriert. Bitte setzen Sie ein Passwort und aktivieren Sie den Passwortschutz in den <a href="options-general.php?page=password-protection-with-shareable-links">Plugin-Einstellungen</a>.</p>
		</div>
		<?php
	}
}

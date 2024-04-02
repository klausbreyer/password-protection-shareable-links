<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly



add_action('admin_menu', 'safepasslink_add_admin_menu');
add_action('admin_init', 'safepasslink_settings_init');

function safepasslink_add_admin_menu()
{
	add_options_page('Password Protection with Shareable Links', 'Password Protection with Shareable Links', 'manage_options', 'safepasslink', 'safepasslink_options_page');
}

function safepasslink_settings_init()
{
	register_setting('safepasslink', 'safepasslink_settings');

	add_settings_section(
		'safepasslink_safepasslink_section',
		__('Einstellungen für SafePassLink', 'safepasslink'),
		'safepasslink_settings_section_callback',
		'safepasslink'
	);

	add_settings_field(
		'safepasslink_password_protect',
		__('Passwortschutz aktivieren', 'safepasslink'),
		'safepasslink_password_protect_render',
		'safepasslink',
		'safepasslink_safepasslink_section'
	);

	add_settings_field(
		'safepasslink_text_field_0',
		__('Password', 'safepasslink'),
		'safepasslink_text_field_0_render',
		'safepasslink',
		'safepasslink_safepasslink_section'
	);
}

function safepasslink_password_protect_render()
{
	$options = get_option('safepasslink_settings');
	?>
	<input type='checkbox' name='safepasslink_settings[safepasslink_password_protect]' <?php checked(isset($options['safepasslink_password_protect']) && $options['safepasslink_password_protect']); ?> value='1'>
	<?php
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
	echo __('Geben Sie hier Ihre Einstellungen für den Passwortschutz ein.', 'safepasslink');
}


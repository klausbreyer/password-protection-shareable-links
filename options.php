<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('admin_menu', 'passpass_add_admin_menu');
add_action('admin_init', 'passpass_settings_init');

function passpass_add_admin_menu()
{
	add_options_page('PassPass', 'PassPass', 'manage_options', 'passpass', 'passpass_options_page');
}

function passpass_settings_init()
{
	register_setting('passpass', 'passpass_settings');

	add_settings_section(
		'passpass_passpass_section',
		esc_html__('Settings for PassPass - Password Protection with Shareable Links', 'passpass'),
		'passpass_settings_section_callback',
		'passpass'
	);

	add_settings_field(
		'passpass_password_protect',
		esc_html__('Enable Password Protection', 'passpass'),
		'passpass_password_protect_render',
		'passpass',
		'passpass_passpass_section'
	);

	add_settings_field(
		'passpass_text_field_0',
		esc_html__('Password', 'passpass'),
		'passpass_text_field_0_render',
		'passpass',
		'passpass_passpass_section'
	);
}

function passpass_password_protect_render()
{
	$options = get_option('passpass_settings');
	?>
	<input type='checkbox' name='passpass_settings[passpass_password_protect]' <?php checked(isset($options['passpass_password_protect']) && $options['passpass_password_protect']); ?> value='1'>
	<?php
}

function passpass_text_field_0_render()
{
	$options = get_option('passpass_settings');
	if (!is_array($options)) {
		$options = array('passpass_text_field_0' => '');
	}

	?>
	<input type='text' name='passpass_settings[passpass_text_field_0]' value='<?php echo esc_attr($options['passpass_text_field_0']); ?>'>
	<?php
}

function passpass_options_page()
{
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields('passpass');
		do_settings_sections('passpass');
		submit_button();
		?>
	</form>
	<?php
}
function passpass_settings_section_callback()
{
	esc_html_e('Enter your settings for password protection here.', 'passpass');
}

add_action('admin_notices', 'passpass_check_configuration');
function passpass_check_configuration()
{
	$options = get_option('passpass_settings');
	if (!is_array($options)) {
		$options = array('passpass_text_field_0' => '');
	}

	$isPasswordProtectionEnabled = isset($options['passpass_password_protect']) && $options['passpass_password_protect'];
	$savedPassword = $options['passpass_text_field_0'] ?? '';

	// Check if password protection is enabled and password is set
	if (!$isPasswordProtectionEnabled || strlen($savedPassword) === 0) {
		// Plugin is not configured correctly, show warning
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>
					<?php esc_html_e('PassPass', 'passpass'); ?>
				</strong>
				<?php esc_html_e('is installed but not configured correctly. Please set a password and enable password protection in the', 'passpass'); ?> <a href="options-general.php?page=passpass">
					<?php esc_html_e('plugin settings', 'passpass'); ?>
				</a>.
			</p>
		</div>
		<?php
	}
}

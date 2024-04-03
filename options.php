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
		esc_html__('Settings for Password Protection with Shareable Links', 'ppwsl'),
		'ppwsl_settings_section_callback',
		'password-protection-with-shareable-links'
	);

	add_settings_field(
		'ppwsl_password_protect',
		esc_html__('Enable Password Protection', 'ppwsl'),
		'ppwsl_password_protect_render',
		'password-protection-with-shareable-links',
		'ppwsl_ppwsl_section'
	);

	add_settings_field(
		'ppwsl_text_field_0',
		esc_html__('Password', 'ppwsl'),
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
	<input type='text' name='ppwsl_settings[ppwsl_text_field_0]' value='<?php echo esc_attr($options['ppwsl_text_field_0']); ?>'>
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
	esc_html_e('Enter your settings for password protection here.', 'ppwsl');
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

	// Check if password protection is enabled and password is set
	if (!$isPasswordProtectionEnabled || strlen($savedPassword) === 0) {
		// Plugin is not configured correctly, show warning
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>
					<?php esc_html_e('Password Protection with Shareable Links', 'ppwsl'); ?>
				</strong>
				<?php esc_html_e('is installed but not configured correctly. Please set a password and enable password protection in the', 'ppwsl'); ?> <a href="options-general.php?page=password-protection-with-shareable-links">
					<?php esc_html_e('plugin settings', 'ppwsl'); ?>
				</a>.
			</p>
		</div>
		<?php
	}
}

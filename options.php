<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('admin_menu', 'ppsl_add_admin_menu');
add_action('admin_init', 'ppsl_settings_init');

function ppsl_add_admin_menu()
{
	add_options_page('Password Protection with Shareable Links', 'Password Protection with Shareable Links', 'manage_options', 'password-protection-shareable-links', 'ppsl_options_page');
}

function ppsl_settings_init()
{
	register_setting('password-protection-shareable-links', 'ppsl_settings');

	add_settings_section(
		'ppsl_ppsl_section',
		esc_html__('Settings Password Protection with Shareable Links', 'password-protection-shareable-links'),
		'ppsl_settings_section_callback',
		'password-protection-shareable-links'
	);

	add_settings_field(
		'ppsl_password_protect',
		esc_html__('Enable Password Protection', 'password-protection-shareable-links'),
		'ppsl_password_protect_render',
		'password-protection-shareable-links',
		'ppsl_ppsl_section'
	);

	add_settings_field(
		'ppsl_text_field_0',
		esc_html__('Password', 'password-protection-shareable-links'),
		'ppsl_text_field_0_render',
		'password-protection-shareable-links',
		'ppsl_ppsl_section'
	);
}

function ppsl_password_protect_render()
{
	$options = get_option('ppsl_settings');
	?>
	<input type='checkbox' name='ppsl_settings[ppsl_password_protect]' <?php checked(isset($options['ppsl_password_protect']) && $options['ppsl_password_protect']); ?> value='1'>
	<?php
}

function ppsl_text_field_0_render()
{
	$options = get_option('ppsl_settings');
	if (!is_array($options)) {
		$options = array('ppsl_text_field_0' => '');
	}

	?>
	<input type='text' name='ppsl_settings[ppsl_text_field_0]' value='<?php echo esc_attr($options['ppsl_text_field_0']); ?>'>
	<?php
}

function ppsl_options_page()
{
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields('password-protection-shareable-links');
		do_settings_sections('password-protection-shareable-links');
		submit_button();
		?>
	</form>
	<?php
}
function ppsl_settings_section_callback()
{
	esc_html_e('Enter your settings for password protection here.', 'password-protection-shareable-links');
}

add_action('admin_notices', 'ppsl_check_configuration');
function ppsl_check_configuration()
{
	$options = get_option('ppsl_settings');
	if (!is_array($options)) {
		$options = array('ppsl_text_field_0' => '');
	}

	$isPasswordProtectionEnabled = isset($options['ppsl_password_protect']) && $options['ppsl_password_protect'];
	$savedPassword = $options['ppsl_text_field_0'] ?? '';

	// Check if password protection is enabled and password is set
	if (!$isPasswordProtectionEnabled || strlen($savedPassword) === 0) {
		// Plugin is not configured correctly, show warning
		?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>
					<?php esc_html_e('Password Protection with Shareable Links', 'password-protection-shareable-links'); ?>
				</strong>
				<?php esc_html_e('is installed but not configured correctly. Please set a password and enable password protection in the', 'password-protection-shareable-links'); ?> <a href="options-general.php?page=password-protection-shareable-links">
					<?php esc_html_e('plugin settings', 'password-protection-shareable-links'); ?>
				</a>.
			</p>
		</div>
		<?php
	}
}

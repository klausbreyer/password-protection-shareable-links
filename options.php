<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('admin_menu', 'ppsl_add_admin_menu');
add_action('admin_init', 'ppsl_settings_init');

function ppsl_add_admin_menu()
{
	add_options_page(
		'Password Protection with Shareable Links',
		'Password Protection with Shareable Links',
		'manage_options',
		'password-protection-shareable-links',
		'ppsl_options_page'
	);
}

function ppsl_settings_init()
{
	register_setting(
		'password-protection-shareable-links',
		'ppsl_settings',
		array(
			'sanitize_callback' => 'ppsl_sanitize_settings',
		)
	);

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

function ppsl_sanitize_settings($input)
{
	$sanitized_input = array();

	// Sanitization für 'ppsl_password_protect' Checkbox
	$sanitized_input['ppsl_password_protect'] = isset($input['ppsl_password_protect']) && $input['ppsl_password_protect'] == '1' ? '1' : '0';

	// Sanitization für 'ppsl_text_field_0' Textfeld (Passwort)
	if (isset($input['ppsl_text_field_0'])) {
		$sanitized_input['ppsl_text_field_0'] = sanitize_text_field($input['ppsl_text_field_0']);
	} else {
		$sanitized_input['ppsl_text_field_0'] = '';
	}

	return $sanitized_input;
}

function ppsl_password_protect_render()
{
	$options = get_option('ppsl_settings');
	?>
	<input type='checkbox' name='ppsl_settings[ppsl_password_protect]' <?php checked(isset($options['ppsl_password_protect']) && $options['ppsl_password_protect'], '1', true); ?> value='1'>
	<?php
}

function ppsl_text_field_0_render()
{
	$options = get_option('ppsl_settings');
	$password = isset($options['ppsl_text_field_0']) ? sanitize_text_field($options['ppsl_text_field_0']) : '';
	?>
	<input type='text' name='ppsl_settings[ppsl_text_field_0]' value='<?php echo esc_attr($password); ?>'>
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
	ppsl_plugiplugi_footer();
}

function ppsl_settings_section_callback()
{
	esc_html_e('Enter your settings for password protection here.', 'password-protection-shareable-links');
}

// Rest Ihres Codes bleibt unverändert

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

function ppsl_plugiplugi_footer()
{
	$plugin_url = plugin_dir_url(__FILE__);
	?>
	<div class="your-plugin-footer" style="margin-top: 40px; display: flex; align-items: center; justify-content: left; flex-direction: row;">
		<span style="font-size: 1.25em; font-weight: 600; color: #BE185D; margin-right: 10px; flex-shrink: 0">
			Made by
		</span>
		<a href="https://plugiplugi.com" target="_blank" rel="noopener noreferrer" style="margin-right: 10px;">
			<img src="<?php echo esc_url($plugin_url . 'images/plugiplugi.png'); ?>" alt="Plugiplugi Logo" style="height: 2.5em;">
		</a>

	</div>
	<div>
		<span style="font-size: 1.25em; font-weight: 600; color: #BE185D;">
			No bullshit, minimal configuration WordPress plugins that do what they promise.
		</span>
	</div>

	<?php
}

<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('add_meta_boxes', 'ppsl_add_custom_box');
function ppsl_add_custom_box()
{
	$screen = get_post_types(['public' => true]); // Get all public post types

	foreach ($screen as $single_screen) {
		add_meta_box(
			'ppsl_sectionid',             // Unique ID
			'Protected Shareable Link',         // Box title
			'ppsl_custom_box_html',       // Content callback
			$single_screen,                       // Post type
			'side',                               // Context
			'high'                                // Priority
		);
	}
}
function ppsl_custom_box_html($post)
{
	$salt = get_option('ppsl_salt');
	$passwordOption = get_option('ppsl_settings');
	$password = $passwordOption['ppsl_text_field_0'] ?? '';

	$options = get_option('ppsl_settings');
	if (!is_array($options)) {
		$options = array('ppsl_text_field_0' => '');
	}

	if (!isset($options['ppsl_password_protect']) || !$options['ppsl_password_protect']) {
		?>
		<p>
			<?php esc_html_e('The plugin is not yet configured.', 'password-protection-shareable-links'); ?>
		<p>
		<p>
			<a href="/wp-admin/options-general.php?page=password-protection-shareable-links">
				<?php esc_html_e('Go to settings.', 'password-protection-shareable-links'); ?>
			</a>
		</p>
		<?php
		return;
	}

	$encryptedPassword = ppsl_encrypt($password, $salt);
	$permalink = get_permalink($post->ID);
	$separator = (wp_parse_url($permalink, PHP_URL_QUERY) == NULL) ? '?' : '&';
	$link = $permalink . $separator . 'password=' . urlencode($encryptedPassword);
	?>

	<div>
		<p>
			<?php esc_html_e('With this link, anyone will have direct access to the protected page or post. After accessing, free navigation is possible as if the password was manually entered. The password is securely encrypted in this link to ensure data protection.', 'password-protection-shareable-links'); ?>
		</p>
		<p><strong>
				<?php esc_html_e('Important:', 'password-protection-shareable-links'); ?>
			</strong>
			<?php esc_html_e('This link should only be shared with trusted individuals. Anyone who possesses this link will have access to all protected content.', 'password-protection-shareable-links'); ?>
		</p>

		<label for="ppsl_secure_link">
			<?php esc_html_e('Your secure link:', 'password-protection-shareable-links'); ?>
		</label>
		<input type="text" id="ppsl_secure_link" value="<?php echo esc_attr($link); ?>" readonly style="width: 100%; margin-bottom: 10px;">
		<button onclick="copyToClipboard()">
			<?php esc_html_e('Copy', 'password-protection-shareable-links'); ?>
		</button>
	</div>

	<script>
		function copyToClipboard() {
			var copyText = document.getElementById("ppsl_secure_link");
			copyText.select();
			document.execCommand("copy");
		}
	</script>

	<?php
}

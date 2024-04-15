<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('add_meta_boxes', 'passpass_add_custom_box');
function passpass_add_custom_box()
{
	$screen = get_post_types(['public' => true]); // Get all public post types

	foreach ($screen as $single_screen) {
		add_meta_box(
			'passpass_sectionid',             // Unique ID
			'Protected Shareable Link',         // Box title
			'passpass_custom_box_html',       // Content callback
			$single_screen,                       // Post type
			'side',                               // Context
			'high'                                // Priority
		);
	}
}
function passpass_custom_box_html($post)
{
	$salt = get_option('passpass_salt');
	$passwordOption = get_option('passpass_settings');
	$password = $passwordOption['passpass_text_field_0'] ?? '';

	$options = get_option('passpass_settings');
	if (!is_array($options)) {
		$options = array('passpass_text_field_0' => '');
	}

	if (!isset($options['passpass_password_protect']) || !$options['passpass_password_protect']) {
		?>
		<p>
			<?php esc_html_e('The plugin is not yet configured.', 'passpass'); ?>
		<p>
		<p>
			<a href="/wp-admin/options-general.php?page=passpass">
				<?php esc_html_e('Go to settings.', 'passpass'); ?>
			</a>
		</p>
		<?php
		return;
	}

	$encryptedPassword = passpass_encrypt($password, $salt);
	$permalink = get_permalink($post->ID);
	$separator = (wp_parse_url($permalink, PHP_URL_QUERY) == NULL) ? '?' : '&';
	$link = $permalink . $separator . 'password=' . urlencode($encryptedPassword);
	?>

	<div>
		<p>
			<?php esc_html_e('With this link, anyone will have direct access to the protected page or post. After accessing, free navigation is possible as if the password was manually entered. The password is securely encrypted in this link to ensure data protection.', 'passpass'); ?>
		</p>
		<p><strong>
				<?php esc_html_e('Important:', 'passpass'); ?>
			</strong>
			<?php esc_html_e('This link should only be shared with trusted individuals. Anyone who possesses this link will have access to all protected content.', 'passpass'); ?>
		</p>

		<label for="passpass_secure_link">
			<?php esc_html_e('Your secure link:', 'passpass'); ?>
		</label>
		<input type="text" id="passpass_secure_link" value="<?php echo esc_attr($link); ?>" readonly style="width: 100%; margin-bottom: 10px;">
		<button onclick="copyToClipboard()">
			<?php esc_html_e('Copy', 'passpass'); ?>
		</button>
	</div>

	<script>
		function copyToClipboard() {
			var copyText = document.getElementById("passpass_secure_link");
			copyText.select();
			document.execCommand("copy");
		}
	</script>

	<?php
}

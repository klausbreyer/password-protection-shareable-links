<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('add_meta_boxes', 'ppwsl_add_custom_box');
function ppwsl_add_custom_box()
{
	$screen = get_post_types(['public' => true]); // Get all public post types

	foreach ($screen as $single_screen) {
		add_meta_box(
			'ppwsl_sectionid',             // Unique ID
			'Protected Shareable Link',         // Box title
			'ppwsl_custom_box_html',       // Content callback
			$single_screen,                       // Post type
			'side',                               // Context
			'high'                                // Priority
		);
	}
}
function ppwsl_custom_box_html($post)
{
	$salt = get_option('ppwsl_salt');
	$passwordOption = get_option('ppwsl_settings');
	$password = $passwordOption['ppwsl_text_field_0'] ?? '';

	$options = get_option('ppwsl_settings');
	if (!is_array($options)) {
		$options = array('ppwsl_text_field_0' => '');
	}

	if (!isset($options['ppwsl_password_protect']) || !$options['ppwsl_password_protect']) {
		?>
		<p>
			<?php _e('The plugin is not yet configured.', 'ppwsl'); ?>
		<p>
		<p>
			<a href="/wp-admin/options-general.php?page=password-protection-with-shareable-links">
				<?php _e('Go to settings.', 'ppwsl'); ?>
			</a>
		</p>
		<?php
		return;
	}

	$encryptedPassword = ppwsl_encrypt($password, $salt);
	$permalink = get_permalink($post->ID);
	$separator = (parse_url($permalink, PHP_URL_QUERY) == NULL) ? '?' : '&';
	$link = $permalink . $separator . 'password=' . urlencode($encryptedPassword);
	?>

	<div>
		<p>
			<?php _e('With this link, anyone will have direct access to the protected page or post. After accessing, free navigation is possible as if the password was manually entered. The password is securely encrypted in this link to ensure data protection.', 'ppwsl'); ?>
		</p>
		<p><strong>
				<?php _e('Important:', 'ppwsl'); ?>
			</strong>
			<?php _e('This link should only be shared with trusted individuals. Anyone who possesses this link will have access to all protected content.', 'ppwsl'); ?>
		</p>

		<label for="ppwsl_secure_link">
			<?php _e('Your secure link:', 'ppwsl'); ?>
		</label>
		<input type="text" id="ppwsl_secure_link" value="<?php echo esc_attr($link); ?>" readonly style="width: 100%; margin-bottom: 10px;">
		<button onclick="copyToClipboard()">
			<?php _e('Copy', 'ppwsl'); ?>
		</button>
	</div>

	<script>
		function copyToClipboard() {
			var copyText = document.getElementById("ppwsl_secure_link");
			copyText.select();
			document.execCommand("copy");
		}
	</script>

	<?php
}

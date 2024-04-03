<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('add_meta_boxes', 'ppwsl_add_custom_box');
function ppwsl_add_custom_box()
{
	$screen = get_post_types(['public' => true]); // Holt alle öffentlichen Post-Typen

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
			Das Plugin ist noch nicht konfiguriert.
		<p>
		<p>
			<a href="/wp-admin/options-general.php?page=password-protection-with-shareable-links">Zu den Einstellungen.</a>
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
		<p>Mit diesem Link erhält jeder direkten Zugriff auf die geschützte Seite oder den Beitrag. Nach dem Zugriff ist eine freie Navigation möglich, als wäre das Passwort manuell eingegeben worden. Das Passwort ist in diesem Link sicher verschlüsselt, um den Schutz der Daten zu gewährleisten.</p>
		<p><strong>Wichtig:</strong> Dieser Link sollte nur mit Personen geteilt werden, denen vertraut wird. Jeder, der diesen Link besitzt, hat Zugang zu allen geschützten Inhalten.</p>

		<label for="ppwsl_secure_link">Ihr sicherer Link:</label>
		<input type="text" id="ppwsl_secure_link" value="<?php echo esc_attr($link); ?>" readonly style="width: 100%; margin-bottom: 10px;">
		<button onclick="copyToClipboard()">Kopieren</button>
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

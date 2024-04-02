<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

add_action('add_meta_boxes', 'safepasslink_add_custom_box');
function safepasslink_add_custom_box()
{
	$screen = get_post_types(['public' => true]); // Holt alle öffentlichen Post-Typen

	foreach ($screen as $single_screen) {
		add_meta_box(
			'safepasslink_sectionid',             // Unique ID
			'SafePassLink Sicherer Link',         // Box title
			'safepasslink_custom_box_html',       // Content callback
			$single_screen,                       // Post type
			'side',                               // Context
			'high'                                // Priority
		);
	}
}

function safepasslink_custom_box_html($post)
{
	$salt = get_option('safepasslink_salt');
	$passwordOption = get_option('safepasslink_settings');
	$password = $passwordOption['safepasslink_text_field_0'] ?? '';
	if (!empty($password)) {
		$encryptedPassword = safepasslink_encrypt($password, $salt);
		$permalink = get_permalink($post->ID);

		// Prüfen, ob die URL bereits einen Query-Parameter enthält
		$separator = (parse_url($permalink, PHP_URL_QUERY) == NULL) ? '?' : '&';

		$link = $permalink . $separator . 'password=' . urlencode($encryptedPassword);

		echo "<p><strong>Sicherer Link:</strong> <a href='$link' target='_blank' rel='noopener noreferrer'>$link</a></p>";
		echo "<p><strong>Warnung:</strong> Dies ist ein sicherer Link. Wenn dieser Link weitergegeben wird, wird das Passwort für den Nutzer vorausgefüllt. Gib diesen Link also nur an Personen, denen du vertraust.</p>";
	} else {
		echo "<p>Bitte setzen Sie zuerst ein Passwort in den SafePassLink Einstellungen.</p>";
	}
}

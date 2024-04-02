<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly


function safepasslink_show_password_form_with_notice()
{
	$form_html = '<div style="margin: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">';
	$form_html .= '<p>Sie sind dabei, sich über einen privaten Link auf eine passwortgeschützte Seite einzuloggen. Bitte bestätigen Sie den Zugang.</p>';
	$form_html .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
	$form_html .= '<input type="hidden" name="safepasslink_password_confirm" value="1">'; // Verstecktes Feld zur Bestätigung der Formularübermittlung
	$form_html .= '<label for="safepasslink_duration">Wie lange möchten Sie eingeloggt bleiben?</label>';
	$form_html .= '<select id="safepasslink_duration" name="safepasslink_duration">';
	$form_html .= '<option value="3600">1 Stunde</option>';
	$form_html .= '<option value="86400">1 Tag</option>';
	$form_html .= '<option value="604800">1 Woche</option>';
	$form_html .= '<option value="2592000">1 Monat</option>';
	$form_html .= '<option value="31536000">1 Jahr</option>';
	$form_html .= '</select>';
	$form_html .= '<div style="margin-top: 10px;"><input type="submit" value="Zugang bestätigen"></div>';
	$form_html .= '</form>';
	$form_html .= '</div>';

	echo $form_html;
}



function safepasslink_show_password_form()
{
	$form_html = '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
        <label for="safepasslink_password">Bitte geben Sie das Passwort ein:</label>
        <input type="password" id="safepasslink_password" name="safepasslink_password">
        <label for="safepasslink_duration">Wie lange möchten Sie eingeloggt bleiben?</label>
        <select id="safepasslink_duration" name="safepasslink_duration">
            <option value="3600">1 Stunde</option>
            <option value="86400">1 Tag</option>
            <option value="604800">1 Woche</option>
            <option value="2592000">1 Monat</option>
            <option value="31536000">1 Jahr</option>
        </select>
        <input type="submit" value="Zugang">
    </form>';

	echo $form_html;
}

<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

function safepasslink_show_header()
{
	?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Password Protection</title>
		<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'dist/styles.css'; ?>">
	</head>

	<body>
		<?php
}

function safepasslink_show_footer()
{
	?>
	</body>

	</html>
	<?php
}

function safepasslink_show_password_form_with_notice()
{
	safepasslink_show_header();
	?>
	<div class="flex min-h-screen items-center justify-center px-6 py-12">
		<div class="w-full max-w-md space-y-8 border rounded-lg border-gray-300 p-6 shadow-lg">
			<p class="text-sm text-gray-700">Sie sind dabei, sich über einen privaten Link auf eine passwortgeschützte Seite einzuloggen. Bitte bestätigen Sie den Zugang.</p>
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6">
				<input type="hidden" name="safepasslink_password_confirm" value="1"> <!-- Verstecktes Feld zur Bestätigung der Formularübermittlung -->
				<div class="w-auto">
					<label for="safepasslink_duration" class="block text-sm font-medium text-gray-700">Wie lange möchten Sie eingeloggt bleiben?</label>
					<select id="safepasslink_duration" name="safepasslink_duration" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 p-1">
						<option value="3600">1 Stunde</option>
						<option value="86400">1 Tag</option>
						<option value="604800">1 Woche</option>
						<option value="2592000">1 Monat</option>
						<option value="31536000">1 Jahr</option>
					</select>
				</div>
				<div>
					<button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
						Zugang bestätigen
					</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	safepasslink_show_footer();
}

function safepasslink_show_password_form()
{
	safepasslink_show_header();
	?>
	<!-- Beginn des Formulars, gestaltet mit Tailwind CSS -->
	<div class="flex min-h-screen items-center justify-center px-4 py-12">
		<div class="w-full max-w-md space-y-6">
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
				<p class="text-sm text-gray-700">Sie sind dabei, sich über einen privaten Link auf eine passwortgeschützte Seite einzuloggen. Bitte bestätigen Sie den Zugang.</p>
				<div>
					<label for="safepasslink_password" class="block text-sm font-medium text-gray-700">Bitte geben Sie das Passwort ein:</label>
					<input type="password" id="safepasslink_password" name="safepasslink_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
				</div>
				<div class="w-auto">
					<label for="safepasslink_duration" class="block text-sm font-medium text-gray-700">Wie lange möchten Sie eingeloggt bleiben?</label>
					<select id="safepasslink_duration" name="safepasslink_duration" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600">1 Stunde</option>
						<option value="86400">1 Tag</option>
						<option value="604800">1 Woche</option>
						<option value="2592000">1 Monat</option>
						<option value="31536000">1 Jahr</option>
					</select>
				</div>
				<input type="submit" value="Zugang" class="flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
			</form>
		</div>
	</div>
	<?php
	safepasslink_show_footer();
}

<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

function ppwsl_show_header()
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

function ppwsl_show_footer()
{
	?>
	</body>

	</html>
	<?php
}

function ppwsl_show_password_form_with_notice()
{
	ppwsl_show_header();
	?>
	<div class="flex items-center justify-center min-h-screen px-6 py-12">
		<div class="w-full max-w-md p-6 space-y-8 border border-gray-300 rounded-lg shadow-lg">
			<p class="text-sm text-gray-700">Willkommen! Sie haben einen speziellen Zugangslink erhalten, der es Ihnen ermöglicht, direkt auf bestimmte Inhalte zuzugreifen, die sonst durch ein Passwort geschützt sind. Dieser Link beinhaltet bereits das erforderliche Passwort in verschlüsselter Form. Bitte bestätigen Sie nachfolgend, wie lange Sie eingeloggt bleiben möchten, um nahtlos auf die Inhalte zugreifen zu können, ohne das Passwort erneut eingeben zu müssen.</p>

			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6">
				<input type="hidden" name="ppwsl_password_confirm" value="1"> <!-- Verstecktes Feld zur Bestätigung der Formularübermittlung -->
				<div class="w-auto">
					<label for="ppwsl_duration" class="block text-sm font-medium text-gray-700">Wie lange möchten Sie eingeloggt bleiben?</label>
					<select id="ppwsl_duration" name="ppwsl_duration" class="block p-1 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600">1 Stunde</option>
						<option value="86400">1 Tag</option>
						<option value="604800">1 Woche</option>
						<option value="2592000">1 Monat</option>
						<option value="31536000">1 Jahr</option>
					</select>
				</div>
				<div>
					<button type="submit" class="flex justify-center w-full px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
						Zugang bestätigen
					</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	ppwsl_show_footer();
}

function ppwsl_show_password_form($error = false)
{
	ppwsl_show_header();
	// Holen Sie den zuvor ausgewählten Wert, default ist 3600 Sekunden
	$selectedDuration = $_POST['ppwsl_duration'] ?? '3600';
	?>
	<!-- Beginn des Formulars, gestaltet mit Tailwind CSS -->
	<div class="flex items-center justify-center min-h-screen px-4 py-12">
		<div class="w-full max-w-md space-y-6">
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="px-8 pt-6 pb-8 mb-4 space-y-6 bg-white rounded shadow-md">
				<p class="text-gray-600 text-md">Sie sind im Begriff, eine passwortgeschützte Seite zu betreten. Um Zugang zu den geschützten Inhalten zu erhalten, müssen Sie das korrekte Passwort eingeben. Bitte wählen Sie auch, wie lange Sie ohne erneute Passworteingabe auf die Inhalte zugreifen möchten.</p>
				<?php if ($error): ?>
					<?php ppwsl_alert("Das eingegebene Passwort ist falsch. Bitte versuchen Sie es erneut."); ?>
				<?php endif; ?>
				<div>
					<label for="ppwsl_password" class="block text-sm font-medium text-gray-700">Bitte geben Sie das Passwort ein:</label>
					<input type="password" id="ppwsl_password" name="ppwsl_password" class="block w-full mt-1 border-b-2 border-gray-700 rounded-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
				</div>
				<div class="w-auto">
					<label for="ppwsl_duration" class="block text-sm font-medium text-gray-700">Wie lange möchten Sie eingeloggt bleiben?</label>
					<select id="ppwsl_duration" name="ppwsl_duration" class="block px-2 py-1 mt-1 border-gray-700 rounded-sm shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600" <?php echo $selectedDuration == '3600' ? 'selected' : ''; ?>>1 Stunde</option>
						<option value="86400" <?php echo $selectedDuration == '86400' ? 'selected' : ''; ?>>1 Tag</option>
						<option value="604800" <?php echo $selectedDuration == '604800' ? 'selected' : ''; ?>>1 Woche</option>
						<option value="2592000" <?php echo $selectedDuration == '2592000' ? 'selected' : ''; ?>>1 Monat</option>
						<option value="31536000" <?php echo $selectedDuration == '31536000' ? 'selected' : ''; ?>>1 Jahr</option>
					</select>
				</div>
				<input type="submit" value="Zugang" class="flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
			</form>
		</div>
	</div>
	<?php
	ppwsl_show_footer();
}


function ppwsl_alert($text)
{
	?>
	<div class="p-4 rounded-md bg-red-50">
		<div class="flex">
			<div class="flex-shrink-0">
				<svg class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
					<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
				</svg>
			</div>
			<div class="ml-3">
				<p class="text-sm text-red-700">
					<?php echo $text; ?>
				</p>
			</div>
		</div>
	</div>
	<?php
}

function ppwsl_alert_page($text)
{
	ppwsl_show_header();
	?>
	<div class="flex items-center justify-center min-h-screen px-4 py-12">
		<div class="w-full max-w-xl space-y-6">

			<?php ppwsl_alert($text); ?>
		</div>
	</div>
	<?php
	ppwsl_show_footer();
}

function ppwsl_password_confirm_alert()
{
	ppwsl_alert_page("Das Passwort in Ihrem Link ist veraltet oder falsch. Bitte bitten Sie um einen neuen Link oder kontaktieren Sie den Administrator der Website.");
}

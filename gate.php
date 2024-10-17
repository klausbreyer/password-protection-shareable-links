<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly



function ppsl_show_header()
{
	$plugin_file_path = plugin_dir_path(__FILE__) . 'password-protection-shareable-links.php';
	$plugin_data = get_file_data($plugin_file_path, array('Version' => 'Version'), 'plugin');
	$version = $plugin_data['Version'];

	?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
			<?php esc_html_e('Password Protection', 'password-protection-shareable-links'); ?>
		</title>
		<link rel="stylesheet" href="<?php echo esc_url(plugin_dir_url(__FILE__) . 'dist/styles.css?ver=' . $version); ?>">
	</head>

	<body>
		<?php
}

function ppsl_show_footer()
{
	?>
	</body>

	</html>
	<?php
}
function ppsl_show_password_form_with_notice()
{
	ppsl_show_header();
	?>
	<div class="flex items-center justify-center px-6 py-6 md:min-h-screen">
		<div class="w-full max-w-md p-6 space-y-8 border border-gray-300 rounded-lg shadow-lg">
			<p class="font-bold text-gray-700 text-md">
				<?php esc_html_e('Welcome!', 'password-protection-shareable-links'); ?>
			</p>

			<p class="text-gray-700 text-md">
				<?php esc_html_e('You have received a special access link that allows you to directly access specific content that is otherwise protected by a password. This link already contains the required password in encrypted form. Please confirm below how long you would like to stay logged in to seamlessly access the content without having to enter the password again.', 'password-protection-shareable-links'); ?>
			</p>

			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6">
				<?php wp_nonce_field('ppsl_nonce'); ?>
				<input type="hidden" name="ppsl_password_confirm" value="1"> <!-- Hidden field for form submission confirmation -->
				<div class="w-auto">
					<label for="ppsl_duration" class="block text-sm font-medium text-gray-700">
						<?php esc_html_e('How long would you like to stay logged in?', 'password-protection-shareable-links'); ?>
					</label>
					<select id="ppsl_duration" name="ppsl_duration" class="block p-1 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600">
							<?php esc_html_e('1 hour', 'password-protection-shareable-links'); ?>
						</option>
						<option value="86400">
							<?php esc_html_e('1 day', 'password-protection-shareable-links'); ?>
						</option>
						<option value="604800">
							<?php esc_html_e('1 week', 'password-protection-shareable-links'); ?>
						</option>
						<option value="2592000">
							<?php esc_html_e('1 month', 'password-protection-shareable-links'); ?>
						</option>
						<option value="31536000" selected>
							<?php esc_html_e('1 year', 'password-protection-shareable-links'); ?>
						</option>
					</select>
				</div>
				<div>
					<button type="submit" class="flex justify-center w-full px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
						<?php esc_html_e('Confirm Access', 'password-protection-shareable-links'); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	ppsl_show_footer();
}

function ppsl_show_password_form($error = false)
{
	ppsl_show_header();
	$selectedDuration = '31536000';
	// Get the previously selected value, default is 31536000 seconds
	if (isset($_POST['ppsl_duration']) && isset($_POST['ppsl_nonce']) && wp_verify_nonce($_POST['ppsl_nonce'], 'ppsl_nonce')) {
		$selectedDuration = $_POST['ppsl_duration'];
	}
	?>
	<!-- Start of the form, styled with Tailwind CSS -->
	<div class="flex items-center justify-center px-6 py-6 md:min-h-screen">
		<div class="w-full max-w-md p-6 space-y-8 border border-gray-300 rounded-lg shadow-lg">
			<p class="font-bold text-gray-700 text-md">
				<?php esc_html_e('Welcome!', 'password-protection-shareable-links'); ?>
			</p>

			<p class="text-gray-600 text-md">
				<?php esc_html_e('You are about to enter a password-protected page. To access the protected content, you need to enter the correct password. Please also select how long you want to access the content without entering the password again.', 'password-protection-shareable-links'); ?>
			</p>
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6 ">
				<?php wp_nonce_field('ppsl_nonce'); ?>

				<?php if ($error): ?>
					<?php ppsl_alert(__("The entered password is incorrect. Please try again.", 'password-protection-shareable-links')); ?>
				<?php endif; ?>
				<div>
					<label for="ppsl_password" class="block text-sm font-medium text-gray-700">
						<?php esc_html_e('Please enter the password:', 'password-protection-shareable-links'); ?>
					</label>
					<input type="password" id="ppsl_password" name="ppsl_password" class="block w-full mt-1 border-b-2 border-gray-700 rounded-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
				</div>
				<div class="w-auto">
					<label for="ppsl_duration" class="block text-sm font-medium text-gray-700">
						<?php esc_html_e('How long would you like to stay logged in?', 'password-protection-shareable-links'); ?>
					</label>
					<select id="ppsl_duration" name="ppsl_duration" class="block px-2 py-1 mt-1 border-gray-700 rounded-sm shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600" <?php echo $selectedDuration == '3600' ? 'selected' : ''; ?>>
							<?php esc_html_e('1 hour', 'password-protection-shareable-links'); ?>
						</option>
						<option value="86400" <?php echo $selectedDuration == '86400' ? 'selected' : ''; ?>>
							<?php esc_html_e('1 day', 'password-protection-shareable-links'); ?>
						</option>
						<option value="604800" <?php echo $selectedDuration == '604800' ? 'selected' : ''; ?>>
							<?php esc_html_e('1 week', 'password-protection-shareable-links'); ?>
						</option>
						<option value="2592000" <?php echo $selectedDuration == '2592000' ? 'selected' : ''; ?>>
							<?php esc_html_e('1 month', 'password-protection-shareable-links'); ?>
						</option>
						<option value="31536000" <?php echo $selectedDuration == '31536000' ? 'selected' : ''; ?>>
							<?php esc_html_e('1 year', 'password-protection-shareable-links'); ?>
						</option>
					</select>
				</div>
				<input type="submit" value="<?php esc_html_e('Access', 'password-protection-shareable-links'); ?>" class="flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
			</form>
		</div>
	</div>
	<?php
	ppsl_show_footer();
}



function ppsl_alert($text)
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
					<?php echo esc_html($text); ?>
				</p>
			</div>
		</div>
	</div>
	<?php
}

function ppsl_alert_page($text)
{
	ppsl_show_header();
	?>
	<div class="flex items-center justify-center min-h-screen px-4 py-6">
		<div class="w-full max-w-xl space-y-6">

			<?php ppsl_alert($text); ?>
		</div>
	</div>
	<?php
	ppsl_show_footer();
}

// Your i18n capable function
function ppsl_password_confirm_alert()
{
	ppsl_alert_page(esc_html__("The password in your link is outdated or incorrect. Please request a new link or contact the website administrator.", 'password-protection-shareable-links'));
}


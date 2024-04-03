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
			<p class="text-sm text-gray-700">
				<?php _e('Welcome! You have received a special access link that allows you to directly access specific content that is otherwise protected by a password. This link already contains the required password in encrypted form. Please confirm below how long you would like to stay logged in to seamlessly access the content without having to enter the password again.', 'ppwsl'); ?>
			</p>

			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="space-y-6">
				<input type="hidden" name="ppwsl_password_confirm" value="1"> <!-- Hidden field for form submission confirmation -->
				<div class="w-auto">
					<label for="ppwsl_duration" class="block text-sm font-medium text-gray-700">
						<?php _e('How long would you like to stay logged in?', 'ppwsl'); ?>
					</label>
					<select id="ppwsl_duration" name="ppwsl_duration" class="block p-1 mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600">
							<?php _e('1 hour', 'ppwsl'); ?>
						</option>
						<option value="86400">
							<?php _e('1 day', 'ppwsl'); ?>
						</option>
						<option value="604800">
							<?php _e('1 week', 'ppwsl'); ?>
						</option>
						<option value="2592000">
							<?php _e('1 month', 'ppwsl'); ?>
						</option>
						<option value="31536000">
							<?php _e('1 year', 'ppwsl'); ?>
						</option>
					</select>
				</div>
				<div>
					<button type="submit" class="flex justify-center w-full px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
						<?php _e('Confirm Access', 'ppwsl'); ?>
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
	// Get the previously selected value, default is 3600 seconds
	$selectedDuration = $_POST['ppwsl_duration'] ?? '3600';
	?>
	<!-- Start of the form, styled with Tailwind CSS -->
	<div class="flex items-center justify-center min-h-screen px-4 py-12">
		<div class="w-full max-w-md space-y-6">
			<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="px-8 pt-6 pb-8 mb-4 space-y-6 bg-white rounded shadow-md">
				<p class="text-gray-600 text-md">
					<?php _e('You are about to enter a password-protected page. To access the protected content, you need to enter the correct password. Please also select how long you want to access the content without entering the password again.', 'ppwsl'); ?>
				</p>
				<?php if ($error): ?>
					<?php ppwsl_alert(__("The entered password is incorrect. Please try again.", 'ppwsl')); ?>
				<?php endif; ?>
				<div>
					<label for="ppwsl_password" class="block text-sm font-medium text-gray-700">
						<?php _e('Please enter the password:', 'ppwsl'); ?>
					</label>
					<input type="password" id="ppwsl_password" name="ppwsl_password" class="block w-full mt-1 border-b-2 border-gray-700 rounded-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
				</div>
				<div class="w-auto">
					<label for="ppwsl_duration" class="block text-sm font-medium text-gray-700">
						<?php _e('How long would you like to stay logged in?', 'ppwsl'); ?>
					</label>
					<select id="ppwsl_duration" name="ppwsl_duration" class="block px-2 py-1 mt-1 border-gray-700 rounded-sm shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
						<option value="3600" <?php echo $selectedDuration == '3600' ? 'selected' : ''; ?>>
							<?php _e('1 hour', 'ppwsl'); ?>
						</option>
						<option value="86400" <?php echo $selectedDuration == '86400' ? 'selected' : ''; ?>>
							<?php _e('1 day', 'ppwsl'); ?>
						</option>
						<option value="604800" <?php echo $selectedDuration == '604800' ? 'selected' : ''; ?>>
							<?php _e('1 week', 'ppwsl'); ?>
						</option>
						<option value="2592000" <?php echo $selectedDuration == '2592000' ? 'selected' : ''; ?>>
							<?php _e('1 month', 'ppwsl'); ?>
						</option>
						<option value="31536000" <?php echo $selectedDuration == '31536000' ? 'selected' : ''; ?>>
							<?php _e('1 year', 'ppwsl'); ?>
						</option>
					</select>
				</div>
				<input type="submit" value="<?php _e('Access', 'ppwsl'); ?>" class="flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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

// Your i18n capable function
function ppwsl_password_confirm_alert()
{
	ppwsl_alert_page(__("The password in your link is outdated or incorrect. Please request a new link or contact the website administrator.", 'ppwsl'));
}

<?php
/*
Plugin Name: Simple Dark Mode Switcher (SDMS)
Description: Enables an option to switch between dark and light modes
Version: 1.0
Author: Pragadh Shrestha
*/

// Register the settings.
function sdms_register_settings() {
	register_setting('sdms_options_group', 'sdms_default_dark_mode', 'intval');
	register_setting('sdms_options_group', 'sdms_timezone_dark_mode', 'intval');
	register_setting('sdms_options_group', 'sdms_custom_css', 'wp_kses_post');
	register_setting('sdms-settings-group', 'sdms_light_mode_logo');
	register_setting('sdms-settings-group', 'sdms_dark_mode_logo');
	register_setting('sdms-settings-group', 'sdms_logo_width');
	register_setting('sdms-settings-group', 'sdms_logo_height');
}
add_action('admin_init', 'sdms_register_settings');

// Admin options page.
function sdms_create_menu() {
	add_options_page('Simple Dark Mode Settings', 'Simple Dark Mode', 'manage_options', 'sdms-settings', 'sdms_options_page');
}
add_action('admin_menu', 'sdms_create_menu');

function sdms_options_page() {
	?>
	<div class="wrap">
		<h1>Simple Dark Mode Settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields('sdms_options_group'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Default Dark Mode:</th>
					<td>
						<input type="checkbox" name="sdms_default_dark_mode" value="1" <?php checked(1, get_option('sdms_default_dark_mode'), true); ?> />
						<label for="sdms_default_dark_mode">Enable by default</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Shortcode:</th>
					<td>
						<input type="text" value="[dark_mode_toggle]" readonly onClick="this.setSelectionRange(0, this.value.length)">
						<p class="description">Copy the above shortcode to place the toggle button wherever you like.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Enable Dark Mode According to Website Timezone:</th>
					<td>
						<span style="color: red;">Coming soon.</span>
						<p class="description">Your website is currently using the <?php echo esc_html( wp_timezone_string() ); ?> timezone.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Light Mode Logo</th>
					<td>
						<span style="color: red;">Coming soon.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Dark Mode Logo</th>
					<td>
						<span style="color: red;">Coming soon.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Logo Dimensions</th>
					<td>
						<span style="color: red;">Coming soon.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Custom CSS:</th>
					<td>
						<textarea name="sdms_custom_css" rows="5" cols="50"><?php echo esc_textarea(get_option('sdms_custom_css')); ?></textarea>
						<p class="description">Add custom CSS for the Dark Mode toggle button. Do not include &lt;style&gt; tags.</p>
					</td>
				</tr>
				<p style="font-style: italic; margin-top: 20px;">This plugin is provided for free, but if you find it useful and would like to support my work, please consider <a href="https://www.buymeacoffee.com/caiohferreira" target="_blank" rel="noopener noreferrer">buying me a coffee</a>. Thank you for your support!</p>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// AJAX handler to get the default mode and timezone.
function get_sdms_default() {
	$default_dark_mode = get_option('sdms_default_dark_mode', '0');
	$timezone_mode = get_option('sdms_enable_timezone_mode', '0');
	$site_time_offset = get_option('gmt_offset');

	$response = array(
		'default' => $default_dark_mode,
		'timezoneMode' => $timezone_mode,
		'siteTimeOffset' => $site_time_offset
	);

	wp_send_json($response);
}
add_action('wp_ajax_get_sdms_default', 'get_sdms_default');
add_action('wp_ajax_nopriv_get_sdms_default', 'get_sdms_default');

function sdms_enqueue_custom_scripts_and_styles() {
	// Check if default dark mode is enabled
	$default_dark_mode = get_option('sdms_default_dark_mode', '0');

	if ($default_dark_mode == '1') {
		// If dark mode is set to default, apply the dark mode class on the body
		add_filter('body_class', function ($classes) {
			$classes[] = 'dark-mode';
			return $classes;
		});
	}

	wp_enqueue_style('sdms-dark-mode', plugin_dir_url(__FILE__) . 'css/dark-mode.css');
	wp_enqueue_script('sdms-dark-mode-toggle', plugin_dir_url(__FILE__) . 'js/dark-mode-toggle.js', array('jquery'), '1.0', true);
	
	$custom_css = get_option('sdms_custom_css');
	if (!empty($custom_css)) {
		wp_add_inline_style('sdms-dark-mode', $custom_css);
	}

	// Pass ajax_url to script.js
	wp_localize_script('sdms-dark-mode-toggle', 'frontendajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}
add_action('wp_enqueue_scripts', 'sdms_enqueue_custom_scripts_and_styles');

// Shortcode function.
function sdms_dark_mode_toggle_shortcode() {
	return '<button class="dark-mode-toggle" data-enabled-text="Disable Dark Mode" data-disabled-text="Enable Dark Mode">Enable Dark Mode</button>';
}
add_shortcode('dark_mode_toggle', 'sdms_dark_mode_toggle_shortcode');

// Direct call for theme developers.
function sdms_dark_mode_toggle() {
	echo sdms_dark_mode_toggle_shortcode();
}
?>

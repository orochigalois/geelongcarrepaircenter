<?php
/*
Plugin Name: Cost Calculator For WordPress
Plugin URI: https://codecanyon.net/item/cost-calculator-for-wordpress/21709867?ref=QuanticaLabs
Description: Cost Calculator plugin is a unique tool which allows you to easily create price estimation forms to give your client idea of the cost of your service.
Author: QuanticaLabs
Author URI: https://codecanyon.net/user/QuanticaLabs/portfolio?ref=QuanticaLabs
Version: 1.8
Text Domain: cost-calculator
*/

//translation
function cost_calculator_load_textdomain()
{
	load_plugin_textdomain("cost-calculator", false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'cost_calculator_load_textdomain');

//documentation link
function cost_calculator_documentation_link($links)
{
	$documentation_link = sprintf(__("<a href='%s' title='Documentation'>Documentation</a>", 'cost-calculator'), plugins_url('documentation/index.html', __FILE__)); 
	array_unshift($links, $documentation_link); 
	return $links;
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'cost_calculator_documentation_link');

//settings link
function cost_calculator_settings_link($links) 
{ 
	$settings_link = '<a href="admin.php?page=cost_calculator" title="Settings">Settings</a>'; 
	array_unshift($links, $settings_link); 
	return $links;
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'cost_calculator_settings_link');

function cost_calculator_enqueue_scripts()
{
	wp_enqueue_script("jquery-ui-core");
	wp_enqueue_script("jquery-ui-datepicker", false, array("jquery"), false, true);
	wp_enqueue_script("jquery-ui-selectmenu", false, array("jquery"), false, true);
	wp_enqueue_script("jquery-ui-slider", false, array("jquery"), false, true);
	wp_enqueue_script("jquery-ui-touch-punch", plugins_url('js/jquery.ui.touch-punch.min.js', __FILE__), array("jquery"), false, true);
	wp_enqueue_script("jquery-costCalculator", plugins_url('js/jquery.costCalculator.min.js', __FILE__), array("jquery"), false, true);
	wp_enqueue_script("jquery-qtip", plugins_url('js/jquery.qtip.min.js', __FILE__), array("jquery"), false, true);
	wp_enqueue_script("jquery-block-ui", plugins_url('js/jquery.blockUI.min.js', __FILE__), array("jquery"), false, true);
	if(function_exists("is_customize_preview") && !is_customize_preview())
		wp_enqueue_script('cost_calculator_main', plugins_url('js/cost_calculator.js', __FILE__), array("jquery"), false, true);
	wp_register_script("google-recaptcha-v2", "//google.com/recaptcha/api.js", array(), false, true);
	wp_enqueue_style("jquery-qtip", plugins_url('style/jquery.qtip.css', __FILE__));
	wp_enqueue_style("cc-template", plugins_url('/fonts/template/style.css', __FILE__));
	$cost_calculator_global_form_options = get_option("cost_calculator_global_form_options");
	if($cost_calculator_global_form_options["primary_font"]!="" && $cost_calculator_global_form_options["primary_font_custom"]=="")
		wp_enqueue_style("cc-google-font-primary", "//fonts.googleapis.com/css?family=" . urlencode($cost_calculator_global_form_options["primary_font"]) . (!empty($cost_calculator_global_form_options["primary_font_subset"]) ? "&subset=" . implode(",", $cost_calculator_global_form_options["primary_font_subset"]) : ""));
	else if($cost_calculator_global_form_options["primary_font_custom"]=="")
		wp_enqueue_style("cc-google-font-raleway", "//fonts.googleapis.com/css?family=Raleway:400&amp;subset=latin-ext");
	if($cost_calculator_global_form_options["secondary_font"]!="" && $cost_calculator_global_form_options["secondary_font_custom"]=="")
		wp_enqueue_style("cc-google-font-secondary", "//fonts.googleapis.com/css?family=" . urlencode($cost_calculator_global_form_options["secondary_font"]) . (!empty($cost_calculator_global_form_options["secondary_font_subset"]) ? "&subset=" . implode(",", $cost_calculator_global_form_options["secondary_font_subset"]) : ""));
	else if($cost_calculator_global_form_options["secondary_font_custom"]=="")
		wp_enqueue_style("cc-google-font-lato", "//fonts.googleapis.com/css?family=Lato:300,400&amp;subset=latin-ext");
	wp_enqueue_style("cost_calculator_style", plugins_url('style/style.css', __FILE__));
	wp_enqueue_style("cost_calculator_style_responsive", plugins_url('style/responsive.css', __FILE__));
	$cookie_is_rtl = (isset($_COOKIE['cm_direction']) ? $_COOKIE['cm_direction'] : (isset($_COOKIE['cs_direction']) ? $_COOKIE['cs_direction'] : (isset($_COOKIE['re_direction']) ? $_COOKIE['re_direction'] : '')));
	$cost_calculator_is_rtl = (is_rtl() && (($cookie_is_rtl!='' && $cookie_is_rtl!="LTR") || $cookie_is_rtl=='')) || ($cookie_is_rtl!='' && $cookie_is_rtl=="RTL") ? 1 : 0;
	if($cost_calculator_is_rtl)
		wp_enqueue_style("cost_calculator_style_rtl", plugins_url('style/rtl.css', __FILE__));
	ob_start();
	require_once("custom_colors.php");
	$custom_colors_css = ob_get_clean();
	wp_add_inline_style("cost_calculator_style", $custom_colors_css);
	
	$data = array();
	$data["ajaxurl"] = admin_url("admin-ajax.php");
	$data["is_rtl"] = $cost_calculator_is_rtl;
	
	//pass data to javascript
	$params = array(
		'l10n_print_after' => 'cost_calculator_config = ' . json_encode($data) . ';'
	);
	wp_localize_script("cost_calculator_main", "cost_calculator_config", $params);
}
add_action('wp_enqueue_scripts', 'cost_calculator_enqueue_scripts', 11);

//admin
if(is_admin())
{
	function cost_calculator_admin_menu()
	{
		$page = add_menu_page(__("Cost Calculator", 'cost-calculator'), __("Cost Calculator", 'cost-calculator'), 'manage_options', 'cost_calculator', 'cost_calculator_admin_page', 'dashicons-welcome-widgets-menus', 20);
		$global_config_page = add_submenu_page('cost_calculator', 'Global config', 'Global Config', 'manage_options', 'cost_calculator_admin_page_global_config', 'cost_calculator_admin_page_global_config');
		$email_config_page = add_submenu_page('cost_calculator', 'Template config', 'Template Config', 'manage_options', 'cost_calculator_admin_page_email_config', 'cost_calculator_admin_page_email_config');
		$import_dummy_data_page = add_submenu_page('cost_calculator', 'Import dummy data', 'Import Dummy Data', 'manage_options', 'cost_calculator_admin_page_import_dummy_data', 'cost_calculator_admin_page_import_dummy_data');
		add_action("admin_enqueue_scripts", "cost_calculator_admin_enqueue_scripts");
	}
	add_action('admin_menu', 'cost_calculator_admin_menu');
	
	function cost_calculator_admin_init()
	{
		wp_register_script("cost-calculator-colorpicker",  plugins_url('admin/js/colorpicker.js', __FILE__), array("jquery"));
		wp_register_script("cost-calculator-admin", plugins_url('admin/js/cost-calculator-admin.js', __FILE__), array("jquery", "jquery-ui-core", "jquery-ui-selectmenu", "jquery-ui-sortable", "jquery-ui-dialog", "cost-calculator-colorpicker", "shortcode"));
		wp_enqueue_style("cc-google-font-open-sans", '//fonts.googleapis.com/css?family=Open+Sans:400,400i&amp;subset=latin-ext');
		wp_register_style("cost-calculator-colorpicker", plugins_url('admin/style/colorpicker.css', __FILE__));
		wp_enqueue_style("cc-plugin", plugins_url('/fonts/template-admin/style.css', __FILE__));
		wp_register_style("jquery-ui-dialog", includes_url('css/jquery-ui-dialog.min.css', __FILE__));
		wp_register_style("cost-calculator-admin-style", plugins_url('admin/style/style.css', __FILE__), array("cost-calculator-colorpicker"));
		$cost_calculator_contact_form_options = get_option("cost_calculator_contact_form_options");
		if(!$cost_calculator_contact_form_options)
		{
			$cost_calculator_contact_form_options = array(
				"admin_name" => get_option("admin_email"),
				"admin_email" => get_option("admin_email"),
				"smtp_host" => "",
				"smtp_username" => "",
				"smtp_password" => "",
				"smtp_port" => "",
				"smtp_secure" => "",
				"email_subject" => __("Calculation from: [name]", 'cost-calculator'),
				"calculation_details_header" => __("Calculation details", 'cost-calculator'),
				"template" => "<html>
	<head>
	</head>
	<body>
		<table style='border: 1px solid black; border-collapse: collapse;'>
<tbody>
<tr style='border: 1px solid black;'>
<td style='border: 1px solid black;' colspan='2'><b>" . __("Contact details", 'cost-calculator') . "</b></td>
</tr>
<tr style='border: 1px solid black;'>
<td style='border: 1px solid black;'>Name</td>
<td style='border: 1px solid black;'>[name]</td>
</tr>
<tr style='border: 1px solid black;'>
<td style='border: 1px solid black;'>E-mail</td>
<td style='border: 1px solid black;'>[email]</td>
</tr>
<tr style='border: 1px solid black;'>
<td style='border: 1px solid black;'>Phone</td>
<td style='border: 1px solid black;'>[phone]</td>
</tr>
<tr style='border: 1px solid black;'>
<td style='border: 1px solid black;'>Message</td>
<td style='border: 1px solid black;'>[message]</td>
</tr>
</tbody>
</table>
<br><br>
[form_data]
	</body>
</html>",
				"name_message" => __("Please enter your name.", 'cost-calculator'),
				"email_message" => __("Please enter valid e-mail.", 'cost-calculator'),
				"phone_message" => __("Please enter your phone number.", 'cost-calculator'),
				"message_message" => __("Please enter your message.", 'cost-calculator'),
				"recaptcha_message" => __("Please verify captcha.", 'cost-calculator'),
				"terms_message" => __("Checkbox is required.", 'cost-calculator'),
				"thankyou_message" => __("Thank you for contacting us", 'cost-calculator'),
				"error_message" => __("Sorry, we can't send this message", 'cost-calculator')
			);
			add_option("cost_calculator_contact_form_options", $cost_calculator_contact_form_options);
		}
		else if(!get_option("cost_calculator_contact_form_options_updated"))
		{
			//update cost calculator contact form options
			$cost_calculator_contact_form_options["recaptcha_message"] = __("Please verify captcha.", 'cost-calculator');
			$cost_calculator_contact_form_options["terms_message"] = __("Checkbox is required.", 'cost-calculator');
			update_option("cost_calculator_contact_form_options", $cost_calculator_contact_form_options);
			add_option("cost_calculator_contact_form_options_updated", 1);
		}
		$cost_calculator_global_form_options = get_option("cost_calculator_global_form_options");
		if(!$cost_calculator_global_form_options)
		{
			$cost_calculator_global_form_options = array(
				"calculator_skin" => "default",
				"main_color" => "",
				"box_color" => "",
				"text_color" => "",
				"border_color" => "",
				"label_color" => "",
				"form_label_color" => "",
				"inactive_color" => "",
				"primary_font_custom" => "",
				"primary_font" => "",
				"primary_font_subset" => "",
				"secondary_font_custom" => "",
				"secondary_font" => "",
				"secondary_font_subset" => "",
				"send_email" => 1,
				"send_email_client" => 0,
				"save_calculation" => 1,
				"calculation_status" => "draft",
				"google_recaptcha" => 0,
				"recaptcha_site_key" => "",
				"recaptcha_secret_key" => ""
			);
			add_option("cost_calculator_global_form_options", $cost_calculator_global_form_options);
		}
	}
	add_action('admin_init', 'cost_calculator_admin_init');

	function cost_calculator_admin_enqueue_scripts($hook)
	{
		if($hook=="cost-calculator_page_cost_calculator_admin_page_email_config")
		{
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-tabs');
		}
		//if($hook=="toplevel_page_cost_calculator")
		//{
			wp_enqueue_script('cost-calculator-admin');
		//}
		wp_enqueue_style('jquery-ui-dialog');
		wp_enqueue_style('cost-calculator-admin-style');
		$data = array(
			'message_wrong_id' => __("Shortcode ID field accepts only the following characters: letters, numbers, hyphen(-) and underscore(_)", 'cost-calculator'),
			'message_content_area' => __("Please make sure that cost calculator content area isn't empty.", 'cost-calculator'),
			'message_shortcode_saved' => __("Cost Calculator shortcode saved.", 'cost-calculator'),
			'message_shortcode_delete' => __("Click OK to delete selected shortcode.", 'cost-calculator'),
			'message_shortcode_deleted' => __("Cost Calculator shortcode deleted.", 'cost-calculator'),
			'message_shortcode_exists' => __("Shortcode with given id already exists. Click OK to overwrite.", 'cost-calculator'),
			'message_row_delete' => __("Click OK to delete selected row.", 'cost-calculator'),
			'shortcode_id_label_new' => __("Create new shortcode id *", "cost-calculator"),
			'shortcode_id_label_edit' => __("Current shortcode id *", "cost-calculator"),
			'show_advanced_text' => __("Show advanced settings...", "cost-calculator"),
			'hide_advanced_text' => __("Hide advanced settings...", "cost-calculator"),
			'message_shortcode_id_description' => __("Unique identifier for cost calculator shortcode.", 'cost-calculator'),
			'message_shortcode_id_example' => __("Shortcode:", 'cost-calculator'),
			'message_import_in_progress' => __("Please wait and don't reload the page when import is in progress!", 'cost-calculator'),
			'message_import_error' => __("Error during import:", 'cost-calculator')
		);
		//pass data to javascript
		$params = array(
			'l10n_print_after' => 'cost_calculator_config = ' . json_encode($data) . ';'
		);
		wp_localize_script("cost-calculator-admin", "cost_calculator_config", $params);
	}
	
	function cost_calculator_admin_page()
	{
		$cost_calculator_global_form_options = array(
			"main_color" => '',
			"box_color" => '',
			"text_color" => '',
			"border_color" => '',
			"label_color" => '',
			"form_label_color" => '',
			"inactive_color" => '',
			"primary_font_custom" => '',
			"primary_font" => '',
			"primary_font_subset" => '',
			"secondary_font_custom" => '',
			"secondary_font" => '',
			"secondary_font_subset" => '',
			"send_email" => '',
			"send_email_client" => '',
			"save_calculation" => '',
			"calculation_status" => '',
			"google_recaptcha" => '',
			"recaptcha_site_key" => '',
			"recaptcha_secret_key" => ''
		);
		$cost_calculator_global_form_options = cost_calculator_stripslashes_deep(array_merge($cost_calculator_global_form_options, (array)get_option("cost_calculator_global_form_options")));
		require_once("admin/admin-page.php");
	}
	
	function cost_calculator_admin_page_email_config()
	{
		$message = "";
		if(isset($_POST["action"]) && $_POST["action"]=="save")
		{
			$cost_calculator_contact_form_options = array(
				"admin_name" => $_POST["admin_name"],
				"admin_email" => $_POST["admin_email"],
				"smtp_host" => $_POST["smtp_host"],
				"smtp_username" => $_POST["smtp_username"],
				"smtp_password" => $_POST["smtp_password"],
				"smtp_port" => $_POST["smtp_port"],
				"smtp_secure" => $_POST["smtp_secure"],
				"email_subject" => $_POST["email_subject"],
				"calculation_details_header" => $_POST["calculation_details_header"],
				"template" => $_POST["template"],
				"name_message" => $_POST["name_message"],
				"email_message" => $_POST["email_message"],
				"recaptcha_message" => $_POST["recaptcha_message"],
				"terms_message" => $_POST["terms_message"],
				"phone_message" => $_POST["phone_message"],
				"message_message" => $_POST["message_message"],
				"thankyou_message" => $_POST["thankyou_message"],
				"error_message" => $_POST["error_message"]
			);
			update_option("cost_calculator_contact_form_options", $cost_calculator_contact_form_options);
			$message = __("Options saved!", "cost-calculator");
		}
		$cost_calculator_contact_form_options = array(
			"admin_name" => '',
			"admin_email" => '',
			"smtp_host" => '',
			"smtp_username" => '',
			"smtp_password" => '',
			"smtp_port" => '',
			"smtp_secure" => '',
			"email_subject" => '',
			"calculation_details_header" => '',
			"template" => '',
			"name_message" => '',
			"email_message" => '',
			"phone_message" => '',
			"message_message" => '',
			"recaptcha_message" => '',
			"terms_message" => '',
			"thankyou_message" => '',
			"error_message" => ''
		);
		$cost_calculator_contact_form_options = cost_calculator_stripslashes_deep(array_merge($cost_calculator_contact_form_options, (array)get_option("cost_calculator_contact_form_options")));
		require_once("admin/admin-page-email-config.php");
	}
	
	function cost_calculator_admin_page_global_config()
	{	
		$message = "";
		if(isset($_POST["action"]) && $_POST["action"]=="save")
		{
			$cost_calculator_global_form_options = array(
				"calculator_skin" => $_POST["calculator_skin"],
				"main_color" => $_POST["main_color"],
				"box_color" => $_POST["box_color"],
				"text_color" => $_POST["text_color"],
				"border_color" => $_POST["border_color"],
				"label_color" => $_POST["label_color"],
				"form_label_color" => $_POST["form_label_color"],
				"inactive_color" => $_POST["inactive_color"],
				"primary_font_custom" => $_POST["primary_font_custom"],
				"primary_font" => $_POST["primary_font"],
				"primary_font_subset" => (isset($_POST["primary_font_subset"]) ? $_POST["primary_font_subset"] : ""),
				"secondary_font_custom" => $_POST["secondary_font_custom"],
				"secondary_font" => $_POST["secondary_font"],
				"secondary_font_subset" => (isset($_POST["secondary_font_subset"]) ? $_POST["secondary_font_subset"] : ""),
				"send_email" => $_POST["send_email"],
				"send_email_client" => $_POST["send_email_client"],
				"save_calculation" => $_POST["save_calculation"],
				"calculation_status" => $_POST["calculation_status"],
				"google_recaptcha" => $_POST["google_recaptcha"],
				"recaptcha_site_key" => $_POST["recaptcha_site_key"],
				"recaptcha_secret_key" => $_POST["recaptcha_secret_key"]
			);
			update_option("cost_calculator_global_form_options", $cost_calculator_global_form_options);
			$message = __("Options saved!", "cost-calculator");
		}
		$cost_calculator_global_form_options = array(
			"calculator_skin" => '',
			"main_color" => '',
			"box_color" => '',
			"text_color" => '',
			"border_color" => '',
			"label_color" => '',
			"form_label_color" => '',
			"inactive_color" => '',
			"primary_font_custom" => '',
			"primary_font" => '',
			"primary_font_subset" => '',
			"secondary_font_custom" => '',
			"secondary_font" => '',
			"secondary_font_subset" => '',
			"send_email" => '',
			"send_email_client" => '',
			"save_calculation" => '',
			"calculation_status" => '',
			"google_recaptcha" => '',
			"recaptcha_site_key" => '',
			"recaptcha_secret_key" => ''
		);
		$cost_calculator_global_form_options = cost_calculator_stripslashes_deep(array_merge($cost_calculator_global_form_options, (array)get_option("cost_calculator_global_form_options")));
		
		require_once("admin/admin-page-global-config.php");
	}
	
	function cost_calculator_admin_page_import_dummy_data()
	{
		require_once("admin/admin-page-import-dummy-data.php");
	}
	require_once("post-type-calculations.php");	
}
function cost_calculator_ajax_save_shortcode()
{	
	$content = (!empty($_POST["cost_calculator_content"]) ? stripslashes($_POST["cost_calculator_content"]) : "");
	$shortcode_id = (!empty($_POST["cost_calculator_shortcode_id"]) ? $_POST["cost_calculator_shortcode_id"] : "");
	
	if($shortcode_id!=="" && $content!=="")
	{
		$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
		if($cost_calculator_shortcodes_list===false)
			$cost_calculator_shortcodes_list = array();
		$cost_calculator_shortcodes_list[$shortcode_id] = $content;
		ksort($cost_calculator_shortcodes_list);
		$advanced_settings = array(
			"calculator_skin" => $_POST["calculator_skin"],
			"main_color" => $_POST["main_color"],
			"box_color" => $_POST["box_color"],
			"text_color" => $_POST["text_color"],
			"border_color" => $_POST["border_color"],
			"label_color" => $_POST["label_color"],
			"form_label_color" => $_POST["form_label_color"],
			"inactive_color" => $_POST["inactive_color"],
			"primary_font_custom" => $_POST["primary_font_custom"],
			"primary_font" => $_POST["primary_font"],
			"primary_font_subset" => (isset($_POST["primary_font_subset"]) ? $_POST["primary_font_subset"] : ""),
			"secondary_font_custom" => $_POST["secondary_font_custom"],
			"secondary_font" => $_POST["secondary_font"],
			"secondary_font_subset" => (isset($_POST["secondary_font_subset"]) ? $_POST["secondary_font_subset"] : ""),
			"border_color" => $_POST["border_color"]
		);
		
		if($advanced_settings!="")
			update_option("cost_calculator_advanced_settings_" . $shortcode_id, $advanced_settings);
		if(update_option("cost_calculator_shortcodes_list", $cost_calculator_shortcodes_list))
		{
			echo "calculator_start" . $shortcode_id . "calculator_end";
		}
		else
			echo 0;		
	}
	exit();
}
add_action('wp_ajax_cost_calculator_save_shortcode', 'cost_calculator_ajax_save_shortcode');

function cost_calculator_ajax_delete_shortcode()
{
	if(!empty($_POST["cost_calculator_shortcode_id"]))
	{
		$shortcode_id = $_POST["cost_calculator_shortcode_id"];
		$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
		if($cost_calculator_shortcodes_list!==false && !empty($cost_calculator_shortcodes_list[$shortcode_id]))
		{
			unset($cost_calculator_shortcodes_list[$shortcode_id]);
			if(update_option("cost_calculator_shortcodes_list", $cost_calculator_shortcodes_list))
			{
				echo 1;
				exit();
			}
		}
	}
	echo 0;
	exit();
}
add_action('wp_ajax_cost_calculator_delete_shortcode', 'cost_calculator_ajax_delete_shortcode');

function cost_calculator_ajax_get_shortcode()
{
	if(!empty($_POST["cost_calculator_shortcode_id"]))
	{
		$shortcode_id = $_POST["cost_calculator_shortcode_id"];
		$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
		if($cost_calculator_shortcodes_list!==false && !empty($cost_calculator_shortcodes_list[$shortcode_id]))
		{
			$result = array();
			$result["content"] = html_entity_decode($cost_calculator_shortcodes_list[$shortcode_id]);
			//get advanced settings
			$result["advanced_settings"] = cost_calculator_stripslashes_deep(get_option("cost_calculator_advanced_settings_" . $shortcode_id));
			echo "calculator_start" . json_encode($result) . "calculator_end";
			exit();
		}
	}
	echo 0;
	exit();
}
add_action('wp_ajax_cost_calculator_get_shortcode', 'cost_calculator_ajax_get_shortcode');

//add new mimes for upload dummy content files (code can be removed after dummy content import)
function cost_calculator_custom_upload_files($mimes) 
{
	$mimes = array_merge($mimes, array('xml' => 'application/xml'), array('json' => 'application/json'));
	return $mimes;
}
add_filter('upload_mimes', 'cost_calculator_custom_upload_files');

function cost_calculator_download_import_file($file)
{	
	$url = "http://quanticalabs.com/wp_plugins/cost-calculator-for-wordpress/files/2018/03/" . $file["name"] . "." . $file["extension"];
	$attachment = get_page_by_title($file["name"], "OBJECT", "attachment");
	if($attachment!=null)
		$id = $attachment->ID;
	else
	{
		$tmp = download_url($url);
		$file_array = array(
			'name' => basename($url),
			'tmp_name' => $tmp
		);

		// Check for download errors
		if(is_wp_error($tmp)) 
		{
			@unlink($file_array['tmp_name']);
			return $tmp;
		}

		$id = media_handle_sideload($file_array, 0);
		// Check for handle sideload errors.
		if(is_wp_error($id))
		{
			@unlink($file_array['tmp_name']);
			return $id;
		}
	}
	return get_attached_file($id);
}

function cost_calculator_import_dummy()
{
	$result = array("info" => "");
	//import dummy content
	$fetch_attachments = true;
	$file = cost_calculator_download_import_file(array(
		"name" => "dummy-cost-calculator",
		"extension" => "xml"
	));
	if(!is_wp_error($file))
		require_once 'importer/importer.php';
	else
	{
		$result["info"] .= __("Import file: dummy-cost-calculator.xml not found! Please upload import file manually into Media library. You can find this file inside zip archive downloaded from CodeCanyon.", 'cost-calculator');
		exit();
	}
	//insert shortcodes from live preview
	$cost_calculator_shortcodes_live_preview = array(
		"carservice" => '[vc_row row-layout="columns_3_1-3_1-3_1-3" top_margin="page-margin-top" el_class=""][vc_column width="1/3"][cost_calculator_slider_box id="vehicle-year" name="vehicle-year" label="VEHICLE YEAR" default_value="2008" unit_value="1" step="1" min="1990" max="2018" top_margin="none" el_class=""][/vc_column][vc_column width="1/3"][cost_calculator_dropdown_box id="vehicle-make" name="vehicle-make" label="VEHICLE MAKE" default_value="" options_count="15" option_name0="General Motors" option_value0="General Motors" option_name1="Land Rover" option_value1="Land Rover" option_name2="Lexus" option_value2="Lexus" option_name3="Lincoln" option_value3="Lincoln" option_name4="Mazda" option_value4="Mazda" option_name5="Mercedes - Benz" option_value5="Mercedes - Benz" option_name6="Mercury" option_value6="Mercury" option_name7="Mitsubishi" option_value7="Mitsubishi" option_name8="Nissan" option_value8="Nissan" option_name9="Renault" option_value9="Renault" option_name10="Plymouth" option_value10="Plymouth" option_name11="Pontiac Porsche" option_value11="Pontiac Porsche" option_name12="Rover" option_value12="Rover" option_name13="Saab" option_value13="Saab" option_name14="Saleen" option_value14="Saleen" show_choose_label="1" choose_label="Choose..." top_margin="none"][/vc_column][vc_column width="1/3"][cost_calculator_input_box id="vehicle-mileage" name="vehicle-mileage" label="VEHICLE MILEAGE" default_value="" type="number" checked="1" checkbox_type="button" placeholder="Vehicle Mileage" top_margin="none" el_class=""][/vc_column][/vc_row][vc_row row-layout="columns_2_1-2_1-2" top_margin="page-margin-top" el_class=""][vc_column width="1/2"][cost_calculator_input_box id="appointment-date" name="appointment-date" label="APPOINTMENT DATE" default_value="" type="date" checked="1" checkbox_type="button" placeholder="Preffered Date of Appointment" top_margin="none" el_class=""]
[cost_calculator_dropdown_box id="time-frame" name="time-frame" label="PREFFERED TIME FRAME" default_value="" options_count="9" option_name0="09:00 AM - 10:00 AM" option_value0="09:00 AM - 10:00 AM" option_name1="10:00 AM - 11:00 AM" option_value1="10:00 AM - 11:00 AM" option_name2="11:00 AM - 12:00 PM" option_value2="11:00 AM - 12:00 PM" option_name3="12:00 PM - 01:00 PM" option_value3="12:00 PM - 01:00 PM" option_name4="01:00 PM - 02:00 PM" option_value4="01:00 PM - 02:00 PM" option_name5="02:00 PM - 03:00 PM" option_value5="02:00 PM - 03:00 PM" option_name6="03:00 PM - 04:00 PM" option_value6="03:00 PM - 04:00 PM" option_name7="04:00 PM - 05:00 PM" option_value7="04:00 PM - 05:00 PM" option_name8="05:00 PM - 06:00 PM" option_value8="05:00 PM - 06:00 PM" show_choose_label="1" choose_label="Choose..." top_margin="page-margin-top"]<div class="page-margin-top margin-bottom-20 cost-calculator-box"><label>SELECT SERVICES NEEDED</label></div>[cost_calculator_input_box id="air-conditioning" name="air-conditioning" label="Air Conditioning" default_value="1" type="checkbox" checked="0" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="brakes-repair" name="brakes-repair" label="Brakes Repair" default_value="1" type="checkbox" checked="1" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""][cost_calculator_input_box id="engine-diagnostics" name="engine-diagnostics" label="Engine Diagnostics" default_value="1" type="checkbox" checked="0" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="heating-cooling" name="heating-cooling" label="Heating&amp;Cooling" default_value="1" type="checkbox" checked="1" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="oil-lube-filters" name="oil-lube-filters" label="Oil, Lube &amp; Filters" default_value="1" type="checkbox" checked="1" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="steering-suspension" name="steering-suspension" label="Steering&amp;Suspension" default_value="1" type="checkbox" checked="0" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="transmission-repair" name="transmission-repair" label="Transmission Repair" default_value="1" type="checkbox" checked="0" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""][cost_calculator_input_box id="wheel-alignment" name="wheel-alignment" label="Wheel Alignment" default_value="1" type="checkbox" checked="0" checkbox_type="type-button" placeholder="" top_margin="none" el_class=""][/vc_column][vc_column width="1/2"][cost_calculator_contact_box label="CONTACT DETAILS" submit_label="SUBMIT NOW" name_label="Your Name *" name_required="1" email_label="Your Email *" email_required="1" phone_label="Your Phone" phone_required="0" message_label="Additional Questions or Comments" message_required="0" type="" labels_style="placeholder" description="" el_class=""][/vc_column][/vc_row]',
		"cleanmate" => '[vc_row row-layout="columns_2_1-2_1-2" top_margin="page-margin-top" el_class=""][vc_column width="1/2"][cost_calculator_slider_box id="clean-area" name="clean-area" label="Total area to be cleaned in square feet:" default_value="1200" unit_value="1" step="1" min="1" max="2000" top_margin="none" el_class=""]
[cost_calculator_slider_box id="bathrooms" name="bathrooms" label="Number of bathrooms:" default_value="2" unit_value="1" step="1" min="0" max="5" top_margin="none" el_class="margin-top-30"][/vc_column][vc_column width="1/2"][cost_calculator_slider_box id="bedrooms" name="bedrooms" label="Number of bedrooms:" default_value="4" unit_value="1" step="1" min="0" max="8" top_margin="none" el_class=""]
[cost_calculator_slider_box id="livingrooms" name="livingrooms" label="Number of living rooms:" default_value="1" unit_value="1" step="1" min="0" max="3" top_margin="none" el_class="margin-top-30"][/vc_column][/vc_row][vc_row row-layout="columns_3_1-3_1-3_1-3" top_margin="none" el_class="margin-top-30"][vc_column width="1/3"][cost_calculator_dropdown_box id="kitchen-size" name="kitchen-size" label="Size of your kitchen:" default_value="" options_count="3" option_name0="Small (0 - 150 ft2)" option_value0="15" option_name1="Medium (151 - 250 ft2)" option_value1="20" option_name2="Large (&gt;250 ft2)" option_value2="25" show_choose_label="1" choose_label="Choose..." top_margin="none"][/vc_column][vc_column width="1/3"][cost_calculator_dropdown_box id="bathroom-includes" name="bathroom-includes" label="Master bathroom includes:" default_value="" options_count="4" option_name0="Shower only" option_value0="10" option_name1="Tub only" option_value1="13" option_name2="Separete shower and tub" option_value2="15" option_name3="No appliances" option_value3="0" show_choose_label="1" choose_label="Choose..." top_margin="none"][/vc_column][vc_column width="1/3"][cost_calculator_switch_box id="pets" name="pets" label="Do you have pets?" default_value="30" checked="0" top_margin="none" el_class=""][/vc_column][/vc_row][vc_row row-layout="columns_1_1-1" top_margin="page-margin-top" el_class=""][vc_column width="1/1"]<h3 class="cost-calculator-align-center no-border">Select your service &amp; extras</h3>[/vc_column][/vc_row][vc_row row-layout="columns_2_1-2_1-2" top_margin="none" el_class="margin-top-40"][vc_column width="1/2"][cost_calculator_dropdown_box id="cleaning-supplies" name="cleaning-supplies" label="Choose your cleaning supplies:" default_value="500" options_count="3" option_name0="Green cleaning" option_value0="500" option_name1="Company\'s supplies" option_value1="300" option_name2="Client\'s supplies" option_value2="0" show_choose_label="1" choose_label="Choose..." top_margin="none"][/vc_column][vc_column width="1/2"][cost_calculator_dropdown_box id="cleaning-frequency" name="cleaning-frequency" label="Cleaning frequency:" default_value="0.2" options_count="6" option_name0="Weekly Service" option_value0="0.4" option_name1="Bi-Weekly Service" option_value1="0.8" option_name2="Tri-Weekly Service" option_value2="1.2" option_name3="Quarter Weekly Service" option_value3="1.6" option_name4="Monthly Service" option_value4="0.1" option_name5="One Time Service" option_value5="0.2" show_choose_label="1" choose_label="Choose..." top_margin="none"][/vc_column][/vc_row][vc_row row-layout="columns_2_2-3_1-3" top_margin="none" el_class="margin-top-30"][vc_column width="2/3"]<div class="cost-calculator-box cost-calculator-clearfix"><div class="margin-bottom-6"><label>Additional rooms you would like us to clean:</label></div>[cost_calculator_input_box id="dining-room" name="dining-room" label="Dining room" default_value="10" type="checkbox" checked="0" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="play-room" name="play-room" label="Play room" default_value="15" type="checkbox" checked="0" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="laundry" name="laundry" label="Laundry" default_value="14" type="checkbox" checked="0" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="gym" name="gym" label="Gym" default_value="17" type="checkbox" checked="0" placeholder="" top_margin="none" el_class=""]
[cost_calculator_input_box id="garage" name="garage" label="Garage" default_value="20" type="checkbox" checked="0" placeholder="" top_margin="none" el_class=""]</div>[/vc_column][vc_column width="1/3"][cost_calculator_switch_box id="refrigerator-clean" name="refrigerator-clean" label="Clean inside the refrigerator?" default_value="20" checked="0" top_margin="none" el_class=""][/vc_column][/vc_row][vc_row row-layout="columns_1_1-1" top_margin="page-margin-top" el_class="cost-calculator-align-center"][vc_column width="1/1"]<h3>Final cost</h3>[cost_calculator_summary_box id="cost" name="total_cost" formula="cleaning-frequency*clean-area+cleaning-frequency*cleaning-supplies+cleaning-frequency*bedrooms*20+cleaning-frequency*bathrooms*20+cleaning-frequency*livingrooms*30+cleaning-frequency*kitchen-size+cleaning-frequency*bathroom-includes+cleaning-frequency*pets+cleaning-frequency*dining-room+cleaning-frequency*play-room+cleaning-frequency*laundry+cleaning-frequency*gym+cleaning-frequency*garage+cleaning-frequency*refrigerator-clean" currency="$" currency_size="default" currency_position="before" thousandth_separator="," decimal_separator="." description="" icon="" el_class="margin-top-15 cost-calculator-after-border cost-calculator-transparent"]<p>Enter your contact details. We will give you a call to finish up.</p>[/vc_column][/vc_row][vc_row row-layout="columns_1_1-1" top_margin="none" el_class="margin-top-10"][vc_column width="1/1"][cost_calculator_contact_box submit_label="Submit now" name_label="YOUR NAME" name_required="1" email_label="YOUR EMAIL" email_required="1" phone_label="YOUR PHONE" phone_required="0" message_label="QUESTIONS OR COMMENTS" message_required="0" labels_style="default" type="" description="" el_class="cost-calculator-gray"][/vc_column][/vc_row]',
		"renovate" => '[vc_row row-layout="columns_2_2-3_1-3" top_margin="page-margin-top" el_class=""][vc_column width="2/3"][cost_calculator_slider_box id="ir-square-feet" name="square-feet" label="Area to be Renovated in Square Feet:" default_value="300" unit_value="2" step="10" min="10" max="3000" top_margin="none" el_class=""]
[cost_calculator_dropdown_box id="ir-walls" name="walls" label="Walls &amp; Ceilings:" default_value="" options_count="6" option_name0="Painting" option_value0="2" option_name1="Painting + Minor Repairs" option_value1="2.3" option_name2="Painting + Decorative Stone" option_value2="2.5" option_name3="Tiling" option_value3="3" option_name4="Painting + Tiling" option_value4="5" option_name5="Hanging Lining Paper" option_value5="2" show_choose_label="1" choose_label="Choose..." top_margin="none"]
[cost_calculator_dropdown_box id="ir-floors" name="floors" label="Floors:" default_value="" options_count="6" option_name0="Hardwood Flooring" option_value0="1.5" option_name1="Bamboo Flooring" option_value1="2.5" option_name2="Vinyl Tile Flooring" option_value2="2.6" option_name3="Parquet Flooring" option_value3="3.25" option_name4="Wall-to-wall Carpet" option_value4="3.5" option_name5="Ceramic Tile Flooring" option_value5="12" show_choose_label="1" choose_label="Choose..." top_margin="none"]
[cost_calculator_slider_box id="ir-doors" name="doors" label="Interior Doors to Replace:" default_value="6" unit_value="250" step="1" min="0" max="10" top_margin="none" el_class=""]
[cost_calculator_slider_box id="ir-windows" name="windows" label="Windows to Replace:" default_value="4" unit_value="200" step="1" min="0" max="10" top_margin="none" el_class=""]
[cost_calculator_input_box id="ir-cleaning" name="ir-cleaning" label="After Renovation Cleaning" default_value="50" type="checkbox" checked="1" checkbox_type="default" placeholder="" top_margin="none" el_class=""]
[cost_calculator_summary_box id="interior-renovation-cost" name="total_cost" formula="ir-square-feet-value*ir-walls+ir-square-feet*ir-floors+ir-doors-value+ir-windows-value+ir-cleaning" currency="£" currency_size="default" currency_position="before" thousandth_separator="," decimal_separator="." description="Approximate Project Cost" icon="cc-template-wallet"]
[cost_calculator_contact_box label="Contact Details" submit_label="SUBMIT NOW" name_label="Your Name *" name_required="1" email_label="Your Email *" email_required="1" phone_label="Your Phone" phone_required="0" message_label="Message *" message_required="1" type="Interior Renovation" labels_style="placeholder" description="We will contact you within one business day." el_class="cost-calculator-box margin-top-10"][/vc_column][vc_column width="1/3"]<img class="alignnone size-full wp-image-79" src="http://quanticalabs.com/wp_plugins/cost-calculator-for-wordpress/files/2018/03/image_03.jpg" alt="" width="480" height="320"><img class="margin-top-30 alignnone size-full wp-image-79" src="http://quanticalabs.com/wp_plugins/cost-calculator-for-wordpress/files/2018/03/image_01.jpg" alt="" width="480" height="320"><img class="margin-top-30 alignnone size-full wp-image-79" src="http://quanticalabs.com/wp_plugins/cost-calculator-for-wordpress/files/2018/03/image_05.jpg" alt="" width="480" height="320"><img class="margin-top-30 alignnone size-full wp-image-79" src="http://quanticalabs.com/wp_plugins/cost-calculator-for-wordpress/files/2018/03/image_07.jpg" alt="" width="480" height="320">[/vc_column][/vc_row]',
		"lpg-calculator" => '[vc_row row-layout="columns_2_1-2_1-2" top_margin="page-margin-top" el_class=""][vc_column width="1/2"][cost_calculator_slider_box id="annual-distance" name="annual-distance" label="Annual distance travelled" default_value="10000" unit_value="1" step="500" min="1000" max="100000" input_field="1" top_margin="none" el_class=""][/vc_column][vc_column width="1/2"][cost_calculator_slider_box id="gasoline-consumption" name="gasoline-consumption" label="Gasoline consumption" default_value="6" unit_value="1" step="1" min="3" max="30" input_field="1" top_margin="none" el_class=""][/vc_column][/vc_row]
[vc_row row-layout="columns_2_1-2_1-2" top_margin="none" el_class="margin-top-30"][vc_column width="1/2"][cost_calculator_slider_box id="gasoline-price" name="gasoline-price" label="Gasoline price (per liter)" default_value="0.86" unit_value="1" step="0.01" min="0.5" max="1.5" input_field="1" top_margin="none" el_class=""][/vc_column][vc_column width="1/2"][cost_calculator_slider_box id="lpg-price" name="lpg-price" label="LPG price (per liter)" default_value="0.53" unit_value="1" step="0.01" min="0.3" max="1.5" input_field="1" top_margin="none" el_class=""][/vc_column][/vc_row]
[vc_row row-layout="columns_1_1-1" top_margin="none" el_class="margin-top-30"][vc_column width="1/1"][cost_calculator_dropdown_box id="fuel-consumption-increase" name="fuel-consumption-increase" label="Estimated fuel consumption increase with LPG installation" default_value="20" options_count="5" option_name0="10%" option_value0="10" option_name1="15%" option_value1="15" option_name2="20%" option_value2="20" option_name3="25%" option_value3="25" option_name4="30%" option_value4="30" show_choose_label="0" choose_label="Choose..." top_margin="none"][/vc_column][/vc_row]
[vc_row row-layout="columns_3_1-3_1-3_1-3" top_margin="none" el_class="margin-top-30"][vc_column width="1/3"][cost_calculator_summary_box id="gasoline-cost" name="gasoline-cost" formula="annual-distance/100*gasoline-consumption*gasoline-price" currency="$" currency_size="default" currency_position="before" thousandth_separator="," decimal_separator="." description="Annual gasoline cost" icon="" el_class="cost-calculator-align-center"][/vc_column][vc_column width="1/3"][cost_calculator_summary_box id="lpg-cost" name="lpg-cost" formula="annual-distance/100*gasoline-consumption*(100+fuel-consumption-increase)/100*lpg-price" currency="$" currency_size="default" currency_position="before" thousandth_separator="," decimal_separator="." description="Annual LPG cost" icon="" el_class="cost-calculator-align-center"][/vc_column][vc_column width="1/3"][cost_calculator_summary_box id="fuel-savings" name="fuel-savings" formula="gasoline-cost-total-value{-}lpg-cost-total-value" currency="$" currency_size="default" currency_position="before" thousandth_separator="," decimal_separator="." description="Annual savings" icon="" el_class="cost-calculator-align-center"][/vc_column][/vc_row][vc_row row-layout="columns_1_1-1" top_margin="none" el_class="margin-top-30"][vc_column width="1/1"][cost_calculator_summary_box id="fuel-percentage-savings" name="fuel-percentage-savings" formula="(gasoline-cost-total-value{-}lpg-cost-total-value)/gasoline-cost-total-value*100" currency="%" currency_size="default" currency_position="after" thousandth_separator="," decimal_separator="." description="Annual savings %" icon="" el_class="cost-calculator-align-center"][/vc_column][/vc_row]'
	);
	$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
	if($cost_calculator_shortcodes_list===false)
		$cost_calculator_shortcodes_list = array();
	foreach($cost_calculator_shortcodes_live_preview as $key=>$val)
	{
		if(!array_key_exists($key, $cost_calculator_shortcodes_list))
			$cost_calculator_shortcodes_list[$key] = $val;
	}
	ksort($cost_calculator_shortcodes_list);
	update_option("cost_calculator_shortcodes_list", $cost_calculator_shortcodes_list);
	
	update_option("cost_calculator_advanced_settings_carservice", array(
		"calculator_skin" => "carservice",
		"main_color" => "1E69B8",
		"box_color" => "",
		"text_color" => "777777",
		"border_color" => "E2E6E7",
		"label_color" => "333333",
		"form_label_color" => "",
		"inactive_color" => "E2E6E7",
		"primary_font_custom" => "",
		"primary_font" => "Open Sans:regular",
		"primary_font_subset" => array("latin", "latin-ext"),
		"secondary_font_custom" => "",
		"secondary_font" => "",
		"secondary_font_subset" => "" 
	));
	
	update_option("cost_calculator_advanced_settings_cleanmate", array(
		"calculator_skin" => "default",
		"main_color" => "",
		"box_color" => "FFFFFF",
		"text_color" => "",
		"border_color" => "",
		"label_color" => "",
		"form_label_color" => "",
		"inactive_color" => "",
		"primary_font_custom" => "",
		"primary_font" => "",
		"primary_font_subset" => "",
		"secondary_font_custom" => "",
		"secondary_font" => "",
		"secondary_font_subset" => "" 
	));
	
	update_option("cost_calculator_advanced_settings_renovate", array(
		"calculator_skin" => "renovate",
		"main_color" => "F4BC16",
		"box_color" => "F5F5F5",
		"text_color" => "444444",
		"border_color" => "E2E6E7",
		"label_color" => "25282A",
		"form_label_color" => "",
		"inactive_color" => "E2E6E7",
		"primary_font_custom" => "",
		"primary_font" => "",
		"primary_font_subset" => "",
		"secondary_font_custom" => "",
		"secondary_font" => "Raleway:300",
		"secondary_font_subset" => array("latin", "latin-ext")
	));
	
	update_option("cost_calculator_advanced_settings_lpg-calculator", array(
		"calculator_skin" => "default",
		"main_color" => "",
		"box_color" => "FFFFFF",
		"text_color" => "",
		"border_color" => "",
		"label_color" => "",
		"form_label_color" => "",
		"inactive_color" => "",
		"primary_font_custom" => "",
		"primary_font" => "",
		"primary_font_subset" => "",
		"secondary_font_custom" => "",
		"secondary_font" => "",
		"secondary_font_subset" => "" 
	));
	
	if($result["info"]=="")
		$result["info"] = __("Dummy content has been imported successfully!", 'cost-calculator');
	echo "dummy_import_start" . json_encode($result) . "dummy_import_end";
	exit();
}
add_action('wp_ajax_cost_calculator_import_dummy', 'cost_calculator_import_dummy');

function cost_calculator($atts)
{
	$output = "";
	if(!empty($atts["id"]))
	{
		$id = $atts["id"];
		$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
		if($cost_calculator_shortcodes_list!==false && !empty($cost_calculator_shortcodes_list[$id]))
		{
			$shortcode = html_entity_decode($cost_calculator_shortcodes_list[$id]);
			wp_register_style("cost_calculator_inline_style", false);
			wp_enqueue_style("cost_calculator_inline_style");
			$advanced_settings = cost_calculator_stripslashes_deep(get_option("cost_calculator_advanced_settings_" . $id));
			if($advanced_settings["primary_font"]!="" && $advanced_settings["primary_font_custom"]=="")
				wp_enqueue_style("cc-google-font-primary-" . $id, "//fonts.googleapis.com/css?family=" . urlencode($advanced_settings["primary_font"]) . (!empty($advanced_settings["primary_font_subset"]) ? "&subset=" . implode(",", $advanced_settings["primary_font_subset"]) : ""));
			if($advanced_settings["secondary_font"]!="" && $advanced_settings["secondary_font_custom"]=="")
				wp_enqueue_style("cc-google-font-secondary-" . $id, "//fonts.googleapis.com/css?family=" . urlencode($advanced_settings["secondary_font"]) . (!empty($advanced_settings["secondary_font_subset"]) ? "&subset=" . implode(",", $advanced_settings["secondary_font_subset"]) : ""));
			ob_start();
			require("custom_colors.php");
			$custom_colors_css = ob_get_clean();
			wp_add_inline_style("cost_calculator_inline_style", $custom_colors_css);
			$output = '<form id="' . esc_attr($id) . '" action="#" method="post" class="cost-calculator-container cost-calculator-form">' . do_shortcode($shortcode) . '</form>';
		}
		else
			$output = __("Cost Calculator with given id doesn't exists.", 'cost-calculator');
	}
	else
		$output = __("Id parameter missing in the [cost_calculator] shortcode.", 'cost-calculator');
	return $output;
}
add_shortcode("cost_calculator", "cost_calculator");

//shortcodes
function cost_calculator_init()
{
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	$active_sitewide_plugins = get_site_option('active_sitewide_plugins');
	$js_composer_path_array = array_merge(preg_grep("/js_composer/", (array)get_option('active_plugins')), preg_grep("/js_composer/", (is_array($active_sitewide_plugins) ? array_flip($active_sitewide_plugins) : array())));
	$js_composer_path = (count($js_composer_path_array) ? $js_composer_path_array[0] : "js_composer/js_composer.php");
	require_once("shortcodes/cost_calculator_row.php");
	require_once("shortcodes/cost_calculator_column.php");
	require_once("shortcodes/cost_calculator_dropdown_box.php");
	require_once("shortcodes/cost_calculator_slider_box.php");
	require_once("shortcodes/cost_calculator_input_box.php");
	require_once("shortcodes/cost_calculator_switch_box.php");
	require_once("shortcodes/cost_calculator_summary_box.php");
	require_once("shortcodes/cost_calculator_contact_box.php");
	
	if(is_plugin_active($js_composer_path) && function_exists('vc_map'))
	{
		global $wpdb;
		$cost_calculator_shortcodes_list = get_option("cost_calculator_shortcodes_list");
		$cost_calculator_shortcodes_array = array(__("choose...", "cost-calculator") => "-1");
		if(!empty($cost_calculator_shortcodes_list))
		{
			foreach($cost_calculator_shortcodes_list as $key=>$val)
				$cost_calculator_shortcodes_array[$key] = $key;
		}
		
		vc_map( array(
			"name" => __("Cost Calculator", 'cost-calculator'),
			"base" => "cost_calculator",
			"class" => "",
			"controls" => "full",
			"show_settings_on_create" => true,
			"icon" => "icon-wpb-layer-cost-calculator",
			"category" => __('Cost Calculator', 'cost-calculator'),
			"params" => array(
				array(
					"type" => "dropdown",
					"class" => "",
					"heading" => __("Id", 'css3_grid'),
					"param_name" => "id",
					"value" => $cost_calculator_shortcodes_array
				)
			)
		));
		if(function_exists("vc_shortcodes_theme_templates_dir") && vc_shortcodes_theme_templates_dir("vc_row.php")=="" && vc_shortcodes_theme_templates_dir("vc_row_inner.php")=="" && vc_shortcodes_theme_templates_dir("vc_column.php")=="" && vc_shortcodes_theme_templates_dir("vc_column_inner.php")=="")
		{
			vc_set_shortcodes_templates_dir(plugin_dir_path(__FILE__) . 'vc_templates');
		}
	}
}
add_action("init", "cost_calculator_init"); 

function cost_calculator_stripslashes_deep($value)
{
	$value = is_array($value) ?
				array_map('stripslashes_deep', $value) :
				stripslashes($value);
	
	return $value;
}
//get_font_subsets
function cc_ajax_get_font_subsets()
{
	if($_POST["font"]!="")
	{
		$subsets = '';
		$fontExplode = explode(":", $_POST["font"]);
		$subsets_array = cc_get_google_font_subset($fontExplode[0]);
		
		foreach($subsets_array as $subset)
			$subsets .= '<option value="' . esc_attr($subset) . '">' . $subset . '</option>';
		
		echo "cc_start" . $subsets . "cc_end";
	}
	exit();
}
add_action('wp_ajax_cc_get_font_subsets', 'cc_ajax_get_font_subsets');

/**
 * Returns array of Google Fonts
 * @return array of Google Fonts
 */
function cc_get_google_fonts()
{
	//get google fonts
	$fontsArray = get_option("cc_google_fonts");
	//update if option doesn't exist or it was modified more than 2 weeks ago
	if($fontsArray===FALSE || count($fontsArray)==0 || (time()-$fontsArray->last_update>2*7*24*60*60)) 
	{
		$google_api_url = 'http://quanticalabs.com/.tools/GoogleFont/font.txt';
		$fontsJson = wp_remote_retrieve_body(wp_remote_get($google_api_url, array('sslverify' => false)));
		$fontsArray = json_decode($fontsJson);
		$fontsArray->last_update = time();		
		update_option("cc_google_fonts", $fontsArray);
	}
	return $fontsArray;
}

/**
 * Returns array of subsets for provided Google Font
 * @param type $font - Google font
 * @return array of subsets for provided Google Font
 */
function cc_get_google_font_subset($font)
{
	$subsets = array();
	//get google fonts
	$fontsArray = cc_get_google_fonts();		
	$fontsCount = count($fontsArray->items);
	for($i=0; $i<$fontsCount; $i++)
	{
		if($fontsArray->items[$i]->family==$font)
		{
			for($j=0, $max=count($fontsArray->items[$i]->subsets); $j<$max; $j++)
			{
				$subsets[] = $fontsArray->items[$i]->subsets[$j];
			}
			break;
		}
	}
	return $subsets;
}
?>
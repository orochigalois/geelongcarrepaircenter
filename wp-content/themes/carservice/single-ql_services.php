<?php
/*
Template Name: Service
*/
get_header();
?>
<div class="theme-page padding-bottom-70">
	<div class="vc_row wpb_row vc_row-fluid gray full-width page-header vertical-align-table">
		<div class="vc_row wpb_row vc_inner vc_row-fluid">
			<div class="page-header-left">
				<h1><?php the_title(); ?></h1>
			</div>
			<div class="page-header-right">
				<div class="bread-crumb-container">
					<label><?php _e("YOU ARE HERE:", 'carservice'); ?></label>
					<ul class="bread-crumb">
						<li>
							<a href="<?php echo esc_url(get_home_url()); ?>" title="<?php esc_attr_e('Home', 'carservice'); ?>">
								<?php _e('HOME', 'carservice'); ?>
							</a>
						</li>
						<li class="separator">
							&#47;
						</li>
						<li>
							<?php the_title(); ?>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix">
		<?php
		/*get page with single post template set*/
		$post_template_page_array = get_pages(array(
			'post_type' => 'page',
			'post_status' => 'publish',
			//'number' => 1,
			'meta_key' => '_wp_page_template',
			'meta_value' => 'single-ql_services.php'
		));
		if(count($post_template_page_array))
		{
			$post_template_page_array = array_values($post_template_page_array);
			$post_template_page = $post_template_page_array[0];
			if(count($post_template_page_array) && isset($post_template_page))
			{
				echo wpb_js_remove_wpautop(apply_filters('the_content', $post_template_page->post_content));
				global $post;
				$post = $post_template_page;
				setup_postdata($post);
			}
			else
				echo wpb_js_remove_wpautop(apply_filters('the_content', '[vc_row el_class="margin-top-70"][vc_column width="1/4"][vc_wp_custommenu nav_menu="27" el_class="vertical-menu"][call_to_action_box title="ONLINE APPOINTMENT" icon="percent" button_label="MAKE APPOINTMENT" button_url="#" top_margin="page-margin-top" text=""]Book your appointment now and get $5 discount.[/call_to_action_box][box_header title="DOWNLOAD" type="h6" bottom_border="1" top_margin="page-margin-top"][vc_btn type="action" icon="arrow-circle-down" title="Download Brochure" url="#" extraclass="margin-top-30"][vc_btn type="action" icon="arrow-circle-down" title="Download Summary" url="#"][/vc_column][vc_column width="3/4"][single_service show_social_icons="1" show_twitter="1" show_facebook="1" show_linkedin="1" show_skype="1" show_googleplus="1" show_instagram="1"][/vc_column][/vc_row]'));
		}
		else
		{
			if(function_exists("vc_map"))
				echo wpb_js_remove_wpautop(apply_filters('the_content', '[vc_row el_class="margin-top-70"][vc_column width="1/4"][vc_wp_custommenu nav_menu="27" el_class="vertical-menu"][call_to_action_box title="ONLINE APPOINTMENT" icon="percent" button_label="MAKE APPOINTMENT" button_url="#" top_margin="page-margin-top" text=""]Book your appointment now and get $5 discount.[/call_to_action_box][box_header title="DOWNLOAD" type="h6" bottom_border="1" top_margin="page-margin-top"][vc_btn type="action" icon="arrow-circle-down" title="Download Brochure" url="#" extraclass="margin-top-30"][vc_btn type="action" icon="arrow-circle-down" title="Download Summary" url="#"][/vc_column][vc_column width="3/4"][single_service show_social_icons="1" show_twitter="1" show_facebook="1" show_linkedin="1" show_skype="1" show_googleplus="1" show_instagram="1"][/vc_column][/vc_row]'));
		}
		?>
	</div>
</div>
<?php
get_footer();
?>
<?php
get_header();
global $post;
$post = get_post(get_option("woocommerce_shop_page_id"));
setup_postdata($post);
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
		<div class="vc_row wpb_row vc_row-fluid margin-top-70">
			<div class="vc_col-sm-9 wpb_column vc_column_container">
				<div class="wpb_wrapper">
					<?php
					if(have_posts()):
						woocommerce_content();
					endif;
					?>
				</div> 
			</div>
			<div class="vc_col-sm-3 wpb_column vc_column_container">
				<div class="wpb_wrapper">
					<div class="wpb_widgetised_column wpb_content_element clearfix">
						<div class="wpb_wrapper">
							<?php dynamic_sidebar("sidebar-shop"); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
global $post;
$post = get_post(get_option("woocommerce_shop_page_id"));
setup_postdata($post);
get_footer(); 
?>
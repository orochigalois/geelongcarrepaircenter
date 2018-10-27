<?php
class cs_list_widget extends WP_Widget 
{
	/** constructor */
	function __construct()
	{
		$widget_options = array(
			'classname' => 'cs_list_widget',
			'description' => 'Displays Bulleted List'
		);
		$control_options = array('width' => 625);
        parent::__construct('carservice_list', __('Bulleted List', 'carservice'), $widget_options, $control_options);
    }
	
	/** @see WP_Widget::widget */
    function widget($args, $instance) 
	{
        extract($args);

		//these are our widget options
		$title = $instance['title'];
		$list_item = $instance["list_item"];
		$list_item_url = $instance["list_item_url"];
		$list_item_target = $instance["list_item_target"];

		echo $before_widget;
		if(isset($title) && $title!="") 
		{
			echo $before_title . $title . $after_title;
		} 
		$arrayEmpty = true;
		for($i=0; $i<count($list_item); $i++)
		{
			if($list_item[$i]!="")
				$arrayEmpty = false;
		}
		if(!$arrayEmpty):
		?>
		<ul class="list margin-top-20">
			<?php
			for($i=0; $i<count($list_item); $i++)
			{
				if($list_item[$i]!=""):
				?>
				<li class="template-bullet"><?php if($list_item_url[$i]!=""): ?><a <?php echo ($list_item_target[$i]=="new_window" ? " target='_blank' " : ""); ?>href="<?php echo esc_url($list_item_url[$i]);?>"><?php endif; echo $list_item[$i];if($list_item_url[$i]!=""): ?></a><?php	endif;?></li>
				<?php
				endif;
			}
			?>
		</ul>
		<?php
		endif;
        echo $after_widget;
    }
	
	/** @see WP_Widget::update */
    function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_item'] = array_values(array_filter((array)$new_instance['list_item']));
		$instance['list_item_url'] = $new_instance['list_item_url'];
		$instance['list_item_target'] = $new_instance['list_item_target'];
		return $instance;
    }
	
	 /** @see WP_Widget::form */
	function form($instance) 
	{	
		if(!isset($instance["list_item"])):
		?>
			<input type="hidden" id="widget-list-button_id" value="<?php echo esc_attr($this->get_field_id('add_new_button')); ?>">
		<?php
		endif;
		$title = esc_attr(isset($instance['title']) ? $instance['title'] : '');
		$list_item = (isset($instance["list_item"]) ? $instance["list_item"] : '');
		$list_item_url = (isset($instance["list_item_url"]) ? $instance["list_item_url"] : '');
		$list_item_target = (isset($instance["list_item_target"]) ? $instance["list_item_target"] : '');
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'carservice'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php
		for($i=0; $i<(count($list_item)<4 ? 4 : count($list_item)); $i++)
		{
		?>
		<p class="widget-border">
			<label for="<?php echo esc_attr($this->get_field_id('list_item')) . absint($i); ?>"><?php _e('List item text', 'carservice'); ?></label>
			<input style="width: 220px;" type="text" class="regular-text" value="<?php echo (isset($list_item[$i]) ? esc_attr($list_item[$i]) : ''); ?>" name="<?php echo esc_attr($this->get_field_name('list_item')); ?>[]">
			<label for="<?php echo esc_attr($this->get_field_id('list_item_url')) . absint($i); ?>"><?php _e('List item url', 'carservice'); ?></label>
			<input style="width: 220px;" type="text" class="regular-text" value="<?php echo (isset($list_item_url[$i]) ? esc_attr($list_item_url[$i]) : ''); ?>" name="<?php echo esc_attr($this->get_field_name('list_item_url')); ?>[]">
			<label for="<?php echo esc_attr($this->get_field_id('list_item_target')) . absint($i); ?>"><?php _e('List item url target', 'carservice'); ?></label>
			<select name="<?php echo esc_attr($this->get_field_name('list_item_target')); ?>[]">
				<option value="same_window"<?php echo (isset($list_item_target[$i]) && $list_item_target[$i]=="same_window" ? " selected='selected'" : ""); ?>><?php _e('same window', 'carservice'); ?></option>
				<option value="new_window"<?php echo (isset($list_item_target[$i]) && $list_item_target[$i]=="new_window" ? " selected='selected'" : ""); ?>><?php _e('new window', 'carservice'); ?></option>
			</select>
		</p>
		<?php
		}
		?>
		<p>
			<input type="button" class="button" name="<?php echo esc_attr($this->get_field_name('add_new_button')); ?>" id="<?php echo esc_attr($this->get_field_id('add_new_button')); ?>" value="<?php esc_attr_e('Add item', 'carservice'); ?>" />
		</p>
		
		<script type="text/javascript">
		jQuery(document).ajaxStop(function(){
			var selector = "#<?php echo esc_attr($this->get_field_id('add_new_button')); ?>";
			if(jQuery(".widgets-holder-wrap #widget-list-button_id").length)
			{
				selector = "#" + jQuery(jQuery(".widgets-holder-wrap #widget-list-button_id")[1]).val();
				jQuery(".widgets-holder-wrap #widget-list-button_id").remove();
			}
			jQuery(selector).off("click");
			jQuery(selector).on("click", function(){
				jQuery(this).parent().before(jQuery(this).parent().prev().clone().wrap('<div>').parent().html());
				jQuery(this).parent().prev().find("input").val('');
				jQuery(this).parent().prev().find("select").each(function(){
					jQuery(this).val(jQuery(this).children("option:first").val());
				});
			});
		});
		jQuery(document).ready(function($){
			$("#<?php echo esc_attr($this->get_field_id('add_new_button')); ?>").on("click", function(){
				$(this).parent().before($(this).parent().prev().clone().wrap('<div>').parent().html());
				$(this).parent().prev().find("input").val('');
				$(this).parent().prev().find("select").each(function(){
					$(this).val($(this).children("option:first").val());
				});
			});
		});
		</script>
		<?php
	}
}
//register widget
function cs_list_widget_init()
{
	return register_widget("cs_list_widget");
}
add_action('widgets_init', 'cs_list_widget_init');
?>
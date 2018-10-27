
    $(document).ready(function() 
    {
        $('.tabs').tabs();
        
        jQuery.getScript('http://quanticalabs.com/.tools/EnvatoItems/js/getItems.js',function() { });
		$(".go-getting-started").click(function(event){
			var tab = $(this).attr('href');
			$('.tabs').tabs('select', tab);
		});
		if(document.referrer.toLowerCase().indexOf('themeforest.net')!=-1)
		{
			$('.tabs').tabs('select', 'tab-9');
			$('.ui-state-default:not(".ui-state-active")').css("display", "none");
		}
    });


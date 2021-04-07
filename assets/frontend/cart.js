jQuery(document).ready(function($){
	
	$('.quantity .quantity_selector_button .negative_button, .quantity .quantity_selector_button .positive_button').click(function(e){
        e.preventDefault();
        if($(this).hasClass('negative_button'))
        {
            var current_val = parseFloat($(this).parent().find('.qty').val());
            if(current_val>=1)
            {
                current_val-=0.5;
            }

            $(this).parent().find('.qty').val(current_val);
            
        }
        else if($(this).hasClass('positive_button'))
        {
            var current_val = parseFloat($(this).parent().find('.qty').val());
            current_val +=0.5;

            $(this).parent().find('.qty').val(current_val);
        }
        $(this).parent().find('.qty').trigger('change');

    });

    $('.woocommerce-cart-form .actions').find('button').click(function(){
    	setTimeout(function(){
	    	$('.quantity .quantity_selector_button .negative_button, .quantity .quantity_selector_button .positive_button').click(function(e){
		        e.preventDefault();
		        if($(this).hasClass('negative_button'))
		        {
		            var current_val = parseFloat($(this).parent().find('.qty').val());
		            if(current_val>=1)
		            {
		                current_val-=0.5;
		            }

		            $(this).parent().find('.qty').val(current_val);
		            
		        }
		        else if($(this).hasClass('positive_button'))
		        {
		            var current_val = parseFloat($(this).parent().find('.qty').val());
		            current_val +=0.5;

		            $(this).parent().find('.qty').val(current_val);
		        }
		        $(this).parent().find('.qty').trigger('change');

		    });
	    }, 4000);
    });

});
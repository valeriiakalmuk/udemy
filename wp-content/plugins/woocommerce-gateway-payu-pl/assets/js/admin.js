jQuery(document).ready(function(){
    function ia_change() {
        if ( jQuery('#woocommerce_payu_payu_ia_enabled').is(':checked') ) {
            jQuery('#woocommerce_payu_ia_title').closest('tr').show();
            jQuery('#woocommerce_payu_ia_description').closest('tr').show();
            jQuery('#woocommerce_payu_ia_title').prop('required',true);
        }
        else {
            jQuery('#woocommerce_payu_ia_title').closest('tr').hide();
            jQuery('#woocommerce_payu_ia_description').closest('tr').hide();
            jQuery('#woocommerce_payu_ia_title').prop('required',false);
        }
    }

    function subscriptions_change() {
        if ( jQuery('#woocommerce_payu_payu_subscriptions_enabled').is(':checked') ) {
            jQuery('#woocommerce_payu_subscriptions_title').closest('tr').show();
            jQuery('#woocommerce_payu_subscriptions_description').closest('tr').show();
            jQuery('#woocommerce_payu_subscriptions_title').prop('required',true);
        }
        else {
            jQuery('#woocommerce_payu_subscriptions_title').closest('tr').hide();
            jQuery('#woocommerce_payu_subscriptions_description').closest('tr').hide();
            jQuery('#woocommerce_payu_subscriptions_title').prop('required',false);
        }
    }

    function api_version_change() {
        let is_sandbox = jQuery('#woocommerce_payu_sandbox').is(':checked');

        jQuery('tr.pos-settings').each(function(i){
            jQuery(this).show();
        });

        if (jQuery('#woocommerce_payu_api_version').val() == 'rest_api') {           

            jQuery('tr[data-api="classic"]').each(function(i){
                jQuery(this).hide();
            });
            jQuery('#woocommerce_payu_testmode').closest('tr').hide();
            jQuery('#woocommerce_payu_check_sig').closest('tr').hide();
            jQuery('#woocommerce_payu_returns_title').hide();
            jQuery('#woocommerce_payu_returns_title').next().hide();
            jQuery('#woocommerce_payu_return_error').closest('table').hide();

              //sandbox_change();
        }else {
            jQuery('tr[data-api="rest"]').each(function(i){
                jQuery(this).hide();
            });

            jQuery('#woocommerce_payu_testmode').closest('tr').show();
            jQuery('#woocommerce_payu_check_sig').closest('tr').show();
            jQuery('#woocommerce_payu_returns_title').show();
            jQuery('#woocommerce_payu_returns_title').next().show();
            jQuery('#woocommerce_payu_return_error').closest('table').show();

        }

        if (is_sandbox) {
            jQuery('tr[data-env="production"]').each(function (i) {
                jQuery(this).hide();
            });
        } else {
            jQuery('tr[data-env="sandbox"]').each(function (i) {
                jQuery(this).hide();
            });
        }
            



    }

    jQuery('<span>'+ payu_admin_object.protocol +' </span>').insertBefore('#woocommerce_payu_return_error, #woocommerce_payu_return_ok, #woocommerce_payu_return_reports');
    jQuery('#woocommerce_payu_return_error').val(payu_admin_object.site_url + '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%&errorId=%error%');
    jQuery('#woocommerce_payu_return_ok').val(payu_admin_object.site_url + '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%');
    jQuery('#woocommerce_payu_return_reports').val(payu_admin_object.site_url + '?wc-api=WC_Gateway_Payu&sessionId=%sessionId%&orderId=%orderId%');

    api_version_change();
    jQuery('#woocommerce_payu_api_version').on('change', function () {
        api_version_change();
    });
    jQuery('#woocommerce_payu_sandbox').on('click', function () {
        api_version_change();
    });
    ia_change();
    jQuery('#woocommerce_payu_payu_ia_enabled').on('change', function () {
        ia_change();
    });
    subscriptions_change();
    jQuery('#woocommerce_payu_payu_subscriptions_enabled').on('change', function () {
        subscriptions_change();
    });

    let currency_selector = jQuery('#currency-selector');
    let add_new_pos = jQuery('#add-new-payu-pos');
    let settings_accordion = jQuery('#settings-accordion');


	jQuery('.payu-tips').tipTip(
		{
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200,
			'defaultPosition': "top"
		}
	);

	settings_accordion.accordion({
		collapsible: true,
		active: false,
		heightStyle: "content"
	});

    refresh_remove_actions();
    refresh_currency_selector();

    currency_selector.on('change', function () {
        if (currency_selector.val() === '') {
            add_new_pos.prop('disabled', true);
        } else {
            add_new_pos.prop('disabled', false);
        }
    });

    currency_selector.trigger('change');

	add_new_pos.on('click', function(e){
		e.preventDefault();

		var data = {
			action: 'payu_get_single_currency',
            security: payu_admin_object.payu_nonce,
            currency: currency_selector.val()
		};
		jQuery.post(ajaxurl, data, function(response) {
            if (response != 0) {
                console.log(response);
                //response = JSON.parse(response);
				if (response.success === true) {
					settings_accordion.append( response.content );
					settings_accordion.accordion( "refresh" );
                    refresh_remove_actions();
                    refresh_currency_selector();
                    api_version_change();
				}
			}
		})
    });
    
    function refresh_remove_actions(){
		jQuery( '.remove_payu_profile' ).on('click', function(e){
			e.preventDefault();
			if (confirm("Czy jesteś pewien że chcesz usunąć tą walutę? po zapisaniu ustawień nie będzie można odzyskać ustawień.")) {
				let header = jQuery( this ).closest('h3');
				let container = header.next('div');
				container.remove();
				header.remove();
                settings_accordion.accordion("refresh");
                refresh_currency_selector();
			} else {
				return false;
			}
		});
    }
    
    function refresh_currency_selector(){
        currency_selector.find('option').each(function(i){
            if (jQuery('h3[data-currency="' + jQuery(this).attr('value') + '"]').length > 0) {
                jQuery(this).prop('disabled', true);
            } else {
                jQuery(this).prop('disabled', false);
            }            
        });
        currency_selector.prop("selectedIndex", 0);
        currency_selector.trigger('change');
	}


});
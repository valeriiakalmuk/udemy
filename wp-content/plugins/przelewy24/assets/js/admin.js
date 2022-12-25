jQuery(document).ready(function ($) {
    var $superFirstInput = $('#woocommerce_przelewy24_p24_paymethods_super_first');

    function getBanksList() {
        var banksList = [];
        $('#woocommerce_przelewy24_p24_paymethods_all option').each(function () {
            if (parseInt($(this).val()) > 0) {
                banksList.push({id: $(this).val(), name: $(this).text()});
            }
        });

        if (banksList.length == 0) {
            banksList.push({id: 25, name: ""});
            banksList.push({id: 31, name: ""});
            banksList.push({id: 112, name: ""});
            banksList.push({id: 20, name: ""});
            banksList.push({id: 65, name: ""});
        }
        return banksList;
    }

    var getStyleForBank = function(id) {
        var $row = $('#p24-hidden-bank-icon-list tr.js-bank-id-' + id);
        var css = '';
        if ($row.length) {
            var url = $row.find('td.js-icon-type-mobile').text();
            css = 'style="background-image: url(' + url + ')"';
        }
        return css;
    };

    function getSuperBankBox(id, name) {
        if (name == undefined) name = '';
        var css = getStyleForBank(id);
        return '<a id="p24-admin-super-bank-' + id + '" class="bank-box" data-super-id="' + id + '"><div class="bank-logo" ' + css + '></div><div class="bank-name">' + name + '</div></a>';
    }

    function getBankBox(id, name) {
        if (name == undefined) name = '';
        var css = getStyleForBank(id);
        return '<a class="bank-box" data-id="' + id + '"><div class="bank-logo" ' + css + '></div><div class="bank-name">' + name + '</div></a>';
    }

    function toggleSomething(toggle, selector) {
        if (toggle) {
            $(selector).show();
        } else {
            $(selector).hide();
        }
    }

    function updateSuperPaymethods() {
        var set = [];
        $('.paymethod .super-selected a.bank-box').each(function () {
            set.push($(this).attr('data-super-id'));
        });
        $superFirstInput.val(set.join(','));
    }

    function updatePaymethods() {
        $('.bank-box').removeClass('ui-helper-unrotate');
        var maxNo = parseInt($('.paymethod .selected').attr('data-max'));
        if (maxNo > 0) {
            if ($('.paymethod .selected a[data-id]').length > maxNo) {
                var i = 0;
                $('.paymethod .selected a[data-id]').each(function () {
                    i++;
                    if (i > maxNo) {
                        $('.paymethod .available')
                            .prepend($(this))
                            .append($('#clear'));
                    }
                });
            }
        }
        $('#woocommerce_przelewy24_p24_paymethods_first').val('');
        $('.paymethod .selected a[data-id]').each(function () {
            $('#woocommerce_przelewy24_p24_paymethods_first').val(
                $('#woocommerce_przelewy24_p24_paymethods_first').val() +
                ($('#woocommerce_przelewy24_p24_paymethods_first').val().length ? ',' : '') +
                $(this).attr('data-id')
            );
        });
        $('#woocommerce_przelewy24_p24_paymethods_second').val('');
        $('.paymethod .available a[data-id]').each(function () {
            $('#woocommerce_przelewy24_p24_paymethods_second').val(
                $('#woocommerce_przelewy24_p24_paymethods_second').val() +
                ($('#woocommerce_przelewy24_p24_paymethods_second').val().length ? ',' : '') +
                $(this).attr('data-id')
            );
        });
    }

    $(document).ready(function () {

        $superFirstInput.hide();
        $('#woocommerce_przelewy24_p24_paymethods_first').hide();
        $('#woocommerce_przelewy24_p24_paymethods_second').closest('tr').hide();
        $('#woocommerce_przelewy24_p24_paymethods_all').closest('tr').hide();

        if ($('#woocommerce_przelewy24_p24_paymethods_all option[value=266]').length) {
            $('#woocommerce_przelewy24_p24_add_to_alternative_method').closest('tr').show();
            $('#woocommerce_przelewy24_p24_custom_promote_p24').closest('tr').show();
        } else {
            $('#woocommerce_przelewy24_p24_add_to_alternative_method').closest('tr').hide();
            $('#woocommerce_przelewy24_p24_custom_promote_p24').closest('tr').hide();
        }

        $superFirstInput.closest('td').append(
            '<div class="paymethod" id="p24-admin-config-methods-checkout">' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg3 + '</div>' +
            '<div class="sortable super-selected" style="width: 730px; border: 5px dashed lightgray; height: 80px; padding: 0.5em; overflow: hidden;"></div>' +
            '<div style="clear:both"></div>' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg4 + '</div>' +
            '<div class="sortable super-available"></div>' +
            '</div>' +
            ''
        );

        var $superSelected = $('.paymethod .super-selected');
        var $superAvailable = $('.paymethod .super-available');

        $('#woocommerce_przelewy24_p24_paymethods_first').closest('td').append(
            '<div class="paymethod" id="p24-admin-config-methods-confirmation">' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg1 + '</div>' +
            '<div class="sortable selected" data-max="5" style="width: 730px; border: 5px dashed lightgray; height: 80px; padding: 0.5em; overflow: hidden;"></div>' +
            '<div style="clear:both"></div>' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg2 + '</div>' +
            '<div class="sortable available"></div>' +
            '</div>' +
            ''
        );

        $('#woocommerce_przelewy24_p24_show_methods_checkout').change(function () {
            var showed = $(this).is(':checked');
            toggleSomething(showed, $('#p24-admin-config-methods-checkout').closest('tr'));
        }).trigger('change');

        $('#woocommerce_przelewy24_p24_show_methods_confirmation').change(function () {
            var showed = $(this).is(':checked');
            toggleSomething(showed, $('#p24-admin-config-methods-confirmation').closest('tr'));
            toggleSomething(showed, $('#woocommerce_przelewy24_p24_graphics').closest('tr'));
            toggleSomething(showed, $('#woocommerce_przelewy24_p24_acceptinshop').closest('tr'));
        }).trigger('change');

        $.each(getBanksList(), function () {
            $superAvailable.append(getSuperBankBox(this.id, this.name));
            $('.sortable.available').append(getBankBox(this.id, this.name));
        });
        $superAvailable.append('<div style="clear:both" id="clear"></div>');
        $('.sortable.available').append('<div style="clear:both" id="clear"></div>');

        $superFirstInput.val().split(',').forEach(function (val) {
            $('#p24-admin-super-bank-' + val).appendTo($superSelected);
        });

        if ($('#woocommerce_przelewy24_p24_paymethods_first').val().length > 0) {
            $.each($('#woocommerce_przelewy24_p24_paymethods_first').val().split(','), function (i, v) {
                $('.bank-box[data-id=' + v + ']').appendTo('.paymethod .selected');
            });
        }
        if ($('#woocommerce_przelewy24_p24_paymethods_second').val().length > 0) {
            $.each($('#woocommerce_przelewy24_p24_paymethods_second').val().split(',').reverse(), function (i, v) {
                $('.bank-box[data-id=' + v + ']').prependTo('.paymethod .available');
            });
        }
        updateSuperPaymethods();
        updatePaymethods();

        var selectorForSuperFirst =  $('.paymethod .super-selected, .paymethod .super-available');
        $(selectorForSuperFirst).sortable({
            connectWith: selectorForSuperFirst,
            placeholder: "bank-box bank-placeholder",
            stop: function () {
                updateSuperPaymethods();
                updateIfP24NOW();
            },
            revert: true,
            start: function (e, ui) {
                window.setTimeout(function () {
                    $('.bank-box.ui-sortable-helper').on('mouseup', function () {
                        $(this).addClass('ui-helper-unrotate');
                    });
                }, 100);
            },
        }).disableSelection();

        $(".sortable.selected,.sortable.available").sortable({
            connectWith: ".sortable.selected,.sortable.available",
            placeholder: "bank-box bank-placeholder",
            stop: function () {
                updatePaymethods();
            },
            revert: true,
            start: function (e, ui) {
                window.setTimeout(function () {
                    $('.bank-box.ui-sortable-helper').on('mouseup', function () {
                        $(this).addClass('ui-helper-unrotate');
                    });
                }, 100);
            },
        }).disableSelection();

        if ($('#p24_no_api_key_provided').length) {
            $('#woocommerce_przelewy24_p24_oneclick,#woocommerce_przelewy24_p24_payinshop,#woocommerce_przelewy24_p24_acceptinshop,#woocommerce_przelewy24_p24_paymethods,#woocommerce_przelewy24_p24_graphics,#woocommerce_przelewy24_p24_paymethods_first,#woocommerce_przelewy24_p24_paymethods_second').closest('tr').hide();
        }
    });
});

(function( $ ) {
    /* Activate automatic admin change currency selector. */
    $( function () {
        $( '.js_currency_admin_selector' ).on( 'change', function () {
            var val, data, nonce;
            val = $( this ).val();
            nonce = $( '#p24_nonce' ).val();
            if ( ! nonce ) {
                /* Fallback for situations the id cannot be set. */
                nonce = $( '.js-p24-alt-nonce' ).val();
            }
            data = {
                action: 'p24_change_currency',
                p24_action_type_field: 'change_currency',
                p24_nonce: nonce,
                p24_currency: val
            };
            $.post( ajaxurl, data, function () {
                $('form').each(function (idx, form) {
                    form.reset();
                });

                location.reload() ;
            });
        });
    });

})( jQuery );

(function( $ ) {
    $( function () {
        var $cb = $('#woocommerce_przelewy24_p24_use_special_status');
        var $sPending = $('#woocommerce_przelewy24_p24_custom_pending_status');
        var $o1stPenging = $sPending.find('option').first();
        var $sProcessing = $('#woocommerce_przelewy24_p24_custom_processing_status');
        var $o1stProcessing = $sProcessing.find('option').first();

        var labelProcessingEnOrg = 'Processing';
        var labelProcessingEnP24 = 'Paid by P24';
        var labelProcessingPlOrg = 'W trakcie realizacji';
        var labelProcessingPlP24 = 'Opłacone przez P24';

        var addSuffix = function($opt) {
            var existing = $opt.text();
            if (labelProcessingEnOrg === existing) {
                $opt.text(labelProcessingEnP24);
            } else if (labelProcessingPlOrg === existing) {
                $opt.text(labelProcessingPlP24);
            } else {
                $opt.text($opt.text() + ' P24');
            }
        };

        var dropSuffix = function($opt) {
            var existing = $opt.text();
            console.log(existing);
            if (labelProcessingEnP24 === existing) {
                $opt.text(labelProcessingEnOrg);
            } else if (labelProcessingPlP24 === existing) {
                $opt.text(labelProcessingPlOrg);
            } else {
                var rx = /(.+)\sP24/;
                $opt.text(rx.test(existing) ? rx.exec(existing)[1] : existing);
            }
        };

        $cb.on('change', function () {
            if ($cb.prop('checked')) {
                $sProcessing.prop('disabled', false);
                addSuffix($o1stProcessing);
                $sPending.prop('disabled', false);
                addSuffix($o1stPenging);
            } else {
                $sProcessing.val('').prop('disabled', true);
                dropSuffix($o1stProcessing);
                $sPending.val('').prop('disabled', true);
                dropSuffix($o1stPenging);
            }
        });

        $cb.trigger('change');
    });
})( jQuery );

// --- changes below associated with p24 now

/**
 * Update sortable based on checkbox
 */
function togglePromotedP24NOW() {
    $p24nowBox = jQuery("#p24-admin-super-bank-266");
    $p24nowBox.remove();
    $idValuesHolder = jQuery("#woocommerce_przelewy24_p24_paymethods_super_first");
    if (jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method").is(':checked')) {
        jQuery(".super-selected").append($p24nowBox);
        addP24ToPromoted();
    } else if(jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method").is(':not(:checked)')){
        jQuery(".super-available").append($p24nowBox);
        removeP24FromPromoted()
    }
    jQuery('.paymethod .super-selected, .paymethod .super-available').sortable( "refreshPositions" );
}

/* promote if added to be */
jQuery(document).ready(function () {
    jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method").click(togglePromotedP24NOW);
});

// /* promote if added  */
jQuery(document).ready(function () {
    jQuery("#woocommerce_przelewy24_p24_paymethods_super_first").change(function () {
        console.log('woocommerce_przelewy24_p24_paymethods_super_first changed');
        if(jQuery('.super-selected').find("#p24-admin-super-bank-266").length > 0){
            jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method").prop('checked', true);
        }else if(jQuery('.super-selected').find("#p24-admin-super-bank-266").length === 0){
            jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method").prop('checked', false);
        }
    });
});

/**
 * Check checkbox based on sortable change
 */
function updateIfP24NOW() {
    jQuery("#woocommerce_przelewy24_p24_add_to_alternative_method")
        .prop('checked', (jQuery('.super-selected').find("#p24-admin-super-bank-266").length > 0));
}

function addP24ToPromoted(){
    jQuery('#woocommerce_przelewy24_p24_paymethods_super_first').val(function () {
        let values = jQuery('#woocommerce_przelewy24_p24_paymethods_super_first')
            .val()
            .split(',');
        values.push('266');
        return values.join(',');
    });
}

function removeP24FromPromoted(){
    jQuery('#woocommerce_przelewy24_p24_paymethods_super_first').val(function () {
        let values = jQuery('#woocommerce_przelewy24_p24_paymethods_super_first')
            .val()
            .split(',');
        const index = values.indexOf('266');
        if (index > -1) {
            values.splice(index, 1);
        }
        return values.join(',');
    });
}

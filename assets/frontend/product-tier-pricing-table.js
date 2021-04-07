jQuery(document).ready(function ($) {
    $.widget.bridge('uiTooltip', $.ui.tooltip);

    var TiredPriceTable = function () {

        
        this.currencyOptions = tieredPricingTable.currency_options;
        this.productType = tieredPricingTable.product_type;
        this.$productQuantityField = $('form.cart').find('[name=quantity]');
        this.tieredPriceTableSelector = '[data-price-rules-table]';
        this.is_premium = tieredPricingTable.is_premium === 'yes';

        this.init = function () {
           
            if (this.productType === 'variable') {
                $(".single_variation_wrap").on("show_variation", this.loadVariationTable.bind(this));

                $(document).on('reset_data', function () {
                    $('[data-variation-price-rules-table]').html('');
                });
            }

            this.$productQuantityField.on('change', this.setPriceByQuantity.bind(this));
            this.$productQuantityField.trigger('change');
        };

        this.setQuantityByClick = function (e) {

            if (!this.is_premium) {
                return;
            }

            var row = $(e.target).closest('tr');
            if (row) {
                var qty = parseInt(row.data('price-rules-amount'));

                if (qty > 0) {
                    this.$productQuantityField.val(qty);
                }
            }

            this.$productQuantityField.trigger('change');
        };

        this.initTooltip = function () {
            var self = this;

            

            $(document).uiTooltip({
                items: '.price-table-tooltip-icon',
                tooltipClass: "price-table-tooltip",
                content: function () {
                    return $(self.tieredPriceTableSelector);
                },
                hide: {
                    effect: "fade",
                },
                position: {
                    my: "center bottom-40",
                    at: "center bottom",
                    using: function (position) {
                        $(this).css(position);
                    }
                },
                close: function (e, tooltip) {
                    tooltip.tooltip.innerHTML = '';
                }
            });
        };
        
        this.set_new_price_foraddon = function (amount,quantity) {
            
            var actual_amount = $('.actual_price').attr("data-actual_price");
            var final_price = quantity * actual_amount;

            var woo_tiered_addon_total_price = parseFloat(0);
            if($('.woo_tiered_addon_total_price').length)
            {
                woo_tiered_addon_total_price = parseFloat($('.woo_tiered_addon_total_price').attr("data-total-addon-price"));
            }
            var final_price = final_price + woo_tiered_addon_total_price;
            
            
            $('.total_product_price span').html(this.formatPrice(final_price));


        };
        this.set_new_price = function (amount,quantity) {
            var total_price = this.formatPrice(quantity * amount);
            
            var actual_amount = $('.actual_price').attr("data-actual_price");
            var final_price = quantity * amount;
            var before_price = quantity * actual_amount;

            actual_amount = before_price - final_price;

            actual_amount = this.formatPrice(actual_amount);
            var amount = this.formatPrice(amount);
            $('.woo_tiered_table_new_price_quantity').attr("data-total-quantity-table-price",final_price);
            $('.woo_tiered_table_new_price_quantity span.first').html(quantity +' x ' + amount +' = '+ total_price );
            $('.woo_tiered_table_new_price_quantity span.second').html(actual_amount );

            var woo_tiered_addon_total_price = parseFloat(0);
            if($('.woo_tiered_addon_total_price').length)
            {
                woo_tiered_addon_total_price = parseFloat($('.woo_tiered_addon_total_price').attr("data-total-addon-price"));
            }
            var final_price = final_price + woo_tiered_addon_total_price;
            if(!$('.price_rule_quantity_div').length)
            {
                actual_amount = $('.actual_price').attr("data-actual_price");
                final_price = actual_amount;
            }
            
            $('.total_product_price span').html(this.formatPrice(final_price));

        };

        this.setPriceByQuantity = function () {
            
            $('.price-rule-active').removeClass('price-rule-active');

            if ($(this.tieredPriceTableSelector).length > 0) {

                var priceRules = JSON.parse($(this.tieredPriceTableSelector).attr('data-price-rules'));

                var quantity = this.$productQuantityField.val();
                var _keys = [];

                for (var k in priceRules) {
                    if (priceRules.hasOwnProperty(k)) {
                        _keys.push(parseInt(k));
                    }
                }

                _keys = _keys.sort(function (a, b) {
                    return a > b
                }).reverse();

                for (var i = 0; i < _keys.length; i++) {
                    var amount = parseInt(_keys[i]);
                    var foundPrice = false;
                    var priceHtml;
                    var price;

                    if (quantity >= amount) {
                        price = parseFloat($('[data-price-rules-amount="' + amount + '"]').data('price-rules-price'));
                        priceHtml = $('[data-price-rules-amount=' + amount + ']').find('[data-price-rules-formated-price]').html();

                        //this.changePriceHtml(priceHtml);
                        //this.set_new_price(amount,quantity,priceHtml);

                        foundPrice = true;

                        $(document).trigger('tiered_price_update', {price, quantity, __instance: this});

                        break;
                    }
                }

                amount = foundPrice ? amount : this.getTableMinimum();

                var currentPrice = $('[data-price-rules-amount="' + amount + '"]').data('price-rules-price');

                    if (this.is_premium) {
                        var formatedPrice = this.formatPrice(quantity * currentPrice);

                        this.changePriceHtml(formatedPrice, true);
                    }

                if (!foundPrice) {

                    
                        //this.changePriceHtml(this.getDefaultPriceHtml());
                        //this.set_new_price(amount,quantity,this.getDefaultPriceHtml());
                    $('[data-price-rules-amount=' + this.getTableMinimum() + ']').addClass('price-rule-active');

                    price = parseFloat($('[data-price-rules-amount="' + this.getTableMinimum() + '"]').data('price-rules-price'));

                    $(document).trigger('tiered_price_update', {price, quantity, __instance: this});

                    //return;
                }

                $('[data-price-rules-amount="' + amount + '"]').addClass('price-rule-active');
                this.set_new_price(currentPrice,quantity);
            }
            else
            {
                var quantity = this.$productQuantityField.val();
                amount = foundPrice ? amount : this.getTableMinimum();

                var currentPrice = $('[data-price-rules-amount="' + amount + '"]').data('price-rules-price');
                this.set_new_price_foraddon(currentPrice,quantity);
            }
        };

        this.formatPrice = function (price, includeSuffix = true) {
            price = this.formatNumber(price, this.currencyOptions.decimals, this.currencyOptions.decimal_separator, this.currencyOptions.thousand_separator);
            var currency = '<span class="woocommerce-Price-currencySymbol">' + this.currencyOptions.currency_symbol + '</span>';

            var priceSuffixPart = includeSuffix ? ' %3$s ' : '';

            var template = '<span class="woocommerce-Price-amount amount">' + this.currencyOptions.price_format + priceSuffixPart + '</span>';

            return $('<textarea />').html(template.replace('%2$s', price).replace('%1$s', currency).replace('%3$s', this.getPriceSuffix())).text();
        };

        this.getPriceSuffix = function () {
            // Allow external plugins modifying suffix
            if (typeof tieredPriceTableGetProductPriceSuffix !== "undefined") {
                return tieredPriceTableGetProductPriceSuffix();
            }

            return this.currencyOptions.price_suffix;
        }

        this.formatNumber = function (number, decimals, dec_point, thousands_sep) {

            var i, j, kw, kd, km;

            if (isNaN(decimals = Math.abs(decimals))) {
                decimals = 2;
            }
            if (dec_point == undefined) {
                dec_point = ",";
            }
            if (thousands_sep == undefined) {
                thousands_sep = ".";
            }

            i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

            if ((j = i.length) > 3) {
                j = j % 3;
            } else {
                j = 0;
            }

            km = (j ? i.substr(0, j) + thousands_sep : "");
            kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);

            kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");
            return km + kw + kd;
        };

        this.getDefaultPriceHtml = function () {
            return $('[data-price-rules-amount=' + this.getTableMinimum() + ']').find('[data-price-rules-formated-price]').html();
        };

        this.changePriceHtml = function (priceHtml, wipeDiscount) {

            wipeDiscount = wipeDiscount === undefined ? false : wipeDiscount;

            var priceContainer = $('form.cart').closest('.summary').find('[data-tiered-price-wrapper]');

            if (priceContainer.length < 1 && typeof tieredPriceTableGetProductPriceContainer != "undefined") {
                priceContainer = tieredPriceTableGetProductPriceContainer();
            }

            if (wipeDiscount) {
                priceContainer.html(priceHtml);
            }

            if (priceContainer.children('ins').length > 0) {
                priceContainer.find('ins').html(priceHtml);
            } else {
                priceContainer.find('span:first').html(priceHtml);
            }
        };

        this.loadVariationTable = function (event, variation) {

            $.post(document.location.origin + document.location.pathname + '?wc-ajax=get_price_table', {
                variation_id: variation['variation_id'],
                nonce: tieredPricingTable.load_table_nonce
            }, (function (response) {
                $('.price-rules-table').remove();
                $('[data-variation-price-rules-table]').html(response);

                if (!response) {
                    this.formatPrice(this.formatPrice(variation.display_price), true);
                    this.$productQuantityField.val(variation.min_qty);
                } else {
                    this.$productQuantityField.trigger('change');
                }

               

            }).bind(this));
        };

        this.getTableMinimum = function () {
            var min = $(this.tieredPriceTableSelector).data('minimum');

            min = min ? parseInt(min) : 1;

            return min;
        };

        this.getProductName = function () {
            return $(this.tieredPriceTableSelector).data('product-name');
        }
    };

    var tieredPriceTable = new TiredPriceTable();

    tieredPriceTable.init();
    
     $('.woo_tiered_addon_prices_quantity_selector .quantity_selector_button .negative_button, .woo_tiered_addon_prices_quantity_selector .quantity_selector_button .positive_button').click(function(e){
        e.preventDefault();
        if($(this).hasClass('negative_button'))
        {
            var current_val = $(this).parent().find('.selected_value').attr("data-val");
            if(current_val>0)
            {
                current_val--;
            }

            $(this).parent().find('.selected_value').attr("data-val",current_val);
            $(this).parent().find('.selected_value').html(current_val);
            
            $(this).parent().parent().find('.addon_field_quantity').val(current_val);
            $(this).parent().attr("data-addon-quantity",current_val);
        }
        else if($(this).hasClass('positive_button'))
        {
            var current_val = $(this).parent().find('.selected_value').attr("data-val");
            current_val++;
            
            $(this).parent().find('.selected_value').attr("data-val",current_val);
            $(this).parent().find('.selected_value').html(current_val);
            
            $(this).parent().parent().find('.addon_field_quantity').val(current_val);
            $(this).parent().attr("data-addon-quantity",current_val);
        }

        var addon_total_price = 0;
        $('.woo_tiered_addon_prices_item').each(function(){
            var add_qty = $(this).find('.quantity_selector_button').attr("data-addon-quantity");
            var add_price = $(this).find('.quantity_selector_button').attr("data-addon-price");
            addon_total_price+= add_price*add_qty;
        });
        $('.woo_tiered_addon_total_price').attr("data-total-addon-price",addon_total_price);
        
        var amount = tieredPriceTable.formatPrice(addon_total_price);
        
        $('.woo_tiered_addon_total_price span').html(amount);
        var woo_tiered_table_new_price_quantity = parseFloat($('.actual_price').attr("data-actual_price"));
        
        if($('.woo_tiered_table_new_price_quantity').length)
        {
            woo_tiered_table_new_price_quantity = parseFloat($('.woo_tiered_table_new_price_quantity').attr("data-total-quantity-table-price"));    
        }
        else
        {
             var productQuantityField = parseFloat($('form.cart').find('[name=quantity]').val());
             
            woo_tiered_table_new_price_quantity = productQuantityField * woo_tiered_table_new_price_quantity;
        }

        var total_product_price = addon_total_price + woo_tiered_table_new_price_quantity;

        $('.total_product_price span').html(tieredPriceTable.formatPrice(total_product_price));

    });
});

/**
 * SUMMARY TABLE
 */
(function ($) {

    $(document).on('tiered_price_update', function (event, data) {

        $('[data-tier-pricing-table-summary]').removeClass('tier-pricing-summary-table--hidden');

        $('[data-tier-pricing-table-summary-product-qty]').text(data.__instance.formatNumber(data.quantity, 0));
        $('[data-tier-pricing-table-summary-product-price]').html(data.__instance.formatPrice(data.price, false));
        $('[data-tier-pricing-table-summary-total]').html(data.__instance.formatPrice(data.price * data.quantity, false));
        $('[data-tier-pricing-table-summary-product-name]').html(data.__instance.getProductName());
    });

    $(document).on('reset_data', function () {
        $('[data-tier-pricing-table-summary]').addClass('tier-pricing-summary-table--hidden');
    });

    $(document).on('found_variation', function () {
        $('[data-tier-pricing-table-summary]').addClass('tier-pricing-summary-table--hidden');
    });

})(jQuery);

/**
 * MIN QUANTITIES
 */

(function ($) {

    $(document).on('found_variation', function (event, variation) {
        if (typeof variation.qty_value !== "undefined" && variation.qty_value > 1) {
            $('form.cart').find('[name=quantity]').val(variation.qty_value)
        }
    });
    $(document).ready(function(){

       




    });
})(jQuery);

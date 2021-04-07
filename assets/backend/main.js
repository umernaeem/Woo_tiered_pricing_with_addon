(function ($) {
    jQuery(document).ready(function ($) {

        jQuery(document).on('click', '[data-add-new-price-rule]', function (e) {
            e.preventDefault();

            var newRuleInputs = jQuery(e.target).parent().find('[data-price-rules-input-wrapper]').first().clone();

            jQuery('<span data-price-rules-container></span>').insertBefore(jQuery(e.target))
                .append(newRuleInputs)
                .append('<span class="notice-dismiss remove-price-rule" data-remove-price-rule style="vertical-align: middle"></span>')
                .append('<br><br>');

            newRuleInputs.children('input').val('');

            recalculateIndexes(jQuery(e.target).closest('[data-price-rules-wrapper]'));
        });

        jQuery('body').on('click', '.remove-price-rule', function (e) {
            e.preventDefault();

            var element = jQuery(e.target.parentElement);
            var wrapper = element.parent('[data-price-rules-wrapper]');
            var containers = wrapper.find('[data-price-rules-container]');

            if ((containers.length) < 2) {
                containers.find('input').val('');
                return;
            }

            jQuery('[data-price-rules-wrapper] .wc_input_price').trigger('change');

            element.remove();

            recalculateIndexes(wrapper);
        });

        $(document).on('change', '[data-role-tiered-price-type-select]', function (e) {

            var $container = $(e.target).closest('div');

            $container.find('[data-role-tiered-price-type]').css('display', 'none');
            $container.find('[data-role-tiered-price-type-' + this.value + ']').css('display', 'block');
        });

        var initializedBlocks = [];

        var RoleBasedBlock = function () {

            this.$block = null;

            this.init = function (id) {

                this.variationCanBeChangedAlreadyTriggered = false;
                this.id = id;
                this.$block = jQuery('#' + id);

                if (initializedBlocks[id] !== undefined) {
                    this.unbindEvents();
                }

                this.bindEvents();

                initializedBlocks[id] = this;
            };

            this.bindEvents = function () {
                $('body').on('click', '#' + this.id + ' .tpt-role-based-role-action--delete', this.removeRole.bind(this));
                $('body').on('click', '#' + this.id + ' .tpt-role-based-role__header', this.toggleRoleView.bind(this));
                $('body').on('click', '#' + this.id + ' .tpt-role-based-adding-form__add-button', this.addRole.bind(this));
            }

            this.unbindEvents = function () {
                $('body').off('click', '#' + this.id + ' .tpt-role-based-role-action--delete');
                $('body').off('click', '#' + this.id + ' .tpt-role-based-role__header');
                $('body').off('click', '#' + this.id + ' .tpt-role-based-adding-form__add-button');
            }

            this.toggleRoleView = function (event) {

                var $element = $(event.target);

                if ($element.hasClass('tpt-role-based-role-action--delete')) {
                    return;
                }

                var role = $element.closest('.tpt-role-based-role');

                if (role.data('visible')) {
                    this.hideRole(role);
                } else {
                    this.showRole(role);
                }
            };

            this.showRole = function ($role) {
                $role.find('.tpt-role-based-role__content').stop().slideDown(400);
                $role.find('.tpt-role-based-role__action-toggle-view')
                    .removeClass('tpt-role-based-role__action-toggle-view--open')
                    .addClass('tpt-role-based-role__action-toggle-view--close');

                $role.data('visible', true);
            };

            this.hideRole = function ($role) {
                $role.find('.tpt-role-based-role__content').stop().slideUp(400);
                $role.find('.tpt-role-based-role__action-toggle-view')
                    .removeClass('tpt-role-based-role__action-toggle-view--close')
                    .addClass('tpt-role-based-role__action-toggle-view--open');
                $role.data('visible', false);
            };

            this.removeRole = function (e) {
                e.preventDefault();

                if (confirm("Are you sure?")) {

                    var $roleToRemove = $(e.target).closest('.tpt-role-based-role');
                    var roleSlug = $roleToRemove.data('role-slug');

                    this.$block.find('.tpt-role-based-adding-form__role-selector').append('<option value="' + roleSlug + '">' + $roleToRemove.data('role-name') + '</option>');
                    this.$block.find('.tiered_price_rules_roles_to_delete').find('[value=' + roleSlug + ']').prop('selected', true);

                    $roleToRemove.slideUp(400, function () {
                        $roleToRemove.remove();
                    });

                    this.triggerVariationCanBeUpdated();
                }
            };

            this.block = function () {
                this.$block.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            };

            this.unblock = function () {
                this.$block.unblock();
            };

            this.addRole = function (event) {

                event.preventDefault();

                var selectedRole = this.$block.find('.tpt-role-based-adding-form__role-selector').val();

                if (selectedRole) {

                    var action = this.$block.data('add-action');
                    var nonce = this.$block.data('add-action-nonce');
                    var productId = this.$block.data('product-id');
                    var loop = this.$block.data('loop');

                    $.ajax({
                        method: 'GET',
                        url: ajaxurl,
                        data: {
                            action: action,
                            nonce: nonce,
                            role: selectedRole,
                            product_id: productId,
                            loop: loop,
                        },
                        beforeSend: (function () {
                            this.block();
                        }).bind(this)
                    }).done((function (response) {
                        if (response.success && response.role_row_html) {
                            this.$block.find('.tpt-role-based-roles').append(response.role_row_html);
                            this.$block.find('.tpt-role-based-no-roles').css('display', 'none');

                            $.each(this.$block.find('.tpt-role-based-role'), (function (i, el) {
                                this.hideRole($(el));
                            }).bind(this));

                            this.showRole(this.$block.find('.tpt-role-based-role').last());

                            this.$block.find('.tpt-role-based-adding-form__role-selector').find('[value=' + selectedRole + ']').remove();
                            this.$block.find('.tiered_price_rules_roles_to_delete').find('[value=' + selectedRole + ']').prop('selected', false);

                            $('.woocommerce-help-tip').tipTip({
                                'attribute': 'data-tip',
                                'fadeIn': 50,
                                'fadeOut': 50,
                                'delay': 200
                            });

                            this.triggerVariationCanBeUpdated();
                        } else {
                            response.error_message && alert(response.error_message);
                        }
                        this.unblock();
                    }).bind(this));
                }
            }

            this.triggerVariationCanBeUpdated = function () {

                if (!this.variationCanBeChangedAlreadyTriggered) {

                    this.$block
                        .closest('.woocommerce_variation')
                        .addClass('variation-needs-update');

                    jQuery('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
                    jQuery('#variable_product_options').trigger('woocommerce_variations_defaults_changed');

                    this.variationCanBeChangedAlreadyTriggered = true;
                }

            }
        };

        jQuery.each($('.tpt-role-based-block'), function (i, el) {
            (new RoleBasedBlock()).init(jQuery(el).attr('id'));
        });

        jQuery(document).on('woocommerce_variations_loaded', function ($) {
            jQuery.each(jQuery('.tpt-role-based-block'), function (i, el) {

                var $el = jQuery(el);

                if ($el.data('product-type') === 'variation') {
                    (new RoleBasedBlock()).init($el.attr('id'));
                }
            });
        });

        function recalculateIndexes(container) {

            var fieldsName = [
                'tiered_price_percent_quantity',
                'tiered_price_percent_discount',
                'tiered_price_fixed_quantity',
                'tiered_price_fixed_price'
            ];

            for (var key in fieldsName) {
                if (fieldsName.hasOwnProperty(key)) {
                    var name = fieldsName[key];

                    jQuery.each(jQuery(container.find('input[name^="' + name + '"]')), function (index, el) {
                        var currentName = jQuery(el).attr('name');

                        var newName = currentName.replace(/\[\d*\]$/, '[' + index + ']');

                        jQuery(el).attr('name', newName);
                    });
                }
            }

        }

    });

    $(document).on('woocommerce_variations_loaded woocommerce_variations_added', function () {
        // Handle dynamic appearance blocks
        setTimeout(function () {
            $.each($('.variable_roles_tiered_pricing'), function (i, el) {
                if ($(el).is(':checked')) {
                    $(el).closest('.data').find('.show_if_variable_roles_tiered_pricing').show();
                } else {
                    $(el).closest('.data').find('.show_if_variable_roles_tiered_pricing').hide();
                }
            });
        }, 1000);
    });

    $(document).on('change', '.variable_roles_tiered_pricing', function () {
        if ($(this).is(':checked')) {
            $(this).closest('.data').find('.show_if_variable_roles_tiered_pricing').show();
        } else {
            $(this).closest('.data').find('.show_if_variable_roles_tiered_pricing').hide();
        }

    });
})(jQuery);



jQuery(document).ready(function ($) {
    var TieredPricingSettings = function () {
        this.init = function () {
            this.prefix = 'tier_pricing_table_';

            this.$displayType = this.getRow('display_type');
            this.$displayAtCatalog = this.getRow('tiered_price_at_catalog');
            this.$showDiscountColumn = this.getRow('show_discount_column');
            this.$changePriceAtProductPage = this.getRow('tiered_price_at_product_page');
            this.$showSummary = this.getRow('display_summary');
            this.$summaryType = this.getRow('summary_type');

            this.$showSummary.on('change', (function () {

                this.$summaryType.trigger('change');

                if (this.$showSummary.attr('checked')) {
                    this.showRow(this.getRow('summary_type'));
                    this.showRow(this.getRow('summary_title'));
                    this.showRow(this.getRow('summary_position_hook'));
                } else {
                    this.hideRow(this.getRow('summary_type'));
                    this.hideRow(this.getRow('summary_title'));
                    this.hideRow(this.getRow('summary_position_hook'));
                }

            }).bind(this));

            this.$summaryType.on('change', (function () {

                if (this.$summaryType.val() === 'inline' && this.$showSummary.attr('checked')) {
                    this.showRow(this.getRow('summary_total_label'));
                    this.showRow(this.getRow('summary_each_label'));
                } else {
                    this.hideRow(this.getRow('summary_total_label'));
                    this.hideRow(this.getRow('summary_each_label'));
                }

            }).bind(this));

            this.$displayType.on('change', (function () {

                if (this.$displayType.val() === 'tooltip') {
                    this.hideRow(this.getRow('position_hook'));
                    this.hideRow(this.getRow('table_title'));
                    this.showRow(this.getRow('tooltip_color'));
                    this.showRow(this.getRow('tooltip_size'));
                    this.showRow(this.getRow('tooltip_border'));
                } else {
                    this.showRow(this.getRow('position_hook'));
                    this.showRow(this.getRow('table_title'));
                    this.hideRow(this.getRow('tooltip_size'));
                    this.hideRow(this.getRow('tooltip_color'));
                    this.hideRow(this.getRow('tooltip_border'));
                }

            }).bind(this));

            this.getRow('tiered_price_at_catalog_type').on('change', (function (e) {
                if ($(e.target).val() === 'lowest') {
                    this.showRow(this.getRow('lowest_prefix'));
                } else {
                    this.hideRow(this.getRow('lowest_prefix'));
                }
            }).bind(this));

            this.$displayAtCatalog.on('change', (function () {
                if (this.$displayAtCatalog.attr('checked')) {
                    this.showRow(this.getRow('lowest_prefix'));
                    this.showRow(this.getRow('tiered_price_at_catalog_type'));
                    this.showRow(this.getRow('tiered_price_at_catalog_for_variable'));
                    this.showRow(this.getRow('tiered_price_at_product_page'));
                } else {
                    this.hideRow(this.getRow('tiered_price_at_catalog_type'));
                    this.hideRow(this.getRow('tiered_price_at_catalog_for_variable'));
                    this.hideRow(this.getRow('tiered_price_at_product_page'));
                    this.hideRow(this.getRow('lowest_prefix'));
                }
            }).bind(this));

            this.$showDiscountColumn.on('change', (function () {
                if (this.$showDiscountColumn.attr('checked')) {
                    this.showRow(this.getRow('head_discount_text'));
                } else {
                    this.hideRow(this.getRow('head_discount_text'));
                }
            }).bind(this));


            this.$changePriceAtProductPage.on('change', (function () {
                if (this.$changePriceAtProductPage.attr('checked')) {
                    this.hideRow(this.getRow('show_total_price'));
                } else {
                    this.showRow(this.getRow('show_total_price'));
                }
            }).bind(this));

            this.$displayType.trigger('change');
            this.$displayAtCatalog.trigger('change');
            this.$showDiscountColumn.trigger('change');
            this.$changePriceAtProductPage.trigger('change');
            this.$showSummary.trigger('change');
            this.$summaryType.trigger('change');

            this.getRow('tiered_price_at_catalog_type').trigger('change');
        };

        this.showRow = function (el) {
            el.closest('tr').show(100);
        };

        this.hideRow = function (el) {
            el.closest('tr').hide(100);
        };

        this.getRow = function (settingsName) {
            return $('[name=' + this.prefix + settingsName + ']');
        }
    };

    var tieredPricingSettings = new TieredPricingSettings();

    tieredPricingSettings.init();
});
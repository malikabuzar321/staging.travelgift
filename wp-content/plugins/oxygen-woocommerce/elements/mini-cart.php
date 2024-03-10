<?php

namespace Oxygen\WooElements;

class MiniCart extends \OxyWooEl {

    function name() {
        return 'Mini Cart';
    }

    function enableFullPresets() {
        return true;
    }

    function woo_button_place() {
        return "other";
    }

    function init() {
        add_filter( 'woocommerce_add_to_cart_fragments', array($this,'add_to_cart_woo_ajax_callback') );
    }

    function afterInit() {
        $this->removeApplyParamsButton();
    }

    function icon() {
        return plugin_dir_url(__FILE__) . 'assets/'.basename(__FILE__, '.php').'.svg';
    }

    function render($options, $defaults, $content) {
        if (function_exists("WC") && isset(WC()->cart)) {

            // echo $flyout_full_width_css;

            $oxyCartTotal = WC()->cart->get_total();

            $cartIcon = isset( $options['cart_icon'] ) ? esc_attr($options['cart_icon']) : "";
            global $oxygen_svg_icons_to_load;
            $oxygen_svg_icons_to_load[] = $cartIcon;
            $oxyCartItemCount = WC()->cart->get_cart_contents_count();

            if( $options['show_flyout_in_preview'] === 'show' ) { 
                ?>
                    <style>
                    #ct-builder .oxy-woo-mini-cart__flyout {
                        opacity: 1 !important;
                    }
                    </style>
                <?php
            }

            ?>
                <div class='oxy-woo-mini-cart__summary'>            
                    <div class='oxy-woo-mini-cart__quantity-badge'>
                        <?=WC()->cart->get_cart_contents_count();?>
                    </div>        
                    <div class='oxy-woo-mini-cart__summary-fragments'>
                        <span class='oxy-woo-mini-cart__total'><?php echo $oxyCartTotal; ?></span>
                        <span class='oxy-woo-mini-cart__items-count'>
                            <?php
                            /* translators: %s: The number of items in the Oxygen WooCommerce mini cart. */
                            printf( _n( '%s item', '%s items', $oxyCartItemCount, 'woocommerce' ), $oxyCartItemCount );
                            ?>
                        </span>
                    </div>
                    <svg class='oxy-woo-mini-cart__icon' viewBox="0 0 25 28">
                        <use xlink:href="#<?php echo $cartIcon; ?>"></use>
                    </svg>
                </div>
                <div class="oxy-woo-mini-cart__flyout">
                <div class="oxy-woo-mini-cart__flyout-fragments">
                <?php
                defined( 'ABSPATH' ) || exit;
                woocommerce_mini_cart();
                ?>
                </div>
                </div>
            <?php

        }

        $this->El->footerJS(
            
            "
            var miniCartDocReady = (callback) => {
                if (document.readyState != 'loading') callback();
                else document.addEventListener('DOMContentLoaded', callback);
              }
              
              miniCartDocReady(() => { 
                calculateMinicartFlyoutPosition();
            });

            document.querySelectorAll('.oxy-woo-mini-cart__summary').forEach( (minicart) => {
                minicart.addEventListener('mouseover', () => { calculateMinicartFlyoutPosition() });
                minicart.addEventListener('click', () => { calculateMinicartFlyoutPosition() });
            });

            function calculateMinicartFlyoutPosition() {
                document.querySelectorAll('.oxy-woo-mini-cart__flyout').forEach( (flyout) => {

                    var rect = flyout.getBoundingClientRect();
                    var vWidth = window.innerWidth;

                    if( rect.left < 0 ) {
                        flyout.style.transform = 'translateX(' + Math.abs(rect.left) + 'px)';
                    }
                });
            }
            "
        );

    }

    function defaultCSS() {

        return file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.css');

    }

    function controls() {
        global $media_queries_list;

        // Set up all sections.

        $summary_section = $this->addControlSection('summary', __('Summary'), 'assets/icon.png', $this);

        $summary_icon_section = $summary_section->addControlSection( 'summary-icon', __('Icon'), 'assets/icon.png', $this );
        $summary_size_spacing_section = $summary_section->addControlSection('summary-size-spacing', __('Size & Spacing'), 'assets/icon.png', $this);
        $summary_border_section = $summary_section->addControlSection('summary-borders', __('Borders'), 'assets/icon.png', $this);

        $flyout_section = $this->addControlSection('flyout', __('Flyout'), 'assets/icon.png', $this);

        // Root controls
        $show_flyout_in_preview = $this->addOptionControl( array(
            'type' => 'buttons-list', 
            'slug' => 'show_flyout_in_preview', 
            'name' => __('Flyout Preview'),
            'default' => 'hide'
            )
        );
        $show_flyout_in_preview->setValue( array( 'hide', 'show' ) );
        $show_flyout_in_preview->rebuildElementOnChange();

        // Add style controls directly in Summary section
        $show_price_and_quantity = $summary_section->addOptionControl(
            array(
                'type' => 'checkbox',
                'name' => __('Show Price & Quantity'),
                'slug' => 'show_price_and_quantity',
                'default' => 'true'
            )
        )->rebuildElementOnChange();
        $show_price_and_quantity->setValueCSS(
            array(
                'true' => '.oxy-woo-mini-cart__quantity-badge { display: none; }',
                'false' => '.oxy-woo-mini-cart__summary-fragments { display: none; }
                            .oxy-woo-mini-cart__quantity-badge { display: flex; }'
            )
        );
        $show_price_and_quantity->whitelist();

        $quantity_badge_color = $summary_section->addStyleControl(
            array(
                'name' => __('Quantity Badge Color'),
                'property' => 'background-color',
                'selector' => '.oxy-woo-mini-cart__quantity-badge'
            )
        );
        $quantity_badge_color->setCondition('show_price_and_quantity=false');

        $quantity_badge_text_color = $summary_section->addStyleControl(
            array(
                'name' => __('Quantity Badge Text Color'),
                'property' => 'color',
                'selector' => '.oxy-woo-mini-cart__quantity-badge'
            )
        );
        $quantity_badge_text_color->setCondition('show_price_and_quantity=false');

        $summary_section->addStyleControls(
            array(
                array(
                    'name' => __('Quantity Margin Right'),
                    'property' => 'margin-right',
                    'selector' => '.oxy-woo-mini-cart__summary-fragments',
                    'condition' => 'show_price_and_quantity=true'
                )
            )
        );

        $summary_section->addStyleControls(
            array(
                array(
                    'name'     => __('Background Color'),
                    'property' => 'background-color',
                    'selector' => '.oxy-woo-mini-cart__summary'
                )
            )
        );

        // Add icon finder to Summary > Icon section

        $summary_icon_section->addStyleControl(
            array(
                'name' => 'Icon Color',
                'property' => 'fill',
                'selector' => '.oxy-woo-mini-cart__icon',
                'control_type' => 'colorpicker'
            )
        );

        $summary_icon_section->addStyleControl(
            array(
                'name' => 'Size',
                'property' => 'width',
                'selector' => '.oxy-woo-mini-cart__icon',
                'control_type' => 'measurebox',
                'unit' => 'px'
            )
        );
        
        $summary_icon_section->addOptionControl(
            array(
                'type' => 'icon_finder',
                'name' => __('Cart Icon'),
                'slug' => 'cart_icon',
                'default' => 'FontAwesomeicon-shopping-cart'
            )
        )->rebuildElementOnChange();

        // Add Summary > Price Typography & quantity Typography preset sections

        $summary_price_typography_section = $summary_section->typographySection(
            __("Price Typography"),
            ".oxy-woo-mini-cart__summary .woocommerce-Price-amount",
            $this
        );

        $summary_quantity_typography_section = $summary_section->typographySection(
            __("Quantity Typography"),
            ".oxy-woo-mini-cart__items-count",
            $this
        );

        // Add Summary > Size & Spacing > Padding preset

        $summary_size_spacing_section->addPreset(
            'padding_api',
            'summary_padding',
            __('Padding'),
            '.oxy-woo-mini-cart__summary'
        )->whitelist();

        // Add Summary > Size & Spacing > Margin preset

        $summary_size_spacing_section->addPreset(
            'margin',
            'summary_size',
            __('Margin'),
            ' '
        )->whitelist();

        // Add Summary > Borders > Border preset

        $summary_border_section->addPreset(
            'border_api',
            'summary_borders',
            __('Borders'),
            '.oxy-woo-mini-cart__summary'
        )->whitelist();

        // Add Summary > Borders > Border Radius preset

        $summary_border_section->addPreset(
            'border-radius',
            'summary_border_radius',
            __('Border Radius'),
            '.oxy-woo-mini-cart__summary, .oxy-woo-mini-cart__flyout'
        )->whitelist();

        $flyout_full_width = $flyout_section->addOptionControl( array(
            'type' => 'buttons-list',
            'name' => __('Full Width'),
            'value' => array('disable', 'enable'),
            'slug' => 'flyout_full_width'
        ) )->rebuildElementOnChange();
        $flyout_full_width->whitelist();

        $flyout_full_width->setValueCSS(
            array(
                'disable' => '.oxy-woo-mini-cart__flyout { position: absolute; }',
                'enable' => '.oxy-woo-mini-cart__flyout {
                    width: 100vw;
                    right: 0; 
                    left: unset; }
                }'
            )
        );
        
        $flyout_section->addStyleControls(
            array(
                array(
                    'name'     => __('Background Color'),
                    'property' => 'background-color',
                    'selector' => '.oxy-woo-mini-cart__flyout'
                ),
                array(
                    'name'     => __('Width'),
                    'property' => 'min-width',
                    'selector' => '.oxy-woo-mini-cart__flyout',
                    'default' => '300'
                )
            )
        );

        $flyout_position = $flyout_section->addControl( 'buttons-list', 'flyout_position', __('Position') );
        $flyout_position->setValue( array( 'left', 'center', 'right' ) );
        $flyout_position->setValueCSS(
            array(
                'left'      => '.oxy-woo-mini-cart__flyout { right: 0; left: unset; }',
                'center'    => '.oxy-woo-mini-cart__flyout { left: unset; right: unset; }',
                'right'     => '.oxy-woo-mini-cart__flyout { left: 0; right: unset; }'
            )
        );
        $flyout_position->setCondition('flyout_full_width=disable');
        $flyout_position->setDefaultValue('left');
        $flyout_position->whitelist();

        // Padding control, all 4 sides.
        $padding = $flyout_section->addStyleControl(
            array(
                'name' => __('Padding'),
                'property' => 'padding-top|padding-right|padding-bottom|padding-left',
                'selector' => '.oxy-woo-mini-cart__flyout',
                'control_type' => 'slider-measurebox',
            )
        );

        $padding->setUnits('px', 'px');
        $padding->setRange(0, 500, 1);

        $flyout_box_shadow_section = $flyout_section->boxShadowSection(
            __("Box Shadow"),
            '.oxy-woo-mini-cart__flyout',
            $this);

        $flyout_link_typography_section = $flyout_section->typographySection(
            __("Link Typography"),
            '.oxy-woo-mini-cart__flyout .mini_cart_item a',
            $this
        );

        $flyout_link_typography_section = $flyout_section->typographySection(
            __("Other Typography"),
            '.oxy-woo-mini-cart__flyout .woocommerce-mini-cart__total strong, .oxy-woo-mini-cart__flyout .woocommerce-Price-amount, .oxy-woo-mini-cart__flyout .quantity, .oxy-woo-mini-cart__flyout .woocommerce-mini-cart__empty-message',
            $this
        );

    }

    function add_to_cart_woo_ajax_callback( $fragments ) {

        $oxyCartTotal = WC()->cart->get_total();
        $oxyCartItemCount = WC()->cart->get_cart_contents_count();

        ob_start();

        ?>
        
        <div class='oxy-woo-mini-cart__summary-fragments'>
                    <span class='oxy-woo-mini-cart__total'><?php echo $oxyCartTotal; ?></span>
                    <span class='oxy-woo-mini-cart__items-count'>
                            <?php
                            /* translators: %s: The number of items in the Oxygen WooCommerce mini cart. */
                            printf( _n( '%s item', '%s items', $oxyCartItemCount, 'woocommerce' ), $oxyCartItemCount );
                            ?>
                    </span>
        </div>

        <?php

        $fragments['.oxy-woo-mini-cart__summary-fragments'] = ob_get_clean();

        ob_start();

        ?>
        <div class='oxy-woo-mini-cart__flyout-fragments'>
            <?php
            defined( 'ABSPATH' ) || exit;
            woocommerce_mini_cart();
            ?>
        </div>
        <?php

        $fragments['.oxy-woo-mini-cart__flyout-fragments'] = ob_get_clean();

        ob_start();

        ?>
        <div class='oxy-woo-mini-cart__quantity-badge'>
            <?=WC()->cart->get_cart_contents_count();?>
        </div>
        <?php

        $fragments['.oxy-woo-mini-cart__quantity-badge'] = ob_get_clean();

        return $fragments;
    }

}

new MiniCart();
<?php

namespace Oxygen\WooElements;

class ProductUpsells extends \OxyWooEl {

    function name() {
        return 'Product Upsells';
    }

    function woo_button_place() {
        return "single";
    }

    function icon() {
        return plugin_dir_url(__FILE__) . 'assets/'.basename(__FILE__, '.php').'.svg';
    }

    function wooTemplate() {
        return 'woocommerce_upsell_display';
    }

    function render($options, $defaults, $content) {
		
		$title = apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;' ) );
		$title_tag = $options['title_tag'] ?? 'h2';
        $products_title_tag = $options['products_title_tag'] ?? 'h2';
		// make sure nothing dangerous is injected as part of the HTML tag
	    $title_tag = preg_replace("/[^a-zA-Z0-9]+/", "", $title_tag);
        $products_title_tag = preg_replace("/[^a-zA-Z0-9]+/", "", $products_title_tag);
		
		// disable rendering the default woocommerce title markup
		add_filter('woocommerce_product_upsells_products_heading', '__return_false');

		// render our own title
		if ( $title ) {
			echo '<' . $title_tag . '>' . $title . '</' . $title_tag . '>';
		}

        // Remove the default WooCommerce action that renders individual product titles with H2 tag
        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        // our own title
		$single_product_title = function () use ($products_title_tag) {
            echo '<' . $products_title_tag . ' class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">' . get_the_title() . '</' . $products_title_tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        };
        add_action( 'woocommerce_shop_loop_item_title', $single_product_title, 10 );
		
        global $product;
        $product = wc_get_product();

        if ($product != false) {
            call_user_func($this->wooTemplate());
        }
		
		// If the woo template is used from another plugin or user custom code and somehow rendered after ours, they'll need the original title
	    remove_filter('woocommerce_product_upsells_products_heading', '__return_false');

        // restore the original action for individual product titles
        remove_action( 'woocommerce_shop_loop_item_title', $single_product_title, 10 );
        add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
    }

    function controls() {


        /* Layout */
        $layout_section = $this->addControlSection("layout", __("Layout"), "assets/icon.png", $this);

        $layout_section->addPreset(
            "padding",
            "columns_inner_padding",
            __("Columns Inner Padding"),
            'li.product'
        );

        $items_align = $layout_section->addControl("buttons-list", "items_align", __("Items Align") );
        
		$items_align->setValue( array(
			"left"		=> "Left",
			"center" 	=> "Center", 
			"right" 		=> "Right" ) 
        );
        
		$items_align->setValueCSS( array(

            "left" => "
                .woocommerce-loop-product__link {
                align-items: flex-start;
                text-align: left;
            }
            ",
            
            "center" => "
                .woocommerce-loop-product__link {
                align-items: center;
                text-align: center;
            }
            ",

            "right" => "
                .woocommerce-loop-product__link {
                align-items: flex-end;
                text-align: right;
            }
            "
            )
        );

        $items_columns = $layout_section->addControl("buttons-list", "items_columns", __("Columns") );
        
		$items_columns->setValue( array(
            "one"       => "One",
			"two"		=> "Two",
			"three" 	=> "Three", 
            "four" 		=> "Four",
            "five" 		=> "Five",
             ) 
        );

        $items_columns->setDefaultValue("four");
        
		$items_columns->setValueCSS( array(

            "one" => "
            li.product {
            width: 100%;
            }
            ",

            "two" => "
                li.product {
                width: 50%;
            }
            ",
            
            "three" => "
                li.product {
                width: 33.33%;
            }
            ",

            "four" => "
                li.product {
                width: 25%;
            }
            ",

            "five" => "
                li.product {
                width: 20%;
            }
            "
            )
        );

        $items_columns->whiteList();


        /* Title */
        $title = $this->typographySection(
            __("Title"),
	        "h1:not(.woocommerce-loop-product__title),h2:not(.woocommerce-loop-product__title),h3:not(.woocommerce-loop-product__title),h4:not(.woocommerce-loop-product__title),h5:not(.woocommerce-loop-product__title),h6:not(.woocommerce-loop-product__title)",
            $this
        );

        $title->addOptionControl(
            array(
                "type" => "dropdown",
                "name" => "Title Tag",
                "slug" => "title_tag"
            )
        )->setDefaultValue('h2')->setValue( array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        ) )->rebuildElementOnChange();

        /* Sales Badges */

        $sales_badge = $this->addControlSection("sales_badge", __("Sales Badge"), "assets/icon.png", $this);
        $sales_badge_selector = "ul.products li.product .onsale, span.onsale";
        $sales_badge->addStyleControls(
             array(
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'background-color',
                ),
                array(
                    "name" => __('Top Offset'),
                    "selector" => $sales_badge_selector,
                    "property" => 'top',
                ),
                array(
                    "name" => __('Left Offset'),
                    "selector" => $sales_badge_selector,
                    "property" => 'left',
                ),
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'font-size',
                ),
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'font-family',
                ),
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'line-height',
                ),
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'border-radius',
                ),
                array(
                    "selector" => $sales_badge_selector,
                    "property" => 'text-transform',
                )
            )
        );

        /* Images */
        $product_images = $this->addControlSection("categories_images", __("Images"), "assets/icon.png", $this);
        $product_images_selector = 'img.attachment-woocommerce_thumbnail';

        $product_images->borderSection(
            __("Borders"),
            $product_images_selector,
            $this
        );
        
        $product_images->boxShadowSection(
            __("Box Shadow"),
            $product_images_selector,
            $this
        );

        /* Headings */
        $product_heading = $this->typographySection(
            __("Links"),
            ".woocommerce-loop-product__title",
            $this
        );

        $product_heading->addStyleControl(
            array(
                "name" => __('Hover Color'),
                "selector" => '.woocommerce-loop-product__title:hover',
                "property" => 'color',
            )
        );

        $product_heading->addOptionControl(
            array(
                "type" => "dropdown",
                "name" => "Products Title Tag",
                "slug" => "products_title_tag"
            )
        )->setDefaultValue('h2')->setValue( array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        ) )->rebuildElementOnChange();

        /* Stars */
        $stars_section = $this->addControlSection("stars", __("Stars"), "assets/icon.png", $this);
        $stars_section->addStyleControls(
        	array(
        		array(
	                "name" => __('Stars Size'),
	                "selector" => ".star-rating",
	                "property" => 'font-size',
	            ),
	            array(
	                "name" => __('Filled Stars Color'),
	                "selector" => ".star-rating span",
	                "property" => 'color',
	            ),
	            array(
	                "name" => __('Empty Stars Color'),
	                "selector" => ".star-rating::before",
	                "property" => 'color',
	            ),
        	)
        );

        /* Price */
        $price_section = $this->addControlSection("price_section", __("Price"), "assets/icon.png", $this);

        $price_typography = $price_section->typographySection(__("Current Price"),'.price, .price span', $this);
        $strikethrough_section = $price_section->typographySection(__("Strikethrough Price"),'.price del span, ul.products li.product .price del', $this);
            

        /* Add to Cart Button */
        $submit_section = $this->addControlSection("submit_section", __("Main Buttons"), "assets/icon.png", $this);
        $submit_selector = 'a.button';

        $submit_section->addPreset(
            "padding",
            "submit_padding",
            __("Button Paddings"),
            $submit_selector
        );

        $submit_section->addStyleControls(
            array(
                array(
                    "name" => 'Background Color',
                    "selector" => $submit_selector,
                    "property" => 'background-color',
                ),
                array(
                    "name" => 'Background Hover Color',
                    "selector" => $submit_selector.":hover",
                    "property" => 'background-color',
                )
            )
        );

        $submit_section->typographySection(
            __("Typography"),
            $submit_selector,
            $this
        );

        $submit_section->typographySection(
            __("Hover Typography"),
            $submit_selector.":hover",
            $this
        );

        $submit_section->borderSection(
            __("Borders"),
            $submit_selector,
            $this
        );

        $submit_section->borderSection(
            __("Hover Borders"),
            $submit_selector.":hover",
            $this
        );

        $submit_section->boxShadowSection(
            __("Shadow"),
            $submit_selector,
            $this
        );

        $submit_section->boxShadowSection(
            __("Hover Shadow"),
            $submit_selector.":hover",
            $this
        );

        /* View Cart Button */
        $view_section = $this->addControlSection("view_section", __("View Cart Buttons"), "assets/icon.png", $this);
        $view_selector = '.added_to_cart';

        $view_section->addPreset(
            "padding",
            "view_padding",
            __("Button Paddings"),
            $view_selector
        );

        $view_section->addStyleControls(
            array(
                array(
                    "name" => 'Background Color',
                    "selector" => $view_selector,
                    "property" => 'background-color',
                ),
                array(
                    "name" => 'Background Hover Color',
                    "selector" => $view_selector.":hover",
                    "property" => 'background-color',
                )
            )
        );

        $view_section->typographySection(
            __("Typography"),
            $view_selector,
            $this
        );

        $view_section->typographySection(
            __("Hover Typography"),
            $view_selector.":hover",
            $this
        );

        $view_section->borderSection(
            __("Borders"),
            $view_selector,
            $this
        );

        $view_section->borderSection(
            __("Hover Borders"),
            $view_selector.":hover",
            $this
        );

        $view_section->boxShadowSection(
            __("Shadow"),
            $view_selector,
            $this
        );

        $view_section->boxShadowSection(
            __("Hover Shadow"),
            $view_selector.":hover",
            $this
        );

    }

    function defaultCSS() {
        return file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.css');
    }

}

new ProductUpsells();

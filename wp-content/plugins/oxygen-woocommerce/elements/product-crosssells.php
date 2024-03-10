<?php

namespace Oxygen\WooElements;

class ProductCrossSells extends \OxyWooEl {

    function name() {
        return 'Product Cross Sells';
    }

    function woo_button_place() {
        return "single";
    }

    function wooTemplate() {
        return 'woocommerce_cross_sell_display';
    }
    
    function icon() {
        return plugin_dir_url(__FILE__) . 'assets/'.basename(__FILE__, '.php').'.svg';
    }

    function render($options, $defaults, $content) {
        global $wp_query;
        
        $title = apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;' ) );
        $title_tag = $options['title_tag'] ?? 'h2';
        $title_tag = preg_replace("/[^a-zA-Z0-9]+/", "", $title_tag);

        $items_amount = intval( $options['items_amount'] ?? 4 );

        // we need to pass the amount of columns to the woocommerce template
        // to properly render the class name in frontend
        $columns = $options['items_columns'] ?? 'two';
        switch( $columns ){
            case 'one':
            $columns = 1;
            break;
            case 'two':
            $columns = 2;
            break;
            case 'three':
            $columns = 3;
            break;
            case 'four':
            $columns = 4;
            break;
            case 'five':
            $columns = 5;
            break;
        }

        $crosssells = get_post_meta( get_the_ID(), '_crosssell_ids',true);

        if(empty($crosssells)){
            return;
        }

        $args = array( 
            'post_type' => 'product', 
            'posts_per_page' => $items_amount, 
            'post__in' => $crosssells 
            );
        $products = new \WP_Query( $args );
        if( $products->have_posts() ) :
            // render our own title
            if ( $title ) {
                echo '<' . $title_tag . '>' . $title . '</' . $title_tag . '>';
            }
            wc_set_loop_prop( 'columns', $columns );
            woocommerce_product_loop_start();
            while ( $products->have_posts() ) : $products->the_post();
                wc_get_template_part( 'content', 'product' );
            endwhile;
            woocommerce_product_loop_end();
        endif;
        
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

        $items_amount = $layout_section->addControl("textfield", "items_amount", __("Max Items") )->setValue('2');

        $items_columns = $layout_section->addControl("buttons-list", "items_columns", __("Columns") );
        
        $items_columns->setValue( array(
            "one"       => "One",
            "two"		=> "Two",
            "three" 	=> "Three", 
            "four" 		=> "Four",
            "five" 		=> "Five",
                ) 
        );

        $items_columns->setDefaultValue("two");
        
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
            ">h1,>h2,>h3,>h4,>h5,>h6",
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

}

new ProductCrossSells();

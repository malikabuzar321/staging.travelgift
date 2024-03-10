<?php

namespace Oxygen\WooElements;

class ArchiveProducts extends \OxyWooEl {

   public $options;

   var $query_params = array(
            'columns' => "",
            'paginate' => "",
            'limit' => "",
            'orderby' => "",
            'order' => "",
            'category' => "",
            'cat_operator' => "",
            'woo_tag' => "",
            'tag_operator' => "",
            'on_sale' => "",
            'best_selling' => "",
            'top_rated' => "",
            'ids' => "",
            'skus' => "",
            'attribute' => "",
            'terms' => "",
            'terms_operator' => "",
            'visibility' => "",
        );

    function custom_init() {
        add_filter("woocommerce_product_add_to_cart_text", array($this, "add_to_cart_text_filter_callback" ), 10, 2);
        add_action("wp_footer", array($this, 'wp_enqueue_scripts_callback') );

        // Hide default WooCo page title so we can output our own
        add_filter( 'woocommerce_show_page_title', array( $this, 'oxy_woocommerce_show_page_title' ) );

    }

    function name() {
        return 'Products List';
    }

    function slug() {
        return "woo-products";
    }

    function woo_button_place() {
        return "archive";
    }

    function icon() {
        return plugin_dir_url(__FILE__) . 'assets/'.basename(__FILE__, '.php').'.svg';
    }
    
    function render($options, $defaults, $content) {

        // Unhook default WooCommerce heading for individual product listings and output our own so we can customize the h2 tag.
        remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        add_action( 'woocommerce_shop_loop_item_title', array( $this, 'oxy_woocommerce_template_loop_product_title' ), 10 );
    	
        $shortcode_props = shortcode_atts($this->query_params, $options);

		$atts_string = '';

		foreach ($shortcode_props as $name => $value) {
            // Avoid collision with built-in 'tag' option, which was resulting in tag attribute having value of 'div'
            if( $name == 'woo_tag' ) {
                $name = 'tag';
            }
			if ($value) {
				$atts_string .= " {$name}=\"{$value}\"";
			}
		}

        $this->options = $options;

        $shortcode = "[products {$atts_string}]";

        if( $options['hide_page_title'] !== 'true' ) {
            $page_title = woocommerce_page_title(false);
            echo "<{$this->options['page_title_tag']} class='page-title'>{$page_title}</{$this->options['page_title_tag']}>";
        }
        
        if ($options['query_type'] == 'custom') {
            echo do_shortcode($shortcode);
        } else {
            woocommerce_content();    
        }

        // Unhook default WooCommerce heading for individual product listings and output our own so we can customize the h2 tag.
        add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
        remove_action( 'woocommerce_shop_loop_item_title', array( $this, 'oxy_woocommerce_template_loop_product_title' ), 10 );

	}
	
    function controls() {

        /*
         * Products Query Section
         */

        $products_query = $this->addControlSection("products_query", __("Products Query"), "assets/icon.png", $this);

        $products_query->addOptionControl(
            array(
                "type" => 'buttons-list',
                "name" => __("Query Type"),
                "slug" => 'query_type',
            )
        )->setValue(array('default', 'custom'));

        $query_params = array_keys($this->query_params);
        $query_params = array_map(function($value) {
            return $this->El->prefix_option($value);
        }, $query_params);
        $query_params = implode("','",$query_params);

        $reset_query_control = $products_query->addCustomControl(
            '<div class="oxygen-control-wrapper">
                <div class="oxygen-widget-settings-apply-button"
                    ng-click="iframeScope.unsetOptions([\''.$query_params.'\'])">'.
                        __("Reset Custom Query", "oxygen")
                    .'</div>
                </div>');
        $reset_query_control->setCondition("query_type=custom");

		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
				"name" => 'Limit',
				"slug" => 'limit',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'Columns',
                "slug" => 'columns',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Paginate',
                "slug" => 'paginate',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('true', 'false'));


		$products_query->addOptionControl(
			array(
                "type" => 'dropdown',
                "name" => 'Order By',
                "slug" => 'orderby',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('date', 'id', 'menu_order', 'popularity', 'rand', 'rating', 'title'));

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Order',
                "slug" => 'order',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('ASC', 'DESC'));

		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'Category',
                "slug" => 'category',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Cat Operator',
                "slug" => 'cat_operator',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('AND', 'IN', 'NOT IN'));

        $products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'Tag',
                "slug" => 'woo_tag',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Tag Operator',
                "slug" => 'tag_operator',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('AND', 'IN', 'NOT IN'));

		
		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'On Sale Only',
                "slug" => 'on_sale',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('true', 'false'))->setDefaultValue('false');

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Best Selling',
                "slug" => 'best_selling',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('true', 'false'))->setDefaultValue('false');

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Top Rated',
                "slug" => 'top_rated',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('true', 'false'))->setDefaultValue('false');


		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'IDs',
                "slug" => 'ids',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'SKUs',
                "slug" => 'skus',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'Attribute',
                "slug" => 'attribute',
                "condition" => 'query_type=custom',
            )
		);
        
        $products_query->addOptionControl(
			array(
                "type" => 'textfield',
                "name" => 'Terms',
                "slug" => 'terms',
                "condition" => 'query_type=custom',
            )
		);

		$products_query->addOptionControl(
			array(
                "type" => 'buttons-list',
                "name" => 'Terms Operator',
                "slug" => 'terms_operator',
                "condition" => 'query_type=custom',
            )
		)->setValue(array('AND', 'IN', 'NOT IN'));

		$products_query->addOptionControl(
			array(
                "type" => 'dropdown',
                "name" => 'Visibility',
                "slug" => 'visibility',
                "condition" => 'query_type=custom',
            )
        )->setValue(array('visible', 'catalog', 'search', 'hidden', 'featured'));

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
             "one"      => "One",
             "two"		=> "Two",
             "three" 	=> "Three", 
             "four" 		=> "Four",
             "five" 		=> "Five",
              ) 
         );
         
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

        /*---Heading Typography---*/

        $headings_section = $this->typographySection(
            __("Heading"),
            "h1.page-title,h2.page-title,h3.page-title,h4.page-title,h5.page-title,h6.page-title",
            $this
        );

        $headings_section->addOptionControl(
            array(
                "type" => "dropdown",
                "name" => __("Page Title Tag"),
                "slug" => "page_title_tag"
            )
        )->setDefaultValue('h1')->setValue( array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        ) )->rebuildElementOnChange();

        $headings_section->addOptionControl(
            array(
                "type" => "checkbox",
                "name" => __("Hide Page Title"),
                "slug" => "hide_page_title"
            )
        )->setDefaultValue('false')->rebuildElementOnChange();

        /*---Description Typography---*/
        $description_section = $this->typographySection(
            __("Description"),
            ".term-description",
            $this
        );

        $description_section->addOptionControl(
            array(
                "type" => "checkbox",
                "name" => __("Hide Description"),
                "slug" => "hide_description"
            )
        )->setDefaultValue('false')->setValueCSS(
            array(
                'true' => '.term-description { display: none; }',
                'false' => '.sandwich'
            )
        );

        /*---Results Typography ---*/

        $results_section = $this->typographySection(
            __("Results Count"),
            ".woocommerce-result-count",
            $this
        );

        /**
        * Sorting Select
        */

        $sorting_select = $this->addControlSection("sorting_select", __("Sorting Select"), "assets/icon.png", $this);
        $sorting_select_selector = ".woocommerce-ordering select";

        $sorting_select->addPreset(
            "padding",
            "sorting_select_padding",
            __("Select Padding"),
            $sorting_select_selector
        );

        // typography sub-section
        $sorting_select->typographySection(
            __("Typography"),
            $sorting_select_selector,
            $this);

        // border sub-section
        $sorting_select->borderSection(
            __("Border"),
            $sorting_select_selector,
            $this);

        // border sub-section
        $sorting_select->borderSection(
            __("Focus Border"),
            $sorting_select_selector.":focus",
            $this);

        // box-shadow sub-section
        $sorting_select->boxShadowSection(
            __("Box Shadow"),
            $sorting_select_selector,
            $this);

        // box-shadow sub-section
        $sorting_select->boxShadowSection(
            __("Focus Box Shadow"),
            $sorting_select_selector.":focus",
            $this);

        

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
            $this,
            null,
            false //remove inset control
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
                "name" => "Product Title Tag",
                "slug" => "product_title_tag"
            )
        )->setDefaultValue('h2')->setValue( array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        ) );

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

        $submit_section->addControl("textfield", "add_to_cart_text",__("'Add To Cart' Text", "oxygen"));
        $submit_section->addControl("textfield", "read_more_text",__("'Read More' Text", "oxygen"));
        $submit_section->addControl("textfield", "view_products_text",__("'View Products' Text", "oxygen"));
        $submit_section->addControl("textfield", "select_options_text",__("'Select options' Text", "oxygen"));

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

        $view_section->addControl("textfield", "view_cart_text",__("'View Cart' Text", "oxygen"));

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

        /**
         * Pagination 
         */

        $pagination = $this->addControlSection("pagination", __("Pagination"), "assets/icon.png", $this);

        $pagination_align = $pagination->addControl("buttons-list", "pagination_align", __("Items Align") );
        
		$pagination_align->setValue( array(
			"left"		=> "Left",
			"center" 	=> "Center", 
			"right" 		=> "Right" ) 
        );
        
		$pagination_align->setValueCSS( array(

            "left" => "
                .woocommerce-pagination {
                align-items: flex-start;
                text-align: left;
            }
            ",
            
            "center" => "
                .woocommerce-pagination {
                align-items: center;
                text-align: center;
            }
            ",

            "right" => "
                .woocommerce-pagination {
                align-items: flex-end;
                text-align: right;
            }
            "
            )
        );

        $pagination->addStyleControls(
             array(
                array(
                    "selector" => ".woocommerce-pagination",
                    "property" => 'font-size',
                ),

                array(
                    "name" => __("Links Text Color"),
                    "selector" => "nav.woocommerce-pagination ul li a",
                    "property" => 'color',
                ),
                array(
                    "name" => __("Links Background"),
                    "selector" => "nav.woocommerce-pagination ul li a",
                    "property" => 'background-color',
                ),

                // hover
                array(
                    "name" => __("Hover Text Color"),
                    "selector" => "nav.woocommerce-pagination ul li a:hover",
                    "property" => 'color',
                ),
                array(
                    "name" => __("Hover Background"),
                    "selector" => "nav.woocommerce-pagination ul li a:hover",
                    "property" => 'background-color',
                ),

                // current
                array(
                    "name" => __("Current Text Color"),
                    "selector" => "nav.woocommerce-pagination ul li span.current",
                    "property" => 'color',
                ),
                array(
                    "name" => __("Current Background"),
                    "selector" => "nav.woocommerce-pagination ul li span.current",
                    "property" => 'background-color',
                ),

                array(
                    "selector" => "nav.woocommerce-pagination ul, nav.woocommerce-pagination ul li",
                    "property" => 'border-color',
                ),
                
                array(
                    "selector" => "nav.woocommerce-pagination ul",
                    "property" => 'border-radius',
                ),
            )
        );
	}


	function defaultCSS() {

        return file_get_contents(__DIR__.'/'.basename(__FILE__, '.php').'.css');

 
    }


    public function add_to_cart_text_filter_callback($text, $product) {

        $type = $product->get_type();
        
        switch ($type) {
        
            case 'grouped':
                return isset($this->options['view_products_text']) ? __($this->options['view_products_text']) : $text;
                break;

            case 'simple':
                return $product->is_purchasable() && $product->is_in_stock() 
                        ? 
                        (isset($this->options['add_to_cart_text']) ? __($this->options['add_to_cart_text']) : $text)
                        : 
                        (isset($this->options['read_more_text']) ? __($this->options['read_more_text']) : $text);
                break;

            case 'variable':
                return $product->is_purchasable() 
                ? 
                (isset($this->options['select_options_text']) ? __($this->options['select_options_text']) : $text) 
                : 
                (isset($this->options['read_more_text']) ? __($this->options['read_more_text']) : $text);
                break;

            default:
                return $text;
                break;
        }
    }

    public function wp_enqueue_scripts_callback() {

        if ( isset($this->options['view_cart_text']) && !empty($this->options['view_cart_text'])) {
            wp_add_inline_script("wc-add-to-cart", "
                wc_add_to_cart_params['i18n_view_cart'] = '".__($this->options['view_cart_text'])."';
            ");
        }

    }

    public function oxy_woocommerce_template_loop_product_title() {
		echo '<' . $this->options['product_title_tag'] . ' class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">' . get_the_title() . '</' . $this->options['product_title_tag'] . '>';
    }

    public function oxy_woocommerce_show_page_title() {
        return false;
    }
    
}

new ArchiveProducts();

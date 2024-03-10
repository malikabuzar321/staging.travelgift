<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cmitexperts.com/
 * @since      1.0.0
 *
 * @package    Gctcf
 * @subpackage Gctcf/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Gctcf
 * @subpackage Gctcf/public
 * @author     CMITEXPERTS TEAM <cmitexperts@gmail.com>
 */
class Gctcf_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gctcf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gctcf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style('bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css", array(), '4.6.2', 'all');
		wp_enqueue_style('gctcf-owl-theme', plugin_dir_url(__FILE__) . 'css/owl.theme.default.css', array(), $this->version, 'all');
		wp_enqueue_style('gctcf-owl-carousel', plugin_dir_url(__FILE__) . 'css/owl.carousel.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/gctcf-public.css', array(), '1.0.2', 'all');
		wp_enqueue_style('gctcf-main', plugin_dir_url(__FILE__) . 'css/gctcf-main.css', array(), '1.0.2', 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gctcf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gctcf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script('jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script('gctcf-ui-js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), $this->version, false);
		wp_enqueue_script('gctcf-owl', plugin_dir_url(__FILE__) . 'js/jquery.pajinate.js', array('jquery'), $this->version, true);
		
		wp_enqueue_script('gctcf-paginate', plugin_dir_url(__FILE__) . 'js/owl.carousel.min.js', array('jquery'), $this->version, true);

		wp_enqueue_script('fontawesome', 'https://kit.fontawesome.com/94f32119b6.js', array('jquery'), $this->version, true);
		
		wp_enqueue_script('gctcf-marquee', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.Marquee/1.5.0/jquery.marquee.min.js', array('jquery'), $this->version, false);

		wp_enqueue_script('gctcf-por-js', '//porjs.com/2189.js', array(), $this->version, true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/gctcf-public.js', array('jquery'), '1.0.3', true);


		wp_localize_script($this->plugin_name, 'gctcf', array('ajax' => admin_url('admin-ajax.php'), 'hostUrl' => site_url(), 'searchTimeout' => 0, 'prevSearch' => ''));
		wp_enqueue_script('gctcf-ajax', plugin_dir_url(__FILE__) . 'js/gctc-ajax.js', array('jquery'), '1.0.2', true);
		wp_enqueue_script('gctcf-attraction', plugin_dir_url(__FILE__) . 'js/attraction-form-validate.js', array('jquery'), '1.0.2', true);
		
		wp_localize_script('gctcf-ajax', 'gctcf_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'gctcf_siteurl' => site_url()));
		wp_enqueue_script('felloh', "https://sdk.felloh.com/", array('jquery'), '1.0.2', true);
		// wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com/', array('jquery'), $this->version, true);
		wp_enqueue_script('Bootstrap JS', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '4.6.2', true);

	}

	public function register_shortcodes()
	{
		add_shortcode('gctcf-testimonial', array($this, 'gctcf_testimonial_carousel'));
		add_shortcode('gctcf-top-hotels', array($this, 'gctcf_top_hotels_carousel'));
		add_shortcode('gctcf-search-form', array($this, 'gctcf_search_form'));
		add_shortcode('gctcf-transfers-search-form', array($this, 'gctcf_transfers_search_form'));
		add_shortcode('gctcf-tours', array($this, 'gctcf_tours_listing'));

		add_shortcode('gctcf-attraction', array($this, 'gctcf_attraction_file'));
		add_shortcode('gctcf-destinations', array($this, 'gctcf_destinations_file'));

		add_shortcode('gctcf-attraction-thank-you', array($this, 'gctcf_attraction_thank_you'));
	}


	public function gctcf_testimonial_posttype()
	{

		$labels = array(
			'name'                  => _x('Testimonials', 'gctcf'),
			'singular_name'         => _x('Testimonial', 'gctcf'),
			'menu_name'             => _x('Testimonials', 'gctcf'),
			'name_admin_bar'        => _x('Testimonial', 'gctcf'),
			'add_new'               => __('Add New', 'gctcf'),
			'add_new_item'          => __('Add New Testimonial', 'gctcf'),
			'new_item'              => __('New Testimonial', 'gctcf'),
			'edit_item'             => __('Edit Testimonial', 'gctcf'),
			'view_item'             => __('View Testimonial', 'gctcf'),
			'all_items'             => __('All Testimonials', 'gctcf'),
			'search_items'          => __('Search Testimonials', 'gctcf'),
			'parent_item_colon'     => __('Parent Testimonials:', 'gctcf'),
			'not_found'             => __('No Testimonials found.', 'gctcf'),
			'not_found_in_trash'    => __('No Testimonials found in Trash.', 'gctcf'),
			'featured_image'        => _x('Testimonial Image', 'gctcf'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'gctcf_testimonials'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'			 => 'dashicons-testimonial',
			'supports'           => array('title', 'editor', 'thumbnail'),
		);

		register_post_type('gctcf_testimonials', $args);


		$labels = array(
			'name'                => _x('Tours', 'Post Type General Name', 'gctcf'),
			'singular_name'       => _x('Tour', 'Post Type Singular Name', 'gctcf'),
			'menu_name'           => __('Tours', 'gctcf'),
			'parent_item_colon'   => __('Parent Tour', 'gctcf'),
			'all_items'           => __('All Tours', 'gctcf'),
			'view_item'           => __('View Tour', 'gctcf'),
			'add_new_item'        => __('Add New Tour', 'gctcf'),
			'add_new'             => __('Add New', 'gctcf'),
			'edit_item'           => __('Edit Tour', 'gctcf'),
			'update_item'         => __('Update Tour', 'gctcf'),
			'search_items'        => __('Search Tour', 'gctcf'),
			'not_found'           => __('Not Found', 'gctcf'),
			'not_found_in_trash'  => __('Not found in Trash', 'gctcf'),
		);

		// Set other options for Custom Post Type

		$args = array(
			'labels'              => $labels,
			'supports'            => array('title', 'editor', 'excerpt', 'thumbnail'),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 10,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'rewrite'           => array('slug' => 'tour'),
			'show_in_rest' => true,

		);

		register_post_type('tours', $args);

		$labels = array(
			'name'              => _x('Tours Category', 'taxonomy general name', 'gctcf'),
			'singular_name'     => _x('Tours Category', 'taxonomy singular name', 'gctcf'),
			'search_items'      => __('Search Tours Category', 'gctcf'),
			'all_items'         => __('All Tours Category', 'gctcf'),
			'view_item'         => __('View Tour Category', 'gctcf'),
			'parent_item'       => __('Parent Tour Category', 'gctcf'),
			'parent_item_colon' => __('Parent Tour: Category', 'gctcf'),
			'edit_item'         => __('Edit Tour Category', 'gctcf'),
			'update_item'       => __('Update Tour Category', 'gctcf'),
			'add_new_item'      => __('Add New Tour Category', 'gctcf'),
			'new_item_name'     => __('New Tour Name Category', 'gctcf'),
			'not_found'         => __('No Tours Found Category', 'gctcf'),
			'back_to_items'     => __('Back to Tours Category', 'gctcf'),
			'menu_name'         => __('Tour Category', 'gctcf'),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			//'rewrite'           => array( 'slug' => 'tours-category' ),
			'show_in_rest'      => true,
		);


		register_taxonomy('tours-category', 'tours', $args);
		//$sent =  wp_mail('developersuseonly@gmail.com', 'GIFTCARD', 'MESSAGE HERE');
		// var_dump($sent);


		$labels = array(
			'name'                  => _x('Attraction Bookings', 'gctcf'),
			'singular_name'         => _x('Attraction Bookings', 'gctcf'),
			'menu_name'             => _x('Attraction Bookings', 'gctcf'),
			'name_admin_bar'        => _x('Attraction Booking', 'gctcf'),
			'add_new'               => __('Add New', 'attraction booking'),
			'add_new_item'          => __('Add New attraction booking', 'gctcf'),
			'new_item'              => __('New attraction booking', 'gctcf'),
			'edit_item'             => __('Edit attraction booking', 'gctcf'),
			'view_item'             => __('View attraction booking', 'gctcf'),
			'all_items'             => __('All attraction bookings', 'gctcf'),
			'search_items'          => __('Search attraction bookings', 'gctcf'),
			'parent_item_colon'     => __('Parent attraction bookings:', 'gctcf'),
			'not_found'             => __('No attraction bookings found.', 'gctcf'),
			'not_found_in_trash'    => __('No attraction bookings found in Trash.', 'gctcf'),
		);
		$arg = array(
			'labels'             => $labels,
			'description'        => 'Attraction Booking custom post type.',
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'attraction_booking'),
			'capability_type'    => 'post',
			// 'capabilities' 		 => array(
			// 								'read ' 	=> false,
			// 							 ),
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'supports'           => array('title'),
			'show_in_rest'       => true
		);

		register_post_type('attraction_booking', $arg);
		
		$labels = array(
			'name' => _x('Transfer Bookings', 'Post type general name', 'gc4t'),
			'singular_name' => _x('Transfer Booking', 'Post type singular name', 'gc4t'),
			'menu_name' => _x('Transfer Bookings', 'Admin Menu Text', 'gc4t'),
			'name_admin_bar' => _x('Transfer Booking', 'Add New on Toolbar', 'gc4t'),
			'add_new' => __('Add New', 'gc4t'),
			'add_new_item' => __('Add New Booking', 'gc4t'),
			'new_item' => __('New Booking', 'gc4t'),
			'edit_item' => __('Edit Booking', 'gc4t'),
			'view_item' => __('View Booking', 'gc4t'),
			'all_items' => __('All Bookings', 'gc4t'),
			'search_items' => __('Search Bookings', 'gc4t'),
			'parent_item_colon' => __('Parent Booking:', 'gc4t'),
			'not_found' => __('No Bookings Found', 'gc4t'),
			'not_found_in_trash' => __('No Bookings Found In Trash', 'gc4t'),
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'public_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'transfer-bookings',
			),
			'capability_type' => 'post',
			'has_archive' => true,
			'hiercarchical' => false,
			'menu_position' => null,
			'supports' => array(
				'title',
				'author',
				'editor',
				'thumbnail',
				'custom-fields',
			)
		);

		register_post_type('transfer_booking', $args);
	}

	public function gctcf_testimonial_carousel()
	{
		$html = '';
		$args = array(
			'post_type' => 'gctcf_testimonials',
			'post_status' => 'publish',
			'posts_per_page' => 12,
			'orderby' => 'date',
			'order' => 'DESC'
		);
		$posts = get_posts($args);
		if ($posts) {
			$html .= '<div class="gctcf_testimonial-slider owl-carousel">';
			foreach ($posts as $post) {
				$html .= '<div class="gctcf_testimonial-outer item">
							<img src="' . plugin_dir_url(__FILE__) . 'images/quotes.png" alt="image" class="gctcf_testimonial-quote">
							<div class="he_testimonial-inner">
								<div class="gctcf_testimonial-text"><p>' . $post->post_content . '</p></div>
							  	<div class="gctcf_testimonial-details">
								  	<p>' . $post->post_title . '</p>
								  	<p>' . get_post_meta($post->ID, '_testimonial_designation', true) . '</p>
							  	</div>';
				if (has_post_thumbnail($post)) {
					$html .= '<div class="gctcf_testimonial-image">
								  		<img src="' . get_the_post_thumbnail_url($post->ID) . '" alt="image">
								  	</div>';
				}
				$html .= '</div>
						  </div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	public function gctcf_top_hotels_carousel()
	{
		$html = '<div class="gctcf-top-hotels owl-carousel">';

		$random_hotel_file = gc4t_get_popular_hotels_list(9);

		// pre($random_hotel_file); exit();
		//$hotels = '';
		ob_start();
		foreach ($random_hotel_file as $i) {
			$hotel_data = $i;
			//pre($hotel_data);
			include(GCTCF_PATH . 'public/partials/create_hotel_thumbs.php');
		}
		$html .= ob_get_clean();
		$html .= "</div>";

		return $html;
	}

	/**
	 * Register the shortcode to print hotels transfers and giftcards search form in tabs.
	 *
	 * @since    1.0.0
	 */
	public function gctcf_search_form()
	{
		$html = '';
		$def_tab = 'hotel';
		global $post;
		if ($post && isset($post->post_name) && $post->post_name == 'giftcards') {
			$def_tab = 'giftcards';
		}
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'shortcodes/hotels-transfers-cards-search.php';
		$html .= ob_get_clean();
		return $html;
	}

	// Shortcode for search form of transfers
	public function gctcf_transfers_search_form()
	{
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'shortcodes/transfers-search.php';
		return ob_get_clean();
	}

	public function gctcf_tours_listing()
	{
		$html = '';
		$terms = get_terms(array(
			'taxonomy' => 'tours-category',
			'hide_empty' => true
		));
		if ($terms) {
			foreach ($terms as $term) {
				$args = array(
					'post_type' => 'tours',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'tours-category',
							'field' => 'slug',
							'terms' => array($term->slug),
							'operator' => 'IN'
						)
					)
				);

				$html .= '<div class="gc-tour-row-outer">
								<h3>' . $term->name . '</h3>';
				$loop = new WP_Query($args);

				while ($loop->have_posts()) : $loop->the_post();

					$post_id = $loop->post->ID;
					$time = get_field('_time', $post_id);
					$regular_price = get_field('_regular_price', $post_id);
					$sales_price = get_field('_sales_price', $post_id);
					$img = GCTCF_URL . 'public/images/hotel-placeholder-img.png';
					if (get_the_post_thumbnail_url($post_id)) {
						$img = get_the_post_thumbnail_url($post_id);
					}
					$html .= '<div class="gc-col-6">
							    <div class="gc-tour-col-outer-inner">
							      <div class="gc-tour-col-content">
							        <div class="gc-tour-col-description"> <img src="' . $img . '" alt="image"> 
								        <div class="gc-tour-overlay-content">
								          <div class="gc-tour-overlay-left">';
					if ($time) :
						$html .= '<span><i class="fa fa-clock"></i>' . $time . '</span>';
					endif;
					$html .= '</div>
								          <div class="gc-tour-overlay-right">
								            <p class="gc-tour-price-offer">From<del>£' . $regular_price . '</del><span class="gc-tour-main-price">' . $sales_price . '</span></p>
								          </div>
								        </div>
							        </div>
							        <div class="gc-tour-col-content-area">
							          <h3 class="gc-tour-col-content-heading">' . get_the_title($post_id) . '</h3>
							          <div class="gc-tour-col-content-pragraph">' . substr(get_the_excerpt($post_id), 0, 170) . '...</div>
							          <a href="javascript:void(0);" data-id="' . $post_id . '" class="gctcf-tour-enquiry">' . __("Enquire Now", "gctcf") . '</a> </div>
							      </div>
							    </div>
							  </div>';
				endwhile;
				wp_reset_postdata();

				$html .= '</div>';
			}
		}
		if ($html) {
			$html .= '<div class="modal fade" id="ToursModal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">Enquire Now</h4>
								</div>

								<div class="modal-body">
									<div class="gc-row">
										<div class="gc-md-6 tour-content-js">

										</div>

										<div class="gc-md-6 tour-popup-right-form">
											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Name<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-name-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Email<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-email-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Contact Number<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-contact-number-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Booking Date<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-booking-date-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Alt. Booking Date<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-booking-date-alt-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													No. Of Adults<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-adults-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													No. of Children<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<input type="text" class="form-control tour-children-js">
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>

											<div class="gc-row">
												<label class="gc-md-12 form-control-label">
													Message<span class="text-danger">*</span>
												</label>
												<div class="gc-md-12">
													<textarea class="form-control tour-message-js" rows="5"></textarea>
													<label class="text-danger form-error hidden"></label>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="modal-footer">
									<div class="alert alert-success hidden" role="alert">
										Thank you for your enquiry!
									</div>
									<p>
										<button type="button" class="btn btn-primary submit-tour-enquiry-js">Enquire Now</button>
										<button class="btn btn-danger close" data-dismiss="modal">Close</button>
									</p>
								</div>
							</div>
						</div>
					</div>';
		}
		return $html;
	}

	public function gctcf_footer()
	{
		echo '<div class="gctcf-loader" style="display:none;"><div class="gctcf-loader-wrap"><img src="' . GCTCF_URL . 'public/images/loader.gif" alt="image" /><div class="loader-message" style="display:none;"><p>Our system is checking multiple sources.</p><p>The result will appear within a few seconds.</p></div></div></div>';
	}

	public function gctcf_send_giftcard_mail($order, $sent_to_admin, $plain_text, $email)
	{
		if (!$sent_to_admin) :

			$is_egiftcard = false;
			if (!empty($order->get_items())) {
				foreach ($order->get_items() as $item_id => $item) {

					$product = $item->get_product();
					$attr_type = $product->get_variation_attributes();
					if (!empty($attr_type) && isset($attr_type['attribute_type']) && $attr_type['attribute_type'] == 'eGiftcard') {
						$is_egiftcard = true;
					}
				}
			}

			if ($is_egiftcard) {
				$email  = get_post_meta($order->get_id(), 'byconsole_giftcard_email', true);
				$price = get_post_meta($order->get_id(), 'byconsole_giftcard_amount', true);
				$code = get_post_meta($order->get_id(), 'user_giftcard_code', true);
				$message = get_post_meta($order->get_id(), 'byconsole_giftcard_message', true);
				$first_name = get_post_meta($order->get_id(), 'byconsole_giftcard_first_name', true);
				$last_name = get_post_meta($order->get_id(), 'byconsole_giftcard_last_name', true);
				$name = $first_name . ' ' . $last_name;
				$recipient_name = trim($name);
				ob_start();
				include GCTCF_PATH . 'public/partials/gctcf-travelgift-email.php';
				$html = ob_get_clean();

				$mail_headers  = "MIME-Version: 1.0" . "\r\n";
				$mail_headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
				$mail_headers .= "From: " . get_bloginfo() . " <" . get_bloginfo('admin_email') . ">" . "\r\n";
				$to = array($email, get_bloginfo('admin_email'), $order->get_billing_email(), 'jase@robotdwarf.com');
				$schedule_send = get_post_meta( $order->get_id(), 'byconsole_giftcard_schedule_send', true );
				if ( $schedule_send ) {
					$to = array(get_bloginfo('admin_email'), $order->get_billing_email(), 'jase@robotdwarf.com');
				}
				file_put_contents(dirname(__FILE__) . '/order_' . $order->get_id() . '.txt', print_r($to, true) . "\r\n" . $html);
				if ( $order->has_status('completed') ) {
					file_put_contents(dirname(__FILE__) . '/order_' . $order->get_id() . '_completed.txt', print_r($to, true) . "\r\n" . $html);
					if ( ! $schedule_send ) {
						update_post_meta( $order->get_id(), 'gctf_send_status', 'sent' );
					}
					wp_mail($to, 'New GiftCard', $html, $mail_headers);
				}
			} else {
				$mail_headers  = "MIME-Version: 1.0" . "\r\n";
				$mail_headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
				$mail_headers .= "From: " . get_bloginfo() . " <" . get_bloginfo('admin_email') . ">" . "\r\n";
				if ( $order->has_status('completed') ) {
					$to = array( get_bloginfo('admin_email'));
					$html = '<p>Hi, a new Physical Gift card was ordered as part of ORDER #' . $order->get_id() . '</p>';
					if ($schedule_send) {
						$html .= '<p>The gift card has a send date of: ' . $schedule_send . '</p>';
					}
					wp_mail($to, 'New Physical GiftCard needed for Order #' . $order->get_id(), $html, $mail_headers);
				}
			}

		endif;
	}

	public function gctcf_destinations_file()
	{
		global $wpdb;
		$html = '';
		$Parent_id = '';
		$mydestinations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}attraction_destinations WHERE Parent_id = 0");

		if ($mydestinations) {

			$html .= '<div class="gc-tour-destinations-row owl-carousel">';
			foreach ($mydestinations as $mydestination) {
				// echo '<pre>';
				// print_r($mydestination);
				// echo '</pre>';
				$html .= '<div class="item">
                         	<a href="' . site_url('/attraction-search/?search=&dest=' . $mydestination->Title) . '"><div class="dest-img">
                               	<img src="' . $mydestination->Img_sml . '" alt="" />
                    		</div><p>' . $mydestination->Title . '</p></a>
                         </div>';
			}
			$html .= '</div></a>';
		}
		return $html;
	}




	public function gctcf_attraction_file()
	{
		global $wpdb;
		$html = '';
		$CategoryID = '';
		$myposts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}attraction_tags WHERE `CategoryID` = 24 ");
		if ($myposts) {
			$html .= '<div class="do-something-heading"><h2>Do Something...
            </h2></div>';
			$html .= '<div class="gc-tour-listing-row">
             <ul>';
			foreach ($myposts as $mypost) {

				$html .= '<li>
                          <a href="' . site_url('/attraction-search/?search=&tag=' . $mypost->Tag) . '"><div class="gc-tour-listing-img"> <img src="' . site_url() . '/wp-content/plugins/gctcf/public/images/' . $mypost->Tag . '.jpg" alt="image"/><div class="gc-tour-listing-overlay-content">
					           <p>' . strtoupper($mypost->Tag) . '</p>
					          
					       </div></div></a>
                         </li>';
			}

			$html .= '</ul>
                    </div>';
		}
		return $html;
	}


	public function gctcf_attraction_thank_you()
	{
		if (isset($_GET['custom']) && $_GET['custom']) {

			update_post_meta($post_id, '_gctcf_payment_response', $_REQUEST);
			$post_id = isset($_GET['custom']) ? $_GET['custom'] : '';
			$booking = get_post_meta($post_id, '_gctcf_booking_craeted', true);
			$send_email = get_post_meta($post_id, '_gctcf_confiramtion_email', true);

			$attraction_settings = get_option('attraction_api_option');
			$attraction_api_url = rtrim($attraction_settings['attraction_api_url'], "/");
			$attraction_api_user = $attraction_settings['attraction_api_username'];
			$attraction_api_pass = $attraction_settings['attraction_api_password'];

			$txn_id = isset($_GET['txn_id']) ? $_GET['txn_id'] : '';
			update_post_meta($post_id, '_gctcf_attraction_booking_txnID', $txn_id);
			$txn_st = isset($_GET['st']) ? $_GET['st'] : '';
			update_post_meta($post_id, 'txn_status', $txn_st);

			if ($booking == '') {
				//
				if ($txn_id != '' && (isset($_REQUEST['payment_status'])) && (($_REQUEST['payment_status'] == 'Completed') || ($_REQUEST['payment_status'] == 'Success'))) {
					update_post_meta($post_id, '_gctcf_booking_craeted', 1);

					$bodydata = get_post_meta($post_id, '_gctcf_api_data', true);

					$posturl = $attraction_api_url . '/orders';
					$body = json_encode($bodydata, JSON_PRETTY_PRINT);

					$options = array(
						'body'        => $body,
						'headers'     => array(
							'Content-Type' => 'application/json',
							'Authorization' => 'Basic ' . base64_encode($attraction_api_user . ':' . $attraction_api_pass),
						),
						'timeout'     => 60,
					);

					$apiresponse = wp_remote_post($posturl, $options);
					if (is_wp_error($apiresponse)) {
						$error_message = $apiresponse->get_error_message();
						update_post_meta($post_id, '_gctcf_booking_response', $error_message);
					} else {
						$responsebody = wp_remote_retrieve_body($apiresponse);
						$responseBody = json_decode($responsebody);
						update_post_meta($post_id, '_gctcf_booking_response', $responseBody);
						if (isset($responseBody) && isset($responseBody->id)) {
							update_post_meta($post_id, '_gctcf_booking_id', $responseBody->id);
							// echo  get_post_meta($post_id, '_gctcf_booking_id', true);
							$booking_msg = '<p>Paymant has been made sucessfully, Thank you for booking with Giftcards4travel</p>';
						} else {
							$booking_msg = '<p>Your booking is not craeted, Please contact to support.</p>';
						}
					}
				} else {
					$booking_msg = '<p>Your booking is not created, beacuse your payment is not completed</p>';
				}
				// echo 'post-id:     '. $post_id;
				// echo 'booking-id:  '. get_post_meta($post_id, '_gctcf_booking_id', true);

			} else if ($booking == 1) {
				$booking_msg = '<p>Paymant has been made sucessfully, Thank you for booking with Giftcards4travel</p>';
			}
			if ($post_id != '') {

				$booking_id = get_post_meta($post_id, '_gctcf_booking_id', true);
				// pre(get_post_meta($post_id));
				$booking_email = get_post_meta($post_id, '_gctcf_order_email', true);
				$billing_data = get_post_meta($post_id, '_gctcf_billing_info', true);
				$address = !empty($billing_data['billing_address2']) ? $billing_data['billing_address1'] . ', ' . $billing_data['billing_address2'] : $billing_data['billing_address1'];

				$travel_data = get_post_meta($post_id, '_gctcf_travel_info', true);
				$passenger_name = get_post_meta($post_id, '_gctcf_passenger_name', true);

				$attraction_info = get_post_meta($post_id, '_gctcf_ticket_data', true);
				$attractionticket_info = json_decode($attraction_info, true);

				$booking_date = !empty($attractionticket_info['select_time']) ? $attractionticket_info['select_date'] . ', ' . $attractionticket_info['select_time'] : $attractionticket_info['select_date'];
				// pre($attractionticket_info);
				// exit;
				update_post_meta($post_id, '_gctcf_booking_amount', $attractionticket_info['total-price']); ?>
				<script language=JavaScript src="https://portgk.com/create-sale?client=java&MerchantID=2189&SaleID=<?php echo $booking_id; ?>&Purchases=Attraction,<?php echo $attractionticket_info['total-price']; ?>"></script>
				<noscript><img src="https://portgk.com/create-sale?client=img&MerchantID=2189&SaleID=<?php echo $booking_id;; ?>&Purchases=Attraction,<?php echo $attractionticket_info['total-price']; ?>" width="10" height="10" border="0"></noscript>
				<?php
				$html .= '<div class="gc-row attraction-booking-row">
					      	<div class="gc-md-12 gc-sm-12 gc-xs-12 attraction_prepare_list">
					        	<div class="attraction_prepare_section">
						          	<h5>Booking Info</h5>
						          	<div class="attraction-title">
						            	<h3>' . $attractionticket_info['product_name'] . '</h3>
						            	<p><span>£' . $attractionticket_info['total-price'] . '</span></p>
						          	</div>
						          	<div class="attraction-booking-date">
						            	<h6>Booking date : </h6>
						            	<p>' . $booking_date . '</p>
						          	</div>';
				$html .= '<div class="booking-tickets">';
				$html .= '<h6>Tickets</h6>';
				$html .= '<ul>';
				foreach ($attractionticket_info['tickets'] as $key => $value) {

					if ($value['ticket-qty'] > 0) {
						$html .= '<li>' . $value['ticket-qty'] . ' x ' . $value['type'] . '</li>';
					}
				}

				$html .= '</ul>';
				$html .= '</div>';
				$html .= '<div class="attraction-booking-form-data">
						            	<div class="booking-lead-paasengers-name">
						              	<h6>Lead passenger name</h6>
						              	<p>' . $travel_data['lead_passenger_name'] . '</p>
						            </div>';
				if (!empty($passenger_name)) {
					$k = 0;
					foreach ($passenger_name as $key => $value) {
						foreach ($value as  $pass_key => $name) {

							foreach ($name as $pass_name) {

								if ($pass_key == "passenger_name") {
									$k++;
									if ($k == 1) {
										$html .= '<div class="booking-paasengers-name">';
										$html .= '<h6>Passenger\'s Name</h6>';
									}
									$html .= '<p>' . $pass_name . '</p>';
								}
							}
						}
					}
					if ($k > 0) {
						$html .= '</div>';
					}
				}
				$html .= '<div class="booking-departure-date">
						              	<h6>Departure date</h6>
						              	<p>' . $travel_data['depature_date'] . '</p>
						            </div>
						            <div class="booking-departure-date">
						              	<h6>Billing Address</h6>
						              	<p>' . $billing_data['billing_fullname'] . '</p>
						              	<p>' . $billing_data['billing_phoneNumber'] . '</p>
						              	<p>' . $address . '</p>
						              	<p>' . $billing_data['billing_city'] . ', ' . $billing_data['billing_state'] . ', ' . $billing_data['billing_country'] . '</p>
						              	<p>' . $billing_data['billing_pincode'] . '</p>
						            </div>
					        	</div>
					    	</div>
						</div>';
				if (($send_email == 0) && isset($_GET['payment_status']) && ($_GET['payment_status'] == 'Completed')) {
					$coupen_code = get_post_meta($post_id, '_gctcf_coupon_code', true);
					$coupen_value = get_post_meta($post_id, '_gctcf_coupon_amount', true);
					$booking_amount = get_post_meta($post_id, '_gctcf_booking_amount', true);
					$email_content .= '<!DOCTYPE html>
										<html>
											<head>
												<title>Booking Email</title>
												<meta charset="UTF-8">
												<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
											</head>
											<body class="booking-email-block" style="padding: 0px; margin: 0px;">
												<table width="100%" style="max-width: 600px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border: 1px solid #061d2f;
												color: #061d2f;" cellpadding="0" cellspacing="0">
													<tr>
														<td colspan="3" style="padding-bottom:30px;"><h2 style="text-transform: uppercase; font-size: 20px; font-weight: 600;background-color: #fad38f; padding: 10px 0px; text-align: center; margin: 0px;font-family: sans-serif; color: #061d2f;">Booking info</h2></td>
													</tr>
													<tr>
														<td colspan="3"><table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-bottom:15px;">
															<tr>
															<td width="80%" style=" font-size: 16px; font-family:Arial, Helvetica, sans-serif; text-align: left;"><strong>' . $attractionticket_info['product_name'] . '</strong></td>
															<td width="20%" align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f;"><span style="text-align: right;">£' . $attractionticket_info['total-price'] . '</span></td>
															</tr>
														</table></td>
													</tr>
													<tr>
														<td colspan="2" style="font-size: 14px;font-family: sans-serif; color: #061d2f;"><strong>Booking date: </strong>' . $booking_date . '</td>
													</tr>
													<tr>
													
													<td width="30%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;padding-top: 20px;"><strong>Tickets</strong></td>
													</tr>';
					foreach ($attractionticket_info['tickets'] as $key => $value) {

						if ($value['ticket-qty'] > 0) {

							$email_content .= '<tr><td style="font-size: 14px;font-family: sans-serif; color: #061d2f;">' . $value['ticket-qty'] . ' x ' . $value['type'] . '</td></tr>';
						}
					}

					$email_content .= '<tr>
														<td width="33%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;padding-top: 20px;"><strong>Lead passenger name</strong></td>';
					if (!empty($passenger_name)) {
						$l = 1;
						foreach ($passenger_name as $key => $value) {
							foreach ($value as  $pass_key => $name) {
								foreach ($name as $pass_name) {

									if ($pass_key == "passenger_name") {
										if ($l == 1) {
											$email_content .= '<td width="33%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;padding-top: 20px;"><strong>Passenger\'s name</strong></td>';
										}
										$l++;
									}
								}
							}
						}
					}

					$email_content .= '<td width="33%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;padding-top: 20px;"><strong>Departure date</strong></td>
													</tr>
													<tr>
														<td valign="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f;">' . $travel_data['lead_passenger_name'] . '</td>
														';
					if (!empty($passenger_name)) {
						$j = 0;
						foreach ($passenger_name as $key => $value) {
							foreach ($value as  $pass_key => $name) {
								foreach ($name as $pass_name) {

									if ($pass_key == "passenger_name") {
										$j++;
										if ($j == 1) {
											$email_content .= '<td valign="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f;"><table>';
										}
										$email_content .= '<tr><td valign="top">' . $pass_name . '</td></tr>';
									}
								}
							}
						}
						if ($j > 0) {
							$email_content .= '</table></td>';
						}
					}
					$email_content .= '
														<td valign="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f;">' . $travel_data['depature_date'] . '</td>
													</tr>
													<tr>
														<td width="30%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;padding-top: 20px;"><strong>Billing address</strong></td>
													</tr>
													<tr>
														<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;">' . $billing_data['billing_fullname'] . '<br>
														' . $billing_data['billing_phoneNumber'] . '<br>
														' . $address . '<br>
														' . $billing_data['billing_city'] . ', ' . $billing_data['billing_state'] . ', ' . $billing_data['billing_country'] . '<br>
														' . $billing_data['billing_pincode'] . '
														</td>
													</tr>';
					if (!empty($coupen_code)) {
						if ($booking_amount > $coupen_value) {
							$total_paid_amount = $booking_amount - $coupen_value;
						} else {
							$total_paid_amount = '1.00';
						}
						$email_content .= '<tr>
														<td colspan="5" style="padding-top:20px;">
															<table width="100%" cellpadding="0" cellspacing="0">
																<tr>
																	<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:20px; border:2px solid #ccc;"><strong style="padding-right: 10px;">Gift Card Code</strong> <span style="color: #061d2f; font-size: 16px; font-weight: 600; padding: 7px 15px; background-color: #f9d28e;">' . $coupen_code . '</span></td>
																	<td style="padding:15px; border:2px solid #ccc; border-left-width:0;"><table width="100%" cellpadding="0" cellspacing="0">
																<tbody>
																	<tr>
																	<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Attraction price :</td>
																	<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_amount . '</strong></td>
																</tr>
																<tr>
																	<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Coupan Price:</td>
																	<td  align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $coupen_value . '</strong></td>
																</tr>
															</tbody>
															<tfoot>
															<tr>
																<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
																<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $total_paid_amount . '</strong></td>
															</tr>
															</tfoot>
														</table></td>
													</tr>
													</table>
													</td>
													</tr>';
					} else {
						$email_content .= '<tr>
														<td colspan="5" style="padding-top:20px;">
															<table width="100%" cellpadding="0" cellspacing="0">
																<tr>
																<td align="right" style=""><table width="100%" cellpadding="0" cellspacing="0" style="max-width:300px;padding:15px; border:2px solid #ccc;">
																<tbody>
																	<tr>
																	<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Attraction price :</td>
																	<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_amount . '</strong></td>
																</tr>
															</tbody>
															<tfoot>
															<tr>
																<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
																<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $booking_amount . '</strong></td>
															</tr>
															</tfoot>
														</table></td>
													</tr>
													</table>
													</td>
													</tr>';
					}
					$email_content .= '</table><p>contact <a href="mailto:bookings@giftcards4travel.co.uk">bookings@giftcards4travel.co.uk</a> for any queries </p>
											</body>
										</html>';
					$to = $booking_email;
					// $group_emails = array('rob@giftcards4travel.co.uk', 'bookings@giftcards4travel.co.uk', 'developersuseonly@gmail.com', 'preeti@cmitexperts.com', 'developer@giftcards4travel.com');
					$subject = 'Booking confirmation';
					$headers = array('Content-Type: text/html; charset=UTF-8');
					update_post_meta($post_id, '_gctcf_confiramtion_email', 1);
					wp_mail($to, $subject, $email_content, $headers);

					if ($coupen_code) {
						$coupon_wp = new WC_Coupon($coupen_code);
					
						$amount = $coupon_wp->get_amount();
						if ($amount) {
							$new_remaining_amount = $amount - $coupen_value;
							$coupon_wp->set_amount($new_remaining_amount);
							$coupon_wp->save();
						} else {
							$store_vouchers = get_posts(array(
							'post_type' => 'gc4t_store_voucher',
							'post_status' => 'private',
							'posts_per_page' => 1,
							'meta_query' => array(
								array(
								'key' => 'gc4t_voucher_code',
								'value' => $coupen_code,
								)
							)
							));
							if (isset($store_vouchers[0])) {
								$voucher_amount = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount', true);
								$voucher_amount_remaining = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
								$new_remaining_amount = $voucher_amount_remaining - $coupen_value;
								$new_remaining_amount = max($new_remaining_amount, 0);
								update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', $new_remaining_amount);
								if ($new_remaining_amount == 0) {
									update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_status', 'used');
								}
							}
						}
					}
				}
			}
			echo $booking_msg;
			echo $html;
		}
	}

	static function gctcf_displayDates($date1, $date2, $format = 'Y-m-d')
	{
		$dates = array();
		$current = strtotime($date1);
		$date2 = strtotime($date2);
		$stepVal = '+1 day';
		while ($current <= $date2) {
			$dates[] = date($format, $current);
			$current = strtotime($stepVal, $current);
		}
		return $dates;
	}
}

add_action('template_redirect', 'gctcf_hotel_booking');
	function gctcf_hotel_booking()
	{
		if (is_page('hotel-booking') && !isset($_REQUEST['feed_type']) && !isset($_REQUEST['ct_builder'])) {
			wp_redirect(site_url('hotels'));
			exit();
		}
		if ((!isset($_POST['vehicle_book_now']) && !isset($_POST['client_confirm_now'])) && !is_admin() && is_page('transfer-booking') && !isset($_REQUEST['ct_builder'])) {
			wp_redirect(home_url('/transfers'));
			exit();
		}
		$hotel_id = isset($_REQUEST['hotel-id']) ? $_REQUEST['hotel-id'] : 0;
		if (is_page('hotel-details') && empty($hotel_id) && !is_admin() && !isset($_GET['ct_builder'])) {
			wp_redirect('/hotels');
			exit();
		}

		if (is_page('attraction-details') && !isset($_GET['id']) && !isset($_GET['ct_builder'])) {
			//wp_redirect(site_url('attractions'));
			//exit();
		}

		// if (is_page('attractions') ||  is_page('attraction-booking') || is_page('attraction-search') || is_page('attraction-details') ) {
		// 	wp_redirect(site_url());
		// 	exit();
		// }

	}

add_action('wp_head', 'add_HTML_head');
function add_HTML_head()
{
	echo '<meta name="p:domain_verify" content="33db259a7c71518fb5576741defbc258"/>
	<!-- Google tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-RL0X1K21EW">
	</script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag(\'js\', new Date());
	
	  gtag(\'config\', \'G-RL0X1K21EW\');
	</script>';
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support('html5', ['script', 'style']);
	},
	999
);
add_action('wp_loaded', 'prefix_output_buffer_start');
function prefix_output_buffer_start()
{
	ob_start("prefix_output_callback");
}

add_action('shutdown', 'prefix_output_buffer_end');
function prefix_output_buffer_end()
{
	if (ob_get_level() > 0) {
		ob_end_flush();
	}
}

function prefix_output_callback($buffer)
{
	return preg_replace("%[ ]type=[\'\"]text\/(javascript|css)[\'\"]%", '', $buffer);
}
//add_action( 'after_setup_theme', 'wpdocs_i_am_a_function',999 );
function wpdocs_i_am_a_function()
{
	remove_action('wp_head', '_wp_render_title_tag', 10);
	remove_theme_support('title-tag');
}
add_action( 'wp_footer', 'gctcf_custom_footer' );
function gctcf_custom_footer()
{
	if (isset($_GET['ajay']))
	{
		$order = wc_get_order( 16916 );
		$email  = get_post_meta($order->get_id(), 'byconsole_giftcard_email', true);
		$price = 150;
		$code = get_post_meta($order->get_id(), 'user_giftcard_code', true);
		$message = get_post_meta($order->get_id(), 'byconsole_giftcard_message', true);
		$first_name = get_post_meta($order->get_id(), 'byconsole_giftcard_first_name', true);
		$last_name = get_post_meta($order->get_id(), 'byconsole_giftcard_last_name', true);
		$name = $first_name . ' ' . $last_name;
		$receipent_name = trim($name);
		ob_start();
		include GCTCF_PATH . 'public/partials/gctcf-giftcard-email.php';
		$html = ob_get_clean();

		$mail_headers  = "MIME-Version: 1.0" . "\r\n";
		$mail_headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
		$mail_headers .= "From: " . get_bloginfo() . " <" . get_bloginfo('admin_email') . ">" . "\r\n";
		$to = array('ajay@cmitexperts.com', "rob@giftcards4travel.co.uk");
		wp_mail($to, 'New GiftCard', $html, $mail_headers);
	}
}
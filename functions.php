<?php

//Standard Sidebars
function twentytwelve_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'twentytwelve' ),
		'id' => 'sidebar-1',
		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'twentytwelve' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Call to Action Widget Area', 'twentytwelve' ),
		'id' => 'sidebar-2',
		'description' => __( 'Appears in the top right of the website', 'twentytwelve' ),
		'before_widget' => '<div id="call-to-action">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'twentytwelve_widgets_init' );

//Standard Custom Post Type

register_post_type('slides', array(
'label' => __('Slides'),
'singular_label' => __('Slides'),
'public' => true,
'show_ui' => true,
'capability_type' => 'post',
'hierarchical' => false,
'rewrite' => false,	'query_var' => true,
'exclude_from_search' => true,
'supports' => array('title', 'editor', 'thumbnail'),
'taxonomies' => array( 'post_tag')
));

//Product Type and Custom Taxonomy *from Strainsert

function product_category_init() {
	// create a new taxonomy
	register_taxonomy(
		'product-categories',
		'post',
		array(
			'label' => __( 'Product Categories' ),
			'singular_label' => __('Product Category'),
			'hierarchical'      => true,
			'rewrite'      => array('slug' => 'product-categories', 'with_front' => false),
			'capabilities' => array(
    				'manage__terms' => 'edit_posts',
    				'edit_terms' => 'manage_categories',
    				'delete_terms' => 'manage_categories',
    				'assign_terms' => 'edit_posts'
			)
		)
	);
}
add_action( 'init', 'product_category_init' );

add_action('init', 'register_mypost_type');
function register_mypost_type() {
register_post_type('products', array(
'label' => __('Products'),
'singular_label' => __('Products'),
'public' => true,
'show_ui' => true,
'query_var' => true,
'capability_type' => 'post',
'hierarchical' => true,
'rewrite' => array('slug'=>'products','with_front'=>false),
'supports' => array('title', 'editor', 'thumbnail'),
'taxonomies' => array( 'post_tag', 'product-categories'),
));
}


//Add thumbnail size and add to image sizes for galleries

if ( function_exists( 'add_image_size' ) ) { 
	//add_image_size( 'category-thumb', 300, 9999 ); //300 pixels wide (and unlimited height)
	add_image_size( 'homepage-slider', 1600, 800, true ); //(cropped)
	add_image_size( 'blog-thumb', 360, 360, true ); //(cropped)
}

add_filter('image_size_names_choose', 'my_image_sizes');
	function my_image_sizes($sizes) {
		$addsizes = array(
		"blog-thumb" => __( "Blog Thumb")
		);
	$newsizes = array_merge($sizes, $addsizes);
	return $newsizes;
}

//Excerpt
function new_excerpt_more( $more ) {
	return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

//Order Entries by Last Name *from DCASP
function wpsites_cpt_loop_filter($query) {
if ( !is_admin() && $query->is_main_query() ) {
if ( is_post_type_archive('members') ) {
     $query->set( 'orderby', 'last_name' );
     $query->set( 'order', 'ASC' );
    }
  }
}
 
add_action('pre_get_posts','wpsites_cpt_loop_filter');

//add filters for category descriptions *from CDL
remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'term_description', 'wp_kses_data' );

add_filter('edit_category_form_fields', 'cat_description');
function cat_description($tag)
{
    ?>
        <table class="form-table">
            <tr class="form-field">
                <th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
                <td>
                <?php
                    $settings = array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '15', 'textarea_name' => 'description' );
                    wp_editor(wp_kses_post($tag->description , ENT_QUOTES, 'UTF-8'), 'cat_description', $settings);
                ?>
                <br />
                <span class="description"><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></span>
                </td>
            </tr>
        </table>
    <?php
}

add_filter( 'term_description', 'shortcode_unautop');
add_filter( 'term_description', 'do_shortcode' );

add_action('admin_head', 'remove_default_category_description');
function remove_default_category_description()
{
    global $current_screen;
    if ( $current_screen->id == 'edit-category' )
    {
    ?>
        <script type="text/javascript">
        jQuery(function($) {
            $('textarea#description').closest('tr.form-field').remove();
        });
        </script>
    <?php
    }
}

//Change In Stock / Out of Stock Text on Woocommerce *from East Hill
add_filter( 'woocommerce_get_availability', 'wcs_custom_get_availability', 1, 2);
function wcs_custom_get_availability( $availability, $_product ) {
   
   	// Change In Stock Text
    if ( $_product->is_in_stock() ) {
        $availability['availability'] = __('In Stock', 'woocommerce');
    }
    // Change Out of Stock Text
    if ( ! $_product->is_in_stock() ) {
    	$availability['availability'] = __('On Backorder', 'woocommerce');
    }
    return $availability;
}

//Change image and color of button for login page
function login_style() {
    wp_register_style('login-style', TEMPLATE_DIRECTORY_URI . '/assets/css/login-style.css');
    wp_enqueue_style('login-style');
}
add_action( 'login_enqueue_scripts', 'login_style' );


//Add ACF image in RSS Feed *from Perakis

function ld_advanced_custom_field_in_feed($content) {  
    if(is_feed()) {  
        $post_id = get_the_ID();  
        
        $rssimage = get_field('front_image');

            if( !empty($rssimage) ): 
                $imgurl = $rssimage['url'];
                $imgalt = $rssimage['alt'];
                $imgtitle = $rssimage['title'];

                $output .= '<p class="rss-image"><img style="max-width: 540px; height: auto;" src="';
                $output .=  $imgurl;
                $output .= '" alt="';
                $output .= $imgalt;
                $output .= '" title="';
                $output .= $imgtitle;
                $output .= '" /></p>'; 
            endif; 
  
        $content = $content.$output;  
    }  
    return $content;
}  
add_filter('the_content','ld_advanced_custom_field_in_feed');

//Add Browser class from Office Shredding
add_filter('body_class','browser_body_class');
function browser_body_class($classes) {
    global $is_safari, $is_chrome, $is_gecko, $is_winIE, $is_iphone;
    if($is_safari) $classes[] = 'safari';
    elseif($is_chrome) $classes[] = 'chrome';
	elseif($is_gecko) $classes[] = 'firefox';
    elseif($is_winIE) $classes[] = 'ie';
    else $classes[] = 'unknown';
    if($is_iphone) $classes[] = 'iphone';
    return $classes;
}


/** Remove Export Links from Calendar - Taken from PT Women **/
class Tribe__Events__Remove__Export__Links {

    public function __construct() {
        add_action( 'init', array( $this, 'single_event_links' ) );
        add_action( 'init', array( $this, 'view_links' ) );
    }

    public function single_event_links() {
        remove_action(
            'tribe_events_single_event_after_the_content',
            array( 'Tribe__Events__iCal', 'single_event_links' )
        );
    }

    public function view_links() {
        remove_filter(
            'tribe_events_after_footer',
            array( 'Tribe__Events__iCal', 'maybe_add_link' )
        );
    }
}

new Tribe__Events__Remove__Export__Links();

/**
 * Remove empty paragraphs created by wpautop()
 * @author Ryan Hamilton
 * @link https://gist.github.com/Fantikerz/5557617
 */
function remove_empty_p( $content ) {
    $content = force_balance_tags( $content );
    $content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );
    $content = preg_replace( '~\s?<p>(\s|&nbsp;)+</p>\s?~', '', $content );
    return $content;
}
add_filter('the_content', 'remove_empty_p', 20, 1);

//code to remove query strings from scripts for version numbers
function _remove_script_version( $src ){
$parts = explode( '?ver', $src );
return $parts[0];
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );

/**GForm Submit Button from Ramsey's */
add_filter( 'gform_next_button', 'my_next_button_markup', 10, 2 );
function my_next_button_markup( $next_button, $form ) {
    return "<button class='button' id='gform_submit_button_{$form['id']}'><span>Next Step&#9656;</span></button>";
}

//Tree Function, from RDD
function is_tree($pid)
{
  global $post;

  $ancestors = get_post_ancestors($post->$pid);
  $root = count($ancestors) - 1;
  $parent = $ancestors[$root];

  if(is_page() && (is_page($pid) || $post->post_parent == $pid || in_array($pid, $ancestors)))
  {
    return true;
  }
  else
  {
    return false;
  }
};
// Add custom post type to ACF *from Strainsert
// step 1 add a location rule type
  add_filter('acf/location/rule_types', 'acf_wc_product_type_rule_type');
  function acf_wc_product_type_rule_type($choices) {
    // first add the "Product" Category if it does not exist
    // this will be a place to put all custom rules assocaited with woocommerce
    // the reason for checking to see if it exists or not first
    // is just in case another custom rule is added
    if (!isset($choices['Product'])) {
      $choices['Product'] = array();
    }
    // now add the 'Category' rule to it
    if (!isset($choices['Product']['product-categories'])) {
      // product_cat is the taxonomy name for woocommerce products
      $choices['Product']['product_cat_term'] = 'Product Category Term';
    }
    return $choices;
  }
  
  // step 2 skip custom rule operators, not needed
  
  
  // step 3 add custom rule values
  add_filter('acf/location/rule_values/product_cat_term', 'acf_wc_product_type_rule_values');
  function acf_wc_product_type_rule_values($choices) {
    // basically we need to get an list of all product categories
    // and put the into an array for choices
    $args = array(
      'taxonomy' => 'product-categories',
      'hide_empty' => false
    );
    $terms = get_terms($args);
    foreach ($terms as $term) {
      $choices[$term->term_id] = $term->name;
    }
    return $choices;
  }
  
  // step 4, rule match
  add_filter('acf/location/rule_match/product_cat_term', 'acf_wc_product_type_rule_match', 10, 3);
  function acf_wc_product_type_rule_match($match, $rule, $options) {
    if (!isset($_GET['tag_ID'])) {
      // tag id is not set
      return $match;
    }
    if ($rule['operator'] == '==') {
      $match = ($rule['value'] == $_GET['tag_ID']);
    } else {
      $match = !($rule['value'] == $_GET['tag_ID']);
    }
    return $match;
  }

//Add Featured Image to RSS *from Wide Plank
add_filter( 'the_excerpt_rss', 'rgc_add_featured_image_to_feed_excerpt', 1000, 1 );
function rgc_add_featured_image_to_feed_excerpt( $content ) {

	if ( has_post_thumbnail( get_the_ID() ) ) {
		$content = get_the_post_thumbnail( get_the_ID(), 'full', array( 'align' => 'center', 'style' => 'display: block;margin-right:20px;' ) ) . $content;
	}
	return $content;
}

//Disable SRCSET *from Wide Plank
function disable_srcset( $sources ) {
if (is_feed) {
return false; }
}
add_filter( 'wp_calculate_image_srcset', 'disable_srcset' );


// Activate WordPress Maintenance Mode
function wp_maintenance_mode() {
    if (!current_user_can('edit_themes') || !is_user_logged_in()) {
        wp_die('<h1>Under Maintenance</h1>');
    }
}
add_action('get_header', 'wp_maintenance_mode');

add_filter('manage_products_posts_columns', 'set_custom_edit_products_columns');
function set_custom_edit_products_columns($defaults) {
    $defaults['product-categories'] = 'Categories';
    return $defaults;
}

add_action( 'manage_products_posts_custom_column' , 'custom_products_column', 10, 2 );

function custom_products_column( $column, $post_id ) {
  switch ( $column ) {

    // display a list of the custom taxonomy terms assigned to the post
    case 'product-categories' :
      $terms = get_the_term_list( $post_id , 'product-categories' , '' , ', ' , '' );
      echo is_string( $terms ) ? $terms : '—';
      break;

  }
}

add_filter( 'manage_edit-products_sortable_columns', 'set_custom_products_sortable_columns' );

function set_custom_products_sortable_columns( $columns ) {
  $columns['product-categories'] = 'product-categories';

  return $columns;
}

if(!function_exists('mbe_sort_custom_column')){
    function mbe_sort_custom_column($clauses, $wp_query){
        global $wpdb;
        if(isset($wp_query->query['orderby']) && $wp_query->query['orderby'] == 'product-categories'){
            $clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
            $clauses['where'] .= "AND (taxonomy = 'product-categories' OR taxonomy IS NULL)";
            $clauses['groupby'] = "object_id";
            $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC)";
            if(strtoupper($wp_query->get('order')) == 'ASC'){
                $clauses['orderby'] .= 'ASC';
            } else{
                $clauses['orderby'] .= 'DESC';
            }
        }
        return $clauses;
    }
    add_filter('posts_clauses', 'mbe_sort_custom_column', 10, 2);
}

//change text to leave a reply on comment form
function isa_comment_reform ($arg) {
$arg['title_reply'] = __('Post a Comment');
return $arg;
}
add_filter('comment_form_defaults','isa_comment_reform');

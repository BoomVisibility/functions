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
?>


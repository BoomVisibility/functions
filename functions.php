
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

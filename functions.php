<?php
function my_theme_enqueue_styles() {
    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/* JLA - Set excerpts length to 150 words */
function custom_excerpt_length( $length ) {
	return 150;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

function catch_that_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];
 
  if(empty($first_img)){ //Defines a default image
    $first_img = "/images/default.jpg";
  }
  return $first_img;
}

add_filter('document_title_parts', 'dq_override_post_title', 10);
function dq_override_post_title($title){
   // change title for singular blog post
    if( is_singular( 'post' ) ){
        $title['site'] = ''; //optional
    } else {
        $title['tagline'] = '';
    }
    return $title;
}
/* Remove Continue reading link */
function twentyseventeen_excerpt_more_child( $link ) {
        return ' [...]';
}
add_filter( 'excerpt_more', 'twentyseventeen_excerpt_more_child', 999);

/* Add nofollow to link to comments */
function add_nofollow_to_comments_popup_link () {
	return ' rel="nofollow"';
}
add_filter ( 'comments_popup_link_attributes', 'add_nofollow_to_comments_popup_link' ); 

/* Disable BIG image scaling introduced in WP 5.3 */
add_filter( 'big_image_size_threshold', '__return_false' );


/* Enable shortcodes in widgets */
add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode', 11);

/* Trim description for OG plugin */
add_filter( 'og_og_description_value', function( $value ) {
    return wp_trim_words( $value );
});

/* Let Contributor Role to Upload Media */
if ( current_user_can('contributor') && !current_user_can('upload_files') )
    add_action('admin_init', 'allow_contributor_uploads');
function allow_contributor_uploads() {
    $contributor = get_role('contributor');
    $contributor->add_cap('upload_files');
}

/* display featured post thumbnails in WordPress feeds */
add_filter( 'the_excerpt_rss', 'cnxes_ThumbnailsFeeds' );
add_filter( 'the_content_feed', 'cnxes_ThumbnailsFeeds' );
function cnxes_ThumbnailsFeeds( $content ) {
  global $post;
  if( is_feed() ) {
    if ( has_post_thumbnail( $post->ID ) ){
      $prepend = '<div>' . get_the_post_thumbnail( $post->ID, 'medium', array( 'style' => 'margin-bottom: 10px;' ) ) . '</div>';
      $content = $prepend . $content;
    }
  }
  return $content;
}

/**
   * Prints HTML with meta information for the categories, tags and comments.
   */
function twentyseventeen_entry_footer() {

  /* translators: Used between list items, there is a space after the comma. */
  $separate_meta = __( ', ', 'twentyseventeen' );

  // Get Categories for posts.
  $categories_list = get_the_category_list( $separate_meta );

  // Get Tags for posts.
  $tags_list = get_the_tag_list( '', $separate_meta );

  // We don't want to output .entry-footer if it will be empty, so make sure its not.
  if ( ( ( twentyseventeen_categorized_blog() && $categories_list ) || $tags_list ) || get_edit_post_link() ) {

    echo '<footer class="entry-footer">';

    if ( 'post' === get_post_type() ) {
      if ( ( $categories_list && twentyseventeen_categorized_blog() ) || $tags_list ) {
        echo '<span class="cat-tags-links">';

        // Make sure there's more than one category before displaying.
        if ( $categories_list && twentyseventeen_categorized_blog() ) {
          echo '<span class="cat-links">' . twentyseventeen_get_svg( array( 'icon' => 'folder-open' ) ) . '<span class="screen-reader-text">' . __( 'Categories', 'twentyseventeen' ) . '</span>' . $categories_list . '</span>';
        }

        if ( $tags_list && ! is_wp_error( $tags_list ) ) {
          echo '<span class="tags-links">' . twentyseventeen_get_svg( array( 'icon' => 'hashtag' ) ) . '<span class="screen-reader-text">' . __( 'Tags', 'twentyseventeen' ) . '</span>' . $tags_list . '</span>';
        }

        echo '</span>';
      }
      twentyseventeen_edit_link();
    }
    echo '</footer> <!-- .entry-footer -->';
  }

  if (function_exists('adrotate_group') || function_exists('yarpp_related')) {
    echo '<footer id="entry-subFooter" class="one">';
    if(function_exists('adrotate_group')) {
      echo '<div class="one-half">';
      if (!wp_is_mobile()) {
        echo adrotate_group(4);
      } else {
	 if ( function_exists( 'amp_is_request' ) &&  amp_is_request() ) {
             echo adrotate_group(14);
         } else {
             echo adrotate_group(11);
	 }
      }
      echo '</div>';
    } 
    if(function_exists('yarpp_related')) {
      echo '<div class="one-half">';
      yarpp_related(array('post_type' => 'post'));
      echo '</div>'; 
    }
    echo '</footer>';
  }
}

/**
 * Remove dashicons CSS from the page, only load if user is logged in
 */
function dashicons_admin_only() {
    if(!is_user_logged_in()) {
        global $wp_styles;
        wp_dequeue_style('dashicons');
        $wp_styles->registered['dashicons']->src = '';
    }
}
add_action( 'wp_print_styles', 'dashicons_admin_only' );

/* Delay Cookie banner */
function wt_cli_defer_scripts( $tag, $handle, $src ) {
	$defer = array( 
	  'cookie-law-info',
	);
	if ( in_array( $handle, $defer ) ) {
	   return '<script src="' . $src . '" id="'.$handle.'-js" defer="defer" type="text/javascript"></script>' . "\n";
	}
	return $tag;
} 
add_filter( 'script_loader_tag', 'wt_cli_defer_scripts', 10, 3 );
?>

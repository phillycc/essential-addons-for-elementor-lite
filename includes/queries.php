<?php


/**
 * Get All POst Types
 * @return array
 */
function eael_get_post_types(){

    $eael_cpts = get_post_types( array( 'public'   => true, 'show_in_nav_menus' => true ) );
    $eael_exclude_cpts = array( 'elementor_library', 'attachment', 'product' );

    foreach ( $eael_exclude_cpts as $exclude_cpt ) {
        unset($eael_cpts[$exclude_cpt]);
    }
    $post_types = array_merge($eael_cpts);

    return $post_types;
}
/**
 * Post Settings Parameter
 * @param  array $settings
 * @return array
 */
function eael_get_post_settings( $settings ){
    $tags = $categories = [];
    foreach( $settings as $key => $value ) {
        if( in_array( $key, posts_args() ) ) {
            switch( $key ){
                case 'eael_post_tags' : 
                    if( ! empty( $settings['eael_post_tags'] ) ) {
                        $tags = [[
                            'taxonomy' => 'post_tag',
                            'field' => 'term_id',
                            'terms' => $settings['eael_post_tags'],
                        ]];
                    }
                    break;
                case 'category' : 
                    if( ! empty( $settings['category'] ) ) {
                        $categories = [[
                            'taxonomy' => 'category',
                            'field' => 'term_id',
                            'terms' => $settings['category'],
                        ]];
                    }
                    break;
                case 'eael_post_authors' : 
                    if( isset( $settings['eael_post_authors'] ) && ! empty( $settings['eael_post_authors'] ) && is_array( $settings['eael_post_authors'] ) ) {
                        $post_args['author'] = implode( ",", $settings['eael_post_authors'] );
                    }
                    break;
                case 'page__not_in' : 
                    if( isset( $settings['page__not_in'] ) && ! empty( $settings['page__not_in'] ) && is_array( $settings['page__not_in'] ) ) {
                        $post_args['post__not_in'] = $value;
                    }
                    break;
                default : 
                    if( isset( $settings[ $key ] ) && ! empty( $settings[ $key ] ) ) {
                        $post_args[ $key ] = $value;
                    }
                    break;
            }
        }
    }

    if( isset( $post_args['post_type'] ) && $post_args['post_type'] == 'post' ) {
        $relation = ! empty( $categories ) && ! empty( $tags ) ? [ 'relation' => 'OR' ] : [];
        $post_args['tax_query'] = array_merge($relation, $tags, $categories);
    }
    
    $post_args['posts_per_page'] = $post_args['posts_per_page'] ? $post_args['posts_per_page'] : 4;
    $post_args['post_style'] = isset( $post_args['post_style'] ) ? $post_args['post_style'] : 'grid';

    if( isset( $post_args['offset'] ) ) $post_args['offset'] = intval( $post_args['offset'] );
    $post_args['post_status'] = 'publish';

    return $post_args;
}

/**
 * Getting Excerpts By Post Id
 * @param  int $post_id
 * @param  int $excerpt_length
 * @return string
 */
function eael_get_excerpt_by_id( $post_id, $excerpt_length ){
    $the_post = get_post( $post_id ); //Gets post ID

    $the_excerpt = null;
    if( $the_post ){
        $the_excerpt = $the_post->post_excerpt ? $the_post->post_excerpt : $the_post->post_content;
    }

    $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

     if(count($words) > $excerpt_length) :
         array_pop($words);
         array_push($words, '…');
         $the_excerpt = implode(' ', $words);
     endif;

    return $the_excerpt;
}

/**
 * Get Post Thumbnail Size
 * @return array
 */
function eael_get_thumbnail_sizes(){
    $sizes = get_intermediate_image_sizes();
    foreach($sizes as $s){
        $ret[$s] = $s;
    }

    return $ret;
}

/**
 * POst Orderby Options
 * @return array
 */
function eael_get_post_orderby_options(){
    $orderby = array(
        'ID' => 'Post ID',
        'author' => 'Post Author',
        'title' => 'Title',
        'date' => 'Date',
        'modified' => 'Last Modified Date',
        'parent' => 'Parent Id',
        'rand' => 'Random',
        'comment_count' => 'Comment Count',
        'menu_order' => 'Menu Order',
    );

    return $orderby;
}

/**
 * Get Post Categories
 * @return array
 */
function eael_post_type_categories(){
    $terms = get_terms( array(
        'taxonomy' => 'category',
        'hide_empty' => true,
    ));

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach ( $terms as $term ) {
            $options[ $term->term_id ] = $term->name;
        }
    }

    return $options;
}

/**
 * WooCommerce Product Query
 * @return array
 */
function eael_woocommerce_product_categories(){
    $terms = get_terms( array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ));

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach ( $terms as $term ) {
            $options[ $term->slug ] = $term->name;
        }
        return $options;
    }
}

/**
 * WooCommerce Get Product By Id
 * @return array
 */
function eael_woocommerce_product_get_product_by_id(){
    $postlist = get_posts(array(
        'post_type' => 'product',
        'showposts' => 9999,
    ));
    $options = array();

    if ( ! empty( $postlist ) && ! is_wp_error( $postlist ) ){
        foreach ( $postlist as $post ) {
            $options[ $post->ID ] = $post->post_title;
        }
        return $options;

    }
}

/**
 * WooCommerce Get Product Category By Id
 * @return array
 */
function eael_woocommerce_product_categories_by_id(){
    $terms = get_terms( array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    ));

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach ( $terms as $term ) {
            $options[ $term->term_id ] = $term->name;
        }
        return $options;
    }

}

/**
 * Get Contact Form 7 [ if exists ]
 */
if ( function_exists( 'wpcf7' ) ) {
    function eael_select_contact_form(){
        $wpcf7_form_list = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'showposts' => 999,
        ));
        $options = array();
        $options[0] = esc_html__( 'Select a Contact Form', 'essential-addons-elementor' );
        if ( ! empty( $wpcf7_form_list ) && ! is_wp_error( $wpcf7_form_list ) ){
            foreach ( $wpcf7_form_list as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
        } else {
            $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
        }
        return $options;
    }
}

/**
 * Get Gravity Form [ if exists ]
 */
if ( !function_exists('eael_select_gravity_form') ) {
    function eael_select_gravity_form() {
        $options = array();
        if ( class_exists( 'GFCommon' ) ) {
            $gravity_forms = RGFormsModel::get_forms( null, 'title' );

            if ( ! empty( $gravity_forms ) && ! is_wp_error( $gravity_forms ) ) {

                $options[0] = esc_html__( 'Select Gravity Form', 'essential-addons-elementor' );
                foreach ( $gravity_forms as $form ) {   
                    $options[ $form->id ] = $form->title;
                }

            } else {
                $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
            }
        }

        return $options;
    }
}

/**
 * Get WeForms Form List
 * @return array
 */
function eael_select_weform() {

    $wpuf_form_list = get_posts( array(
        'post_type' => 'wpuf_contact_form',
        'showposts' => 999,
    ));

    $options = array();
    
    if ( ! empty( $wpuf_form_list ) && ! is_wp_error( $wpuf_form_list ) ) {
        $options[0] = esc_html__( 'Select weForm', 'essential-addons-elementor' );
        foreach ( $wpuf_form_list as $post ) {
            $options[ $post->ID ] = $post->post_title;
        }
    } else {
        $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
    }
    
    return $options;
}

/**
 * Get Ninja Form List
 * @return array
 */
if ( !function_exists('eael_select_ninja_form') ) {
    function eael_select_ninja_form() {
        $options = array();
        if ( class_exists( 'Ninja_Forms' ) ) {
            $contact_forms = Ninja_Forms()->form()->get_forms();

            if ( ! empty( $contact_forms ) && ! is_wp_error( $contact_forms ) ) {

                $options[0] = esc_html__( 'Select Ninja Form', 'essential-addons-elementor' );

                foreach ( $contact_forms as $form ) {   
                    $options[ $form->get_id() ] = $form->get_setting( 'title' );
                }
            }
        } else {
            $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
        }

        return $options;
    }
}

/**
 * Get Caldera Form List
 * @return array
 */
if ( !function_exists('eael_select_caldera_form') ) {
    function eael_select_caldera_form() {
        $options = array();
        if ( class_exists( 'Caldera_Forms' ) ) {

            $contact_forms = Caldera_Forms_Forms::get_forms( true, true );

            if ( ! empty( $contact_forms ) && ! is_wp_error( $contact_forms ) ) {
                $options[0] = esc_html__( 'Select Caldera Form', 'essential-addons-elementor' );
                foreach ( $contact_forms as $form ) {   
                    $options[ $form['ID'] ] = $form['name'];
                }
            }
        } else {
            $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
        }

        return $options;
    }
}

/**
 * Get WPForms List
 * @return array
 */
if ( !function_exists('eael_select_wpforms_forms') ) {
    function eael_select_wpforms_forms() {
        $options = array();
        if ( class_exists( 'WPForms' ) ) {

            $args = array(
                'post_type'         => 'wpforms',
                'posts_per_page'    => -1
            );

            $contact_forms = get_posts( $args );

            if ( ! empty( $contact_forms ) && ! is_wp_error( $contact_forms ) ) {
                $options[0] = esc_html__( 'Select a WPForm', 'essential-addons-elementor' );
                foreach ( $contact_forms as $post ) {   
                    $options[ $post->ID ] = $post->post_title;
                }
            }
        } else {
            $options[0] = esc_html__( 'Create a Form First', 'essential-addons-elementor' );
        }

        return $options;
    }
}

// Get all elementor page templates
if ( !function_exists('eael_get_page_templates') ) {
    function eael_get_page_templates(){
        $page_templates = get_posts( array(
            'post_type'         => 'elementor_library',
            'posts_per_page'    => -1
        ));

        $options = array();

        if ( ! empty( $page_templates ) && ! is_wp_error( $page_templates ) ){
            foreach ( $page_templates as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
        }
        return $options;
    }
}

// Get all Authors
if ( !function_exists('eael_get_authors') ) {
    function eael_get_authors() {

        $options = array();

        $users = get_users();

        foreach ( $users as $user ) {
            $options[ $user->ID ] = $user->display_name;
        }

        return $options;
    }
}

// Get all Authors
if ( !function_exists('eael_get_tags') ) {
    function eael_get_tags() {

        $options = array();

        $tags = get_tags();

        foreach ( $tags as $tag ) {
            $options[ $tag->term_id ] = $tag->name;
        }

        return $options;
    }
}

// Get all Posts
if ( !function_exists('eael_get_posts') ) {
    function eael_get_posts() {

        $post_list = get_posts( array(
            'post_type'         => 'post',
            'orderby'           => 'date',
            'order'             => 'DESC',
            'posts_per_page'    => -1,
        ) );

        $posts = array();

        if ( ! empty( $post_list ) && ! is_wp_error( $post_list ) ) {
            foreach ( $post_list as $post ) {
               $posts[ $post->ID ] = $post->post_title;
            }
        }

        return $posts;
    }
}

// Get all Pages
if ( !function_exists('eael_get_pages') ) {
    function eael_get_pages() {

        $page_list = get_posts( array(
            'post_type'         => 'page',
            'orderby'           => 'date',
            'order'             => 'DESC',
            'posts_per_page'    => -1,
        ) );

        $pages = array();

        if ( ! empty( $page_list ) && ! is_wp_error( $page_list ) ) {
            foreach ( $page_list as $page ) {
               $pages[ $page->ID ] = $page->post_title;
            }
        }

        return $pages;
    }
}

/**
 * Load More
 */

function eael_load_more_ajax(){
    
    if( isset( $_POST['action'] ) && $_POST['action'] == 'load_more' ) {
        $post_args = eael_get_post_settings( $_POST );
    } else {
        $post_args = array_shift( func_get_args() );
    }
    
    $posts = new WP_Query( $post_args );

    $return = array();
    $return['count'] = $posts->found_posts;

    ob_start();

    while( $posts->have_posts() ) : $posts->the_post();
        /**
         * All content html here.
         */
        include ESSENTIAL_ADDONS_EL_PATH . 'includes/templates/content.php';
    endwhile;

    wp_reset_postdata();
    wp_reset_query();
    $return['content'] = ob_get_clean();
    if( isset( $_POST['action'] ) && $_POST['action'] == 'load_more' ) {
        echo $return['content'];
    } else {
        return $return;
    }
}
add_action( 'wp_ajax_nopriv_load_more', 'eael_load_more_ajax' );
add_action( 'wp_ajax_load_more', 'eael_load_more_ajax' );

/**
 * For All Settings Key Need To Display
 *
 * @return array
 */
function posts_args(){
    return array(
        // for content-ticker
        'eael_ticker_type',
        'eael_ticker_custom_contents',
        
        // common
        'meta_position',
        'eael_show_meta',
        'image_size',
        'eael_show_image',
        'eael_show_title',
        'eael_show_excerpt',
        'eael_excerpt_length',
        'eael_show_read_more',
        'eael_read_more_text',

        // query_args
        'post_type',
        'posts_per_page',
        'post_style',
        'eael_post_tags',
        'category',
        'post__not_in',
        'page__not_in',
        'eael_post_authors',
        'offset',
        'orderby',
        'order',
    );
}
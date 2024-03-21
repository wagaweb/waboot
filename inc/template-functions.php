<?php

namespace Waboot\inc;

use Waboot\inc\core\utils\Utilities;

/**
 * Gets theme widget areas
 *
 * @return array
 */
function getWidgetAreas(){
    $areas = [
        'header' => [
            'name' =>  __('Header', LANG_TEXTDOMAIN),
            'description' => __( 'The main widget area displayed in the header.', LANG_TEXTDOMAIN),
            'render_zone' => 'header'
        ],
        'main_top' => [
            'name' => __('Main Top', LANG_TEXTDOMAIN),
            'description' => __( 'Widget area displayed above the content and the sidebars.', LANG_TEXTDOMAIN ),
            'render_zone' => 'main-top'
        ],
        'aside' => [
            'name' => __('Aside', LANG_TEXTDOMAIN),
            'description' => __('Widget area displayed in aside', LANG_TEXTDOMAIN ),
            'render_zone' => 'aside'
        ],
        'content_before' => [
            'name' => __('Content Before', LANG_TEXTDOMAIN),
            'description' => __('Widget area displayed above the content', LANG_TEXTDOMAIN ),
            'render_zone' => 'content',
            'render_priority' => 9
        ],
        'content_after' => [
            'name' => __('Content After', LANG_TEXTDOMAIN),
            'description' => __('Widget area displayed below the content', LANG_TEXTDOMAIN ),
            'render_zone' => 'content',
            'render_priority' => 90
        ],
        'main_bottom' => [
            'name' => __('Main Bottom', LANG_TEXTDOMAIN),
            'description' => __( 'Widget area displayed below the content and the sidebars.', LANG_TEXTDOMAIN ),
            'render_zone' => 'main-bottom'
        ],
        'footer' => [
            'name' => __('Footer', LANG_TEXTDOMAIN),
            'description' => __( 'The main widget area displayed in the footer.', LANG_TEXTDOMAIN ),
            'render_zone' => 'footer',
            'render_priority' => 8
        ]
    ];

    $areas = apply_filters('waboot/widget_areas/available',$areas);

    return $areas;
}

/**
 * Returns the index page title
 *
 * @return string
 */
function getIndexPageTitle(){
    return single_post_title('', false);
}

/**
 * Returns the appropriate title for the archive page. Clone of get_the_archive_title() with some editing (eg: some suffix has been removed).
 *
 * @return string
 */
function getArchivePageTitle(){
    if ( is_category() ) {
        /* translators: Category archive title. 1: Category name */
        $title = sprintf( '%s', single_cat_title( '', false ) );
    } elseif ( is_tag() ) {
        /* translators: Tag archive title. 1: Tag name */
        $title = sprintf( '%s', single_tag_title( '', false ) );
    } elseif ( is_author() ) {
        /* translators: Author archive title. 1: Author name */
        $title = sprintf( '%s', '<span class="vcard">' . get_the_author() . '</span>' );
    } elseif ( is_year() ) {
        /* translators: Yearly archive title. 1: Year */
        $title = sprintf( __( 'Year: %s' ), get_the_date( _x( 'Y', 'yearly archives date format' ) ) );
    } elseif ( is_month() ) {
        /* translators: Monthly archive title. 1: Month name and year */
        $title = sprintf( __( 'Month: %s' ), get_the_date( _x( 'F Y', 'monthly archives date format' ) ) );
    } elseif ( is_day() ) {
        /* translators: Daily archive title. 1: Date */
        $title = sprintf( __( 'Day: %s' ), get_the_date( _x( 'F j, Y', 'daily archives date format' ) ) );
    } elseif ( is_tax( 'post_format' ) ) {
        if ( is_tax( 'post_format', 'post-format-aside' ) ) {
            $title = _x( 'Asides', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
            $title = _x( 'Galleries', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
            $title = _x( 'Images', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
            $title = _x( 'Videos', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
            $title = _x( 'Quotes', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
            $title = _x( 'Links', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
            $title = _x( 'Statuses', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
            $title = _x( 'Audio', 'post format archive title' );
        } elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
            $title = _x( 'Chats', 'post format archive title' );
        }
    } elseif ( is_post_type_archive() ) {
        /* translators: Post type archive title. 1: Post type name */
        $title = sprintf( '%s', post_type_archive_title( '', false ) );
    } elseif ( is_tax() ) {
        /* translators: Taxonomy term archive title. 1: Current taxonomy term */
        $title = sprintf( '%1$s', single_term_title( '', false ) );
    } else {
        $arch_obj = get_queried_object();
        if(isset($arch_obj->name)){
            $title = $arch_obj->name;
        }else{
            $title = __('Archives', LANG_TEXTDOMAIN);
        }
    }

    /**
     * Filters the archive title.
     *
     * @since 4.1.0
     *
     * @param string $title Archive title to be displayed.
     */
    return apply_filters( 'get_the_archive_title', $title );
}

/**
 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
 *
 * @param int|null $length
 * @param string|null $more
 * @param int|null $post_id
 * @param bool $fallback_to_content use the post content if the excerpt is empty
 *
 * @return string
 */
function getTrimmedExcerpt($length = null,$more = null,$post_id = null, $fallback_to_content = false){
    if(!isset($length)){
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
    }else{
        $excerpt_length = $length;
    }
    if(!isset($more)){
        $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
    }else{
        $excerpt_more = $more;
    }

    if(is_string($fallback_to_content)){ //backward compatibility
        $fallback_to_content = false;
        if($fallback_to_content == "content_also"){
            $fallback_to_content = true;
        }elseif($fallback_to_content == "excerpt_only"){
            $fallback_to_content = false;
        }
    }

    if(isset($post_id)){
        $post = get_post($post_id);
        if($fallback_to_content && $post->post_excerpt == ""){
            $text = apply_filters('the_content', $post->post_content);
        }else{
            $text = $post->post_excerpt;
        }
    }else{
        global $post;
        if($fallback_to_content && $post->post_excerpt == ""){
            $text = get_the_content();
        }else{
            $text = get_the_excerpt();
        }
    }

    return wp_trim_words($text,$excerpt_length,$excerpt_more);
}

/**
 * Returns the first post link and/or post content without the link.
 * Used for the "Link" post format.
 *
 * @param string $output "link" or "post_content"
 * @return string Link or Post Content without link.
 */
function getFilteredLinkPostContent( $output = 'link') {
    $post_content = get_the_content();

    $link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"][^>]*>[^>]*>/is', $post_content, $matches );
    if($link){
        $link_url = $matches[1];
        $post_content = substr( $post_content, strlen( $matches[0] ) );
        if(!$post_content){
            $post_content = '';
        }
    }

    switch($output){
        case 'link':
            if($link && isset($link_url)){
                return $link_url;
            }
            return '';
            break;
        case 'post_content':
            return $post_content;
            break;
        default:
            return $post_content;
            break;
    }
}

/**
 * Returns the first valid oembed
 *
 * @return bool|string
 */
function getFirstVideo() {
    $first_oembed  = '';
    $custom_fields = get_post_custom();

    foreach ( $custom_fields as $key => $custom_field ) {
        if ( 0 !== strpos( $key, '_oembed_' ) ) continue;
        if ( $custom_field[0] == '{{unknown}}' ) continue;

        $first_oembed = $custom_field[0];

        $video_width  = (int) apply_filters( 'wb_video_width', 100 );
        $video_height = (int) apply_filters( 'wb_video_height', 480 );

        $first_oembed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_oembed );
        $first_oembed = preg_replace( '/<\/object>/','<param name="wmode" value="transparent" /></object>', $first_oembed );

        $first_oembed = preg_replace( "/width=\"[0-9]*\"/", "width={$video_width}%", $first_oembed );
        $first_oembed = preg_replace( "/height=\"[0-9]*\"/", "height={$video_height}", $first_oembed );

        break;
    }

    return ( '' !== $first_oembed ) ? $first_oembed : false;
}

/**
 * @param \WP_Post|\WP_Term $object
 * @return int
 */
function getObjectParentId($object): int {
    if($object instanceof \WP_Term) {
        return $object->parent;
    }elseif($object instanceof \WP_Post){
        return $object->post_parent;
    }else{
        throw new \RuntimeException('Unsupported object type');
    }
}

/**
 * Applies $getDataFunction to the provided $object and all of its parent returning the first non-false result.
 * @param \WP_Term|\WP_Post $object
 * @param callable $getDataFunction
 * @param string $defaultValue
 * @param bool $onlyTopMostParent
 * @return false|mixed|string
 */
function getObjectHierarchicalData($object, callable $getDataFunction, $defaultValue = '', bool $onlyTopMostParent = false){
    $parentId = getObjectParentId($object);
    $returnValue = $getDataFunction($object);
    if($returnValue !== false){
        if(!$onlyTopMostParent || ($onlyTopMostParent && $parentId === 0)){
            return $returnValue;
        }
    }
    $returnValue = $defaultValue;
    while(getObjectParentId($object) > 0){
        if($object instanceof \WP_Term) {
            $parent = get_term($object->parent,$object->taxonomy);
            if(!$parent instanceof \WP_Term){
                break;
            }
        }elseif($object instanceof \WP_Post){
            $parent = get_post($object->post_parent);
            if(!$parent instanceof \WP_Post){
                break;
            }
        }else{
            throw new \RuntimeException('Unsupported object type');
        }
        $returnValue = $getDataFunction($parent);
        if($returnValue !== false){
            if(!$onlyTopMostParent){
                break;
            }
            if($onlyTopMostParent && getObjectParentId($parent) === 0){
                break;
            }
        }
        $object = $parent;
    }
    return $returnValue;
}

/**
 * @use getObjectHierarchicalData()
 * @param \WP_Post $post
 * @param callable $getDataFunction
 * @param string|mixed $defaultValue
 * @param bool $onlyTopMostParent
 * @return false|mixed
 */
function getPostHierarchicalData(\WP_Post $post, callable $getDataFunction, $defaultValue = '', bool $onlyTopMostParent = false){
    return getObjectHierarchicalData($post,$getDataFunction,$defaultValue,$onlyTopMostParent);
}

/**
 * @use getObjectHierarchicalData()
 * @param \WP_Term $term
 * @param callable $getDataFunction
 * @param string|mixed $defaultValue
 * @param bool $onlyTopMostParent
 * @return false|mixed
 */
function getTermHierarchicalData(\WP_Term $term, callable $getDataFunction, $defaultValue = '', bool $onlyTopMostParent = false){
    return getObjectHierarchicalData($term,$getDataFunction,$defaultValue,$onlyTopMostParent);
}

/**
 * Return the $fieldKey value from $term or from one of its parents
 * @use getTermHierarchicalData()
 * @param \WP_Term $term
 * @param string $fieldKey
 * @return string
 */
function getHierarchicalTermACFField(\WP_Term $term, string $fieldKey): string {
    if(!function_exists('\get_field')){
        return '';
    }
    return getTermHierarchicalData($term, function(\WP_Term $term) use ($fieldKey){
        $fieldValue = \get_field($fieldKey, $term);
        if(\is_string($fieldValue) && !empty($fieldValue)){
            return $fieldValue;
        }
        return false;
    }, '');
}

/**
 * Return the $fieldKey value from $post or from one of its parents
 * @use getPostHierarchicalData()
 * @param \WP_Post $post
 * @param string $fieldKey
 * @return string
 */
function getHierarchicalPostACFField(\WP_Post $post, string $fieldKey): string {
    if(!function_exists('\get_field')){
        return '';
    }
    return getPostHierarchicalData($post, function(\WP_Post $post) use ($fieldKey){
        $fieldValue = \get_field($fieldKey, $post);
        if(\is_string($fieldValue) && !empty($fieldValue)){
            return $fieldValue;
        }
        return false;
    }, '');
}

/**
 * @param \WP_Term $term
 * @return \WP_Term|null
 */
function getTermMostTopParent(\WP_Term $term): ?\WP_Term {
    $currentTerm = $term;
    while($currentTerm->parent > 0){
        $currentTerm = get_term($currentTerm->parent,$currentTerm->taxonomy);
    }
    return $currentTerm;
}

/**
 * @param $taxonomy
 * @return \WP_Term|null
 */
function getCurrentTermMostTopParent($taxonomy): ?\WP_Term {
    $qo = get_queried_object();
    if($qo instanceof \WP_Term){
        return getTermMostTopParent($qo);
    }
    if($qo instanceof \WP_Post) {
        $terms = wp_get_post_terms($qo->ID,$taxonomy);
        if(\is_array($terms) && count($terms) > 0){
            return getTermMostTopParent($terms[0]);
        }
    }
    return null;
}

/**
 * Retrieve a post's terms as a list with specified format and in an hierarchical order
 *
 * @param int $id Post ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @param bool $linked
 *
 * @return string A list of terms on success, an empty string in case of failure or when no terms has been found.
 */
function getTheTermsListHierarchical( $id, $taxonomy, $before = '', $sep = '', $after = '', $linked = true ) {
    $terms = Utilities::getPostTermsHierarchical($id, $taxonomy);

    if( is_wp_error($terms) || empty($terms) ){
        return '';
    }

    $links = array();

    foreach ( $terms as $term ) {
        if($term instanceof \stdClass){
            $term = get_term($term->term_id,$taxonomy); //Restore the WP_Term
        }
        $link = get_term_link( $term, $taxonomy );
        if ( is_wp_error( $link ) ) {
            return $link;
        }
        if ($linked) {
            $links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
        }else{
            $links[] = $term->name;
        }
    }

    /**
     * Filter the term links for a given taxonomy.
     *
     * The dynamic portion of the filter name, `$taxonomy`, refers
     * to the taxonomy slug.
     *
     * @param array $links An array of term links.
     */
    $term_links = apply_filters( "term_links-$taxonomy", $links );

    return $before . join( $sep, $term_links ) . $after;
}

/**
 * @param string|array $size Image size. Accepts any valid image size, or an array of width and height values in pixels (in that order). Default 'full'.
 * @return string
 */
function getLogo($size = 'full'){
    $image = '';
    $customLogoId = get_theme_mod( 'custom_logo' );
    if(\is_numeric($customLogoId) && $customLogoId !== 0){
        $attachment = wp_get_attachment_image_src($customLogoId , $size);
        if(\is_array($attachment) && count($attachment) > 0){
            $image = $attachment[0];
        }
    }
    return (string) apply_filters('waboot/logo', $image);
}

/**
 * Display a modal with given label and content.
 *
 * @param string $modalLabel The label for the modal.
 * @param string $modalContent The content to be displayed inside the modal.
 * @return void
 */
function displayModal($modalLabel, $modalContent): void
{
?>
    <div class="modal" tabindex="-1" aria-labelledby="<?php echo esc_attr($modalLabel); ?>" aria-hidden="true">
        <div class="modal__overlay">
            <div class="modal__container">
                <button type="button" class="modal__close" data-dismiss="modal" aria-label="Close">&times;</button>
                <div class="modal__content">
                    <?php echo apply_filters('the_content', $modalContent); ?>
                </div>
            </div>
        </div>
    </div>
<?php
}


<?php
namespace Waboot\inc\core\utils;

trait WordPress {
    /**
     * Return a sanitized version of blog name
     *
     * @return string
     */
    public static function getSanitizedBlogname(){
        return sanitize_title_with_dashes(get_bloginfo("name"));
    }

    /**
     * Checks if we are in WP CLI
     *
     * @return bool
     */
    public static function isWPCli(){
        return defined("WP_CLI") && WP_CLI;
    }

    /**
     * Get post type accordingly provided object
     *
     * @param $object
     *
     * @return false|string
     */
    public static function getObjectPostType($object){
        if($object instanceof \WP_Term){
            return Utilities::getPostTypeByTerm($object);
        }elseif($object instanceof \WP_Taxonomy){
            return Utilities::getPostTypeByTaxonomy($object);
        }elseif($object instanceof \WP_Post_Type){
            return $object->name;
        }elseif($object instanceof \WP_Post){
            return $object->post_type;
        }
        return false;
    }

    /**
     * Alias of get_post_meta() that returns the meta unserialized and cache the results.
     *
     * @param int|object $post if object id provided, it checks
     *
     * @return array
     */
    public static function getPostMetas($post){
        $post_id = false;

        if(!is_numeric($post)){
            if($post instanceof \WP_Post){
                if(isset($post->id)){
                    $post_id = $post->id;
                }else{
                    $post_id = $post->ID;
                }
            }
        }else{
            $post_id = $post;
        }

        if(!$post_id) return [];

        static $cache;
        if(isset($cache[$post_id])) return $cache[$post_id];

        $metas = array_map(function($value){
            if(is_array($value) && isset($value[0])){
                return maybe_unserialize($value[0]);
            }else{
                return $value;
            }
        },get_post_meta($post_id));

        $cache[$post_id] = $metas;

        return $metas;
    }

    /**
     * Alias of get_post_meta($post_id,$key,true) that cache the result.
     *
     * @param int|object $post
     * @param string $key
     *
     * @return mixed
     */
    public static function getPostMeta($post,$key){
        $post_id = false;

        if(!is_numeric($post)){
            if($post instanceof \WP_Post){
                if(isset($post->id)){
                    $post_id = $post->id;
                }else{
                    $post_id = $post->ID;
                }
            }
        }else{
            $post_id = $post;
        }

        if(!$post_id) return [];

        static $cache = [];

        if(isset($cache[$post_id][$key])) return $cache[$post_id][$key];

        $meta = get_post_meta($post_id,$key,true);

        if($meta){
            $cache[$post_id][$key] = $meta;
        }

        return $meta;
    }

    /**
     * Get the src of the $post_id thumbnail
     *
     * @param $post_id
     * @param null $size
     * @return mixed
     */
    static function getPostThumbnailSrc($post_id, $size=null){
        $post_thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail = wp_get_attachment_image_src($post_thumbnail_id,$size);
        if(isset($thumbnail[0])){
            return $thumbnail[0];
        }
        return false;
    }

    /**
     * @param string $filePath
     * @param int $postId
     * @throws \RuntimeException
     * @return bool
     */
    public static function setFeaturedImageFromFilePath(string $filePath, int $postId): bool
    {
        $baseName = pathinfo($filePath,PATHINFO_BASENAME);
        $uploadDir = wp_upload_dir();
        $uniqueFileName = wp_unique_filename($uploadDir['path'],$baseName);
        $fileInUploadedFolderResult = wp_upload_bits($uniqueFileName,null,file_get_contents($filePath));
        if(isset($fileInUploadedFolderResult['error']) && $fileInUploadedFolderResult['error'] !== false){
            throw new \RuntimeException($fileInUploadedFolderResult['error']);
        }
        $filetype = wp_check_filetype($filePath );
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_title(pathinfo($filePath,PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        ];
        $attachmentId = wp_insert_attachment( $attachment, $fileInUploadedFolderResult['file']);
        if(\is_wp_error($attachmentId)){
            throw new \RuntimeException($attachmentId->get_error_message());
        }
        if(!function_exists('wp_generate_attachment_metadata')){
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        $attachData = wp_generate_attachment_metadata( $attachmentId, $fileInUploadedFolderResult['file']);
        if(!\is_array($attachData)){
            throw new \RuntimeException('Unable to generate metadata for attachment #'.$attachmentId.' ('.$fileInUploadedFolderResult['file'].')');
        }
        wp_update_attachment_metadata($attachmentId, $attachData);
        return (bool) set_post_thumbnail($postId, $attachmentId);
    }

    /**
     * Toggle maintenance mode for the site.
     *
     * Creates/deletes the maintenance file to enable/disable maintenance mode.
     *
     * @param bool $enable True to enable maintenance mode, false to disable.
     *
     * @extracted from 'class-wp-upgrader.php'
     */
    public static function toMaintenanceMode( $enable = false ) {
        $file = ABSPATH . '.maintenance';
        if ( $enable ) {
            // Create maintenance file to signal that we are upgrading
            $maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
            if(is_file($file)){
                unlink($file);
            }
            file_put_contents($file, $maintenance_string);
        } elseif ( ! $enable && is_file($file) ) {
            unlink($file);
        }
    }

    /**
     * Wrapper for 'wp_ajax_' 'wp_ajax_nopriv_' actions. It automatically tests for DOING_AJAX
     *
     * @param string $name
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     */
    public static function addAjaxEndpoint($name,$callback,$priority = 10,$accepted_args = 1){
        if(!is_callable($callback)){
            trigger_error('Invalid callback for ajax endpoint',E_USER_WARNING);
            return;
        }
        $wrapperCallback = function() use($callback){
            if(!defined('DOING_AJAX') || !DOING_AJAX) return;
            $callback();
        };
        add_action('wp_ajax_'.$name,$wrapperCallback,$priority,$accepted_args);
        add_action('wp_ajax_nopriv_'.$name,$wrapperCallback,$priority,$accepted_args);
    }

    /**
     * Sign-in an user by $login and $password
     *
     * @param string $login
     * @param string $password
     * @param bool $remember
     * @param bool $secure_cookie
     *
     * @return \WP_Error|\WP_User
     */
    public static function signinByCredentials($login,$password,$remember = true,$secure_cookie = true){
        $login = sanitize_user($login);
        $r = wp_signon([
            'user_login' => $login,
            'user_password' => $password,
            'remember' => $remember
        ],$secure_cookie);
        if($r instanceof \WP_User){
            wp_set_current_user($r->ID);
        }
        return $r;
    }

    /**
     * Sign-in an user by the provided $fieldKey and $fieldValue
     *
     * @param string $fieldKey (can be: 'id' || 'login' || 'email' || 'slug')
     * @param string $fieldValue
     * @param bool $remember
     * @param bool $secure_cookie
     *
     * @return \WP_Error|\WP_User
     */
    public static function signinBy($fieldKey,$fieldValue,$remember = true,$secure_cookie = true){
        $user = false;
        switch($fieldKey){
            case 'id':
                $fieldValue = (int) $fieldValue;
                $user = get_user_by('id',$fieldValue);
                break;
            case 'login':
                $fieldValue = sanitize_user($fieldValue);
                $user = get_user_by('login',$fieldValue);
                break;
            case 'email':
                $fieldValue = sanitize_email($fieldValue);
                $user = get_user_by('email',$fieldValue);
                break;
            case 'slug':
                $fieldValue = sanitize_text_field($fieldValue);
                $user = get_user_by('slug',$fieldValue);
                break;
        }
        if($user instanceof \WP_User){
            wp_set_auth_cookie($user->ID,$remember,$secure_cookie);
            do_action('wp_login', $user->user_login, $user);
            wp_set_current_user($user->ID);
            return $user;
        }
        return new \WP_Error('wbf_invalid_login','Invalid login');
    }

    /**
     * Add an admin notice
     *
     * @uses WBF\components\notices\Notice_Manager
     *
     * @param String $message
     * @param String $level (can be: "updated","error","nag")
     * @param array $args (category[default:base], condition[default:null], cond_args[default:null])
     */
    public static function addAdminNotice($message,$level = 'nag',$args = []){
        $args = wp_parse_args($args,[
            'wrapper_class' => 'waboot-notice',
        ]);

        add_action('admin_notices', function() use($message,$level,$args){
            ?>
            <div class="<?php echo $level ?> <?php echo $args['wrapper_class'] ?>">
                <p><?php echo $message; ?></p>
            </div>
            <?php
        });
    }

    /**
     * Alias of WP core function 'is_plugin_active' intended to be used when the latter is not available
     * @param string $plugin
     * @return bool
     */
    public static function isPluginActive(string $plugin): bool
    {
        return in_array($plugin, (array) get_option('active_plugins', []), true) || self::isPluginActiveForNetwork($plugin);
    }

    /**
     * @param string $plugin
     * @return bool
     */
    public static function isPluginActiveForNetwork(string $plugin): bool
    {
        if(!function_exists('is_multisite')){
            return false;
        }

        if(!is_multisite()){
            return false;
        }

        $plugins = get_site_option( 'active_sitewide_plugins' );
        if(isset( $plugins[$plugin])){
            return true;
        }

        return false;
    }
}
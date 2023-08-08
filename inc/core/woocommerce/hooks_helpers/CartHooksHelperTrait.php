<?php

namespace Waboot\inc\core\woocommerce\hooks_shortcuts;

trait CartHooksHelperTrait
{
    /**
     * @param callable $callback
     * @param int $priority
     * @return void
     */
    public function onCartUpdateInCartPage(callable $callback, int $priority = 10): void
    {
        add_action('init', function() use ($callback){
            if(isset($_POST['update_cart'])){
                $callback();
            }
        },$priority);
    }

    /**
     * @param callable $callback
     * @param int $priority
     * @return void
     */
    public function onCartUpdateDuringOrderReviewUpdate(callable $callback, int $priority = 10): void
    {
        add_action('init', function() use ($callback){
            if(defined('DOING_AJAX') && DOING_AJAX && isset($_POST['post_data'])){
                $callback();
            }
        }, $priority);
    }
}
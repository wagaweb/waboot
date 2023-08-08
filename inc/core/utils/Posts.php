<?php
namespace Waboot\inc\core\utils;

class Posts {
    /**
     * @param int $pageId
     * @return false|int
     */
    public static function getPostTopAncestorId(int $pageId)
    {
        $ancestors = get_post_ancestors($pageId);
        if(!\is_array($ancestors) || count($ancestors) === 0){
            return false;
        }
        $topLevelAncestorId = false;
        foreach ($ancestors as $ancestorId) {
            if(wp_get_post_parent_id($ancestorId) > 0){
                continue;
            }
            $topLevelAncestorId = $ancestorId;
        }
        return $topLevelAncestorId;
    }

    /**
     * @param $pageId
     * @return bool
     */
    public static function pageHasChildren($pageId): bool
    {
        return count(get_pages(['child_of' => $pageId])) > 0;
    }

    /**
     * Get the full page hierarchy starting from any page in the hierarchy
     * @param int $startingPostId
     * @param bool $includeSelf
     * @param bool $generateFullTree
     * @return array
     */
    public static function getPostsTreeByPostId(int $startingPostId, $includeSelf = true, $generateFullTree = true): array
    {
        $currentQueriedObjectId = get_queried_object_id();
        $currentObjectAncestorIds = [];
        if(is_page($currentQueriedObjectId)){
            $currentObjectAncestorIds = get_post_ancestors($startingPostId);
        }

        if($generateFullTree && self::hasPostParent($startingPostId)){
            $mainParentId = self::getPostTopAncestorId($startingPostId);
        }else{
            $mainParentId = $startingPostId;
        }

        $generateTree = static function($parentId,$currentLevel = 1) use (&$generateTree,$currentObjectAncestorIds,$startingPostId){
            $pageTree = [];
            $pages = get_pages([
                'child_of' => $parentId,
                'sort_column' => 'menu_order'
            ]);
            foreach ($pages as $page){
                if($page->post_parent !== $parentId) {
                    continue;
                }
                $newEl = [
                    'post' => $page,
                    'attr' => [
                        'active' => \in_array($page->ID,$currentObjectAncestorIds,true) || $page->ID === $startingPostId,
                        'current' => $page->ID === $startingPostId
                    ],
                    'children' => []
                ];
                if(self::pageHasChildren($page->ID)){
                    $newEl['children'] = $generateTree($page->ID, $currentLevel + 1); //Recursion
                }
                $pageTree[] = $newEl;
            }
            return $pageTree;
        };

        $tree = $generateTree($mainParentId);
        if(!\is_array($tree)){
            $tree = [];
        }
        if(!$includeSelf){
            return $tree;
        }
        return [
            [
                'post' => get_post($mainParentId),
                'attr' => [
                    'active' => \in_array($mainParentId,$currentObjectAncestorIds,true) || $mainParentId === $startingPostId,
                    'current' => $mainParentId === $startingPostId
                ],
                'children' => $tree
            ]
        ];
    }

    /**
     * @param int $postId
     * @return bool
     */
    public static function hasPostParent(int $postId): bool
    {
        if(\function_exists('\has_post_parent')){
            return has_post_parent($postId);
        }
        $wpPost = get_post($postId);
        $wpPostParent = !empty($wpPost->post_parent) ? get_post($wpPost->post_parent) : null;
        return (bool) $wpPostParent;
    }

    /**
     * @param string $metaKey
     * @param string $metaValue
     * @return int|null
     */
    public static function getPostIdByMeta(string $metaKey, string $metaValue): ?int
    {
        global $wpdb;
        $sql = <<<SQL
select pm.post_id from $wpdb->postmeta pm where pm.meta_key = %s and pm.meta_value = %s
SQL;
        $res = $wpdb->get_var($wpdb->prepare($sql, $metaKey, $metaValue));
        return $res === null ? null : (int) $res;
    }

    /**
     * @param int $postId
     * @return int|null
     */
    public static function getPostParentId(int $postId): ?int
    {
        global $wpdb;
        $posts_table = $wpdb->prefix."posts";
        $parentId = $wpdb->get_var("SELECT post_parent FROM {$posts_table} WHERE ID = {$postId}");
        if(!\is_string($parentId)){
            return null;
        }
        return (int) $parentId;
    }
}
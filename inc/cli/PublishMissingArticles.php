<?php

namespace waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;

class PublishMissingArticles extends AbstractCommand
{
    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Tries to publish missed articles';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp publish-missed-posts';
        return $description;
    }

    public function run(array $args, array $assoc_args): int
    {
        global $wpdb;
        $missedScheduledIds = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_date <= %s AND post_status = 'future'",
                current_time( 'mysql', 0 )
            )
        );
        if(!\is_array($missedScheduledIds) || count($missedScheduledIds) === 0){
            return 0;
        }
        foreach($missedScheduledIds as $missedPostId){
            $this->log('Publishing missed post: #'.$missedPostId);
            wp_publish_post($missedPostId);
        }
        $this->success('Operation completed');
        return 0;
    }
}
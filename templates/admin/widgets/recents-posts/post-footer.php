<?php if(!$settings['date_relative']): ?>
	<?php \Waboot\hooks\entry\display_post_date() ?>
<?php else : ?>
	<?php \Waboot\hooks\entry\display_post_date(true) ?>
<?php endif; ?>
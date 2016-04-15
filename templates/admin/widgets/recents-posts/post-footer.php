<?php if(!$settings['date_relative']): ?>
	<?php waboot_do_posted_on() ?>
<?php else : ?>
	<?php waboot_do_posted_on(true) ?>
<?php endif; ?>
<?php $tpl = \Waboot\inc\core\getArchiveTemplate(); ?>
<?php if(!empty($tpl)) : ?>
    <?php \Waboot\inc\core\Waboot()->renderView($tpl,[]); ?>
<?php else: ?>
    <?php \Waboot\inc\core\Waboot()->renderView('templates/archive/archive.php',[]); ?>
<?php endif; ?>

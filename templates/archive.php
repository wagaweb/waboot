<?php $vars = \Waboot\functions\get_archives_template_vars(); ?>

<?php if($vars['display_page_title']) : ?>
    <?php \Waboot\template_tags\archive_page_title(); ?>
<?php endif; ?>
<?php if(!empty($vars['tpl'])) : ?>
    <?php try{ (new \WBF\components\mvc\HTMLView($vars['tpl']))->display(['tpl_vars' => $vars]); } catch (\Exception $e){ echo $e->getMessage(); } ?>
<?php else: ?>
    <?php try{ (new \WBF\components\mvc\HTMLView('templates/archive/archive.php'))->display(['tpl_vars' => $vars]); } catch (\Exception $e){ $e->getMessage(); } ?>
<?php endif; ?>
<?php

use Waboot\addons\packages\shop_rules\ShopRule;

/** @var array $gwpChoices */

?>
<?php foreach ($gwpChoices as $c): ?>
<?php
/** @var ShopRule $rule */
$rule = $c['rule'];
?>
<div id="gwp-choice-modal" class="gwp-choice__modal gwp-choice__modal--<?php echo $rule->getKey(); ?>">
    <div class="gwp-choice__inner">
        <form action="" method="post">
            <?php echo $c['template']; ?>
            <input type="submit" value="Aggiungi" class="btn">
        </form>
    </div>
</div>
<?php endforeach; ?>

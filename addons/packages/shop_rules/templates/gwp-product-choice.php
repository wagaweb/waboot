<?php

use Waboot\addons\packages\shop_rules\ShopRule;

/** @var $r ShopRule */
/** @var $res array */
/** @var $gwpProducts WC_Product[] */

$key = $r->getKey();
$maxGifts = $res['choiceQuantity'];
$checkboxName = sprintf('%s[gwp-choice][]', $key);

?>

<div data-shop-rule="<?php echo $key; ?>">
    <p class="gwp-choice__title">
        <?php printf('Scelga %d %s', $maxGifts, $maxGifts > 1 ? 'omaggi' : 'omaggio'); ?>
    </p>
    <div class="gwp-choice__items">
        <?php foreach ($gwpProducts as $p): ?>
            <div class="gwp-choice">
                <input type="checkbox"
                       name="<?php echo $checkboxName; ?>"
                       value="<?php echo $p->get_id(); ?>"
                       id="gwp-<?php echo $p->get_id(); ?>"
                >
                <label class="gwp-choice__item" for="gwp-<?php echo $p->get_id(); ?>">
                    <div class="gwp-choice__image">
                        <?php echo $p->get_image('thumbnail'); ?>
                    </div>
                    <div class="gwp-choice__text">
                        <?php echo $p->get_name(); ?>
                    </div>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        const checkboxName = "<?php echo $checkboxName; ?>";
        const maxChoiceNumber = <?php echo $maxGifts; ?>;
        $(`[name="${checkboxName}"]`).on('change', function () {
            const checkedChoices = $(`[name="${checkboxName}"]:checked`);
            const notCheckedChoices = $(`[name="${checkboxName}"]:not(:checked)`);
            if (checkedChoices.length >= maxChoiceNumber) {
                notCheckedChoices.prop('disabled', true);
                return;
            }

            notCheckedChoices.prop('disabled', false);
        });
    })(jQuery);
</script>

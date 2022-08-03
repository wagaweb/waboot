<div class="gwp-container">
    <h5>In omaggio</h5>

    <?php foreach ($gwpChoices as $c): ?>
        <?php
        /** @var ShopRule $rule */
        $rule = $c['rule'];
        ?>
        <a data-modal-target="gwp-choice__modal--<?php echo $rule->getKey(); ?>" class="gwp-container__message">
            <!-- Scegli l'omaggio a te riservato -->
            <?php echo $rule->getName(); ?> in Omaggio
            <span>Scegli ora</span>
        </a>
        <?php // echo $c['template']; ?>
    <?php endforeach; ?>
</div>






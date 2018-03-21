<?php if($navbar_position_below) : ?>

    <div id="headerflex__wrapper" class="headerflex__wrapper navbar--<?php echo $navbar_position; ?>">
        <div class="<?php echo $header_width; ?>">
            <div class="headerflex__inner header__logo--<?php echo $logo_position; ?>">
                <?php echo $header_content; ?>
                <?php echo $navbar_toggler; ?>

                <?php if ( is_active_sidebar( 'header-left' ) ) : ?>
                    <div class="header__widgetarea header--left">
                        <?php dynamic_sidebar( 'header-left' ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( is_active_sidebar( 'header-right' ) ) : ?>
                    <div class="header__widgetarea header--right">
                        <?php dynamic_sidebar( 'header-right' ); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div id="navbar__wrapper" class="navbar__wrapper">
        <div class="<?php echo $navbar_width; ?>">
            <div class="navbar__inner">
                <?php echo $navbar_content; ?>
            </div>
        </div>
    </div>

<?php else : ?>

    <div id="header__wrapper" class="header__wrapper navbar--<?php echo $navbar_position; ?>">
        <div class="<?php echo $header_width; ?>">
            <div class="headerflex__inner">
                <?php echo $header_content; ?>
                <?php echo $navbar_toggler; ?>
                <?php if($navbar_position_below == false) { echo $navbar_content; } ?>
            </div>
        </div>
    </div>

<?php endif;
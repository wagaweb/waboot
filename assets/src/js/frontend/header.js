import $ from 'jquery';

/**
 * @param {string} selector
 */
export function initHeader(selector) {
  mainPadding();
  headerFixedWhenBack();
  backOnSubmenu();
  keyboardSubmenu();
  mobileDropdown(selector);
  closeSubmenuOnFocusOut();

  $(window).on("scroll", () => {
    headerFixed();
  });

  $(window).on("resize", () => {
    mainPadding();
  });
}

function mainPadding() {
  let $headerHeight = $(".header").outerHeight();
  $(".main").css("padding-top", $headerHeight);
}

function headerFixed() {
  let scroll = $(window).scrollTop(),
      header = $(".header"),
      headerHeight = header.outerHeight();

  if (scroll > headerHeight) {
    $("body").addClass("header--fixed");
  } else {
    $("body").removeClass("header--fixed");
  }

  if (scroll > headerHeight * 2) {
    $("body").addClass("header--animated");
  } else {
    $("body").removeClass("header--animated");
  }

  if (scroll > headerHeight * 3) {
    $("body").addClass("header--scrolled");
  } else {
    $("body").removeClass("header--scrolled");
  }
}

function headerFixedWhenBack() {
  const body = document.body;
  const scrollUp = "scroll-up";
  const scrollDown = "scroll-down";
  let lastScroll = 0;

  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;
    if (currentScroll <= 0) {
      body.classList.remove(scrollUp);
      return;
    }

    if (currentScroll > lastScroll && !body.classList.contains(scrollDown)) {
      body.classList.remove(scrollUp);
      body.classList.add(scrollDown);
    } else if (currentScroll < lastScroll && body.classList.contains(scrollDown)) {
      body.classList.remove(scrollDown);
      body.classList.add(scrollUp);
    }
    lastScroll = currentScroll;
  });
}

function backOnSubmenu() {
  $('.navigation-mobile .sub-menu').prepend('<button class="backlevel__icon"><i class="far fa-angle-left"></i></button>');
  $('.navigation-mobile .menu-item-has-children').append('<button class="sublevel__icon" aria-haspopup="true" aria-expanded="false"><i class="far fa-angle-right"></i></button>');
}

/**
 * Attiva sottomenu con tastiera solo con invio
 */
function keyboardSubmenu() {
  $('.sublevel__icon').on('keydown', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();

      const $btn = $(this);
      const $li = $btn.closest('li');
      const $submenu = $li.find('> .sub-menu');

      const alreadyOpen = $li.hasClass('submenu-open');

      // Chiude solo i sottomenu fratelli allo stesso livello
      const $siblings = $li.siblings('.submenu-open');
      $siblings.removeClass('submenu-open').find('.sublevel__icon').attr('aria-expanded', 'false');

      if (!alreadyOpen) {
        $li.addClass('submenu-open');
        $btn.attr('aria-expanded', 'true');

        const $firstLink = $submenu.find('a').first();
        if ($firstLink.length) {
          $firstLink.focus();
        }
      }
    }
  });
}

function closeSubmenuOnFocusOut() {
  $('.menu-item-has-children').on('focusout', function () {
    const $li = $(this);
    setTimeout(() => {
      if (!$li.find(':focus').length) {
        $li.removeClass('submenu-open');
        $li.find('.sublevel__icon').attr('aria-expanded', 'false');
      }
    }, 10);
  });
}


function mobileDropdown(el) {
  if ($(el).length > 0) {
    $(el + ' > .sublevel__icon').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      let $target = $(e.currentTarget),
          $submenu = $target.prev('.sub-menu');
      $submenu.css('left', 0);
    });

    $(el + ' > a').on('click', function (e) {
      let my_href = $(this).attr("href");
      if (my_href === '#') {
        e.preventDefault();
        e.stopPropagation();
        let $target = $(e.currentTarget),
            $submenu = $target.next('.sub-menu');
        $submenu.css('left', 0);
      }
    });

    $(el + ' > ul .backlevel__icon').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      let $target = $(e.currentTarget).parent('ul');
      $target.css('left', '100%');
    });

    $('[data-slidein-close]').on('click', function () {
      $('.navigation-mobile .sub-menu').css('left', '100%');
    });
  }
}

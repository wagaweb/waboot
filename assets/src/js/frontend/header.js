import $ from 'jquery';

/**
 *
 * @param {string} selector
 */
export function initHeader(selector) {
  mainPadding();
  headerFixedWhenBack();
  backOnSubmenu();
  mobileDropdown(selector);

  initMegaMenu(selector);

  navigationAccessibility();

  $(window).on('scroll', () => {
    headerFixed();
  });

  $(window).on('resize', () => {
    mainPadding();
  });
}

function mainPadding() {
  let $headerHeight = $('.header').outerHeight();
  $('.main').css('padding-top', $headerHeight);
}

function headerFixed() {
  let scroll = $(window).scrollTop(),
    header = $('.header'),
    headerHeight = header.outerHeight();
  if (scroll > headerHeight) {
    $('body').addClass('header--fixed');
  } else {
    $('body').removeClass('header--fixed');
  }
  if (scroll > headerHeight * 2) {
    $('body').addClass('header--animated');
  } else {
    $('body').removeClass('header--animated');
  }
  if (scroll > headerHeight * 3) {
    $('body').addClass('header--scrolled');
  } else {
    $('body').removeClass('header--scrolled');
  }
}

function headerFixedWhenBack() {
  const body = document.body;
  const scrollUp = 'scroll-up';
  const scrollDown = 'scroll-down';
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    if (currentScroll <= 0) {
      body.classList.remove(scrollUp);
      return;
    }

    if (
      currentScroll > lastScroll &&
      !body.classList.contains(scrollDown)
    ) {
      // down
      body.classList.remove(scrollUp);
      body.classList.add(scrollDown);
    } else if (
      currentScroll < lastScroll &&
      body.classList.contains(scrollDown)
    ) {
      // up
      body.classList.remove(scrollDown);
      body.classList.add(scrollUp);
    }
    lastScroll = currentScroll;
  });
}

function backOnSubmenu() {
  $('.navigation-mobile .sub-menu').prepend(
    '<span class="backlevel__icon"><i class="far fa-angle-left"></i></span>'
  );
  $('.navigation-mobile .menu-item-has-children').append(
    '<span class="sublevel__icon"><i class="far fa-angle-right"></i></span>'
  );
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
      let my_href = $(this).attr('href');
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

function initMegaMenu(selector) {
  const $menu = $(selector);

  $menu.find('li.has-megamenu').each(function () {
    const $li = $(this);
    const $trigger = $li.children('a');
    const $megamenu = $li.find('.mega-menu');

    if ($megamenu.length) {
      $li.on('mouseenter', function () {
        $megamenu.addClass('show');
      });

      $trigger.on('focus', function () {
        $megamenu.addClass('show');
        const $firstFocusable = $megamenu
          .find('a, button, [tabindex="0"]')
          .first();
        if ($firstFocusable.length) {
          $firstFocusable.focus();
        }
      });

      $li.on('mouseleave', function () {
        $megamenu.removeClass('show');
      });

      $li.on('focusout', function () {
        setTimeout(function () {
          if (!$li.is(':focus-within')) {
            $megamenu.removeClass('show');
          }
        }, 10);
      });
    }
  });
}

function navigationAccessibility() {
  const $menu = $('.header__navigation .navigation');

  // Inizializza tutti i sub-menu
  $menu.find('.sub-menu').attr({
    'aria-hidden': 'true',
    'inert': ''
  });

  // Inizializza aria-expanded=false su tutti gli <a> dentro .menu-item-has-children
  $menu.find('.menu-item-has-children > a').attr('aria-expanded', 'false');

  // Gestisce gli attributi ARIA e inert dei sub-menu
  $menu.find('.menu-item-has-children').each(function() {
    const $parent = $(this);
    const $submenu = $parent.children('.sub-menu').first();

    $parent.on('mouseenter focusin', function() {
      $submenu.attr('aria-hidden', 'false').removeAttr('inert');
    });

    $parent.on('mouseleave focusout', function(e) {
      if (!$parent.has(e.relatedTarget).length) {
        $submenu.attr('aria-hidden', 'true').attr('inert', '');
      }
    });
  });
}
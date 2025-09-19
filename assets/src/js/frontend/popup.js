import $ from 'jquery';

export default class Popup {
    constructor(selector = '.custom-popup') {
        this.$popups = $(selector);
    }

    init() {
        this.$popups.each((_, el) => {
            const $popup = $(el);
            const id = $popup.attr('id');

            const delay = parseInt($popup.data('delay')) * 1000 || 3000;
            const maxViews = parseInt($popup.data('maxviews')) || 1;
            const offset = parseInt($popup.data('offset')) || 16;
            const width = parseInt($popup.data('width')) || 600;
            const padding = parseInt($popup.data('padding')) || 16;
            const maxHeight = parseInt($popup.data('maxheight')) || null;

            const views = parseInt(localStorage.getItem(`popup_${id}`)) || 0;
            if(views >= maxViews) return;

            const $content = $popup.find('.custom-popup__content');

            $content.css({
                maxWidth: width + 'px',
                padding: padding + 'px',
                maxHeight: maxHeight ? maxHeight + 'px' : 'none',
                transform: 'translateY(100px) scale(0.8)',
                opacity: 0
            });

            const pos = $popup.data('position') || 'center';
            $content.css({ top: '', bottom: '', left: '', right: '', transform: '' });

            switch(pos) {
                case 'top-left':
                    $content.css({ top: offset+'px', left: offset+'px', transform: 'none' });
                    break;
                case 'top-center':
                    $content.css({ top: offset+'px', left: '50%', transform: 'translateX(-50%)' });
                    break;
                case 'top-right':
                    $content.css({ top: offset+'px', right: offset+'px', transform: 'none' });
                    break;
                case 'center-left':
                    $content.css({ top: '50%', left: offset+'px', transform: 'translateY(-50%)' });
                    break;
                case 'center':
                    // Calcolo dinamico centrato
                    const winH = $(window).height();
                    const winW = $(window).width();
                    const contentH = $content.outerHeight();
                    const contentW = $content.outerWidth();
                    const top = Math.max((winH - contentH) / 2, offset);
                    const left = Math.max((winW - contentW) / 2, offset);
                    $content.css({ top: top+'px', left: left+'px', transform: 'none' });
                    break;
                case 'center-right':
                    $content.css({ top: '50%', right: offset+'px', transform: 'translateY(-50%)' });
                    break;
                case 'bottom-left':
                    $content.css({ bottom: offset+'px', left: offset+'px', transform: 'none' });
                    break;
                case 'bottom-center':
                    $content.css({ bottom: offset+'px', left: '50%', transform: 'translateX(-50%)' });
                    break;
                case 'bottom-right':
                    $content.css({ bottom: offset+'px', right: offset+'px', transform: 'none' });
                    break;
                default:
                    const defaultTop = Math.max(( $(window).height() - $content.outerHeight() ) / 2, offset);
                    const defaultLeft = Math.max(( $(window).width() - $content.outerWidth() ) / 2, offset);
                    $content.css({ top: defaultTop+'px', left: defaultLeft+'px', transform: 'none' });
            }

            setTimeout(() => {
                $popup.addClass('is-visible');
                $content.css({ transform: 'translateY(0) scale(1)', opacity: 1 });
            }, delay);

            $popup.find('.custom-popup__close, .custom-popup__overlay').on('click', () => {
                $popup.removeClass('is-visible');
                localStorage.setItem(`popup_${id}`, views + 1);
            });

            // Aggiorna centratura al resize
            $(window).on('resize', () => {
                if(pos === 'center') {
                    const winH = $(window).height();
                    const winW = $(window).width();
                    const contentH = $content.outerHeight();
                    const contentW = $content.outerWidth();
                    const top = Math.max((winH - contentH) / 2, offset);
                    const left = Math.max((winW - contentW) / 2, offset);
                    $content.css({ top: top+'px', left: left+'px' });
                }
            });
        });
    }
}

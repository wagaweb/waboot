import $ from 'jquery';

export class Modal {
    constructor() {
        this.modalButton = $('[data-toggle="modal"]');
        this.closeButton = $('.modal__close');
        this.overlay = $('.modal__overlay');

        this.init();
    }

    init() {
        this.modalButton.on('click', this.handleOpenModal.bind(this));
        this.closeButton.on('click', this.handleCloseModal.bind(this));
        $(document.body).on('keydown', this.handleEscKey.bind(this));
        this.overlay.on('click', this.handleOverlayClick.bind(this));
    }

    handleOpenModal(event) {
        const targetModalId = $(event.currentTarget).data('target');
        const targetModal = $('.modal').filter(`[aria-labelledby="${targetModalId}"]`);

        if (targetModal.length) {
            targetModal.attr('aria-hidden', 'false');
            $(document.body).addClass('modal-open');
        }
    }

    handleCloseModal(event) {
        const modal = $(event.currentTarget).closest('.modal');
        modal.attr('aria-hidden', 'true');
        $(document.body).removeClass('modal-open');
    }

    handleEscKey(event) {
        if (event.keyCode === 27) {
            const openModals = $('.modal[aria-hidden="false"]');
            if (openModals.length > 0) {
                const closeButton = openModals.last().find('.modal__close');
                closeButton.trigger('click');
            }
        }
    }

    handleOverlayClick(event) {
        if ($(event.target).hasClass('modal__overlay')) {
            this.handleCloseModal(event);
        }
    }
}

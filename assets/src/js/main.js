import Header from "./frontend/header";
import { dropdown } from "./frontend/select";
import { Slidein } from "./frontend/slidein.js";
import "../sass/main.scss";

jQuery.fn.slidein = function (options) {
	return this.each(function () {
		if (!jQuery.data(this, "slidein")) {
			jQuery.data(this, "slidein", new Slidein(this, options));
		}
	});
};

jQuery(document).ready(function ($) {
	"use strict";

	new Header(".menu-item-has-children");

	asideBodyClass();
	scrollToAnimate();
	dropdown();
	headerToggleAnimation();
	featuredPostsCarousel();

	$(window).on("load", function () {
		if (window.matchMedia("(max-width: 991px)").matches) {
		}
	});

	$("[data-slidein-nav]").slidein({
		toggler: ".slidein-nav__toggle",
	});

	$("[data-slidein-search]").slidein({
		toggler: ".slidein-search__toggle",
	});

	$("a").each(function () {
		var my_href = $(this).attr("href");
		if (/\.(?:jpg|jpeg|gif|png)/i.test(my_href)) {
			$(this).addClass("glightbox");
		}
	});
	const lightbox = GLightbox({
		selector: ".glightbox",
		touchNavigation: true,
		keyboardNavigation: true,
		loop: true,
		autoplayVideos: true,
		zoomable: true,
	});
});

function asideBodyClass() {
	let $ = jQuery;
	if ($(".main__aside").length > 0) {
		$("body").addClass("with-sidebar");
	}
}

function scrollToAnimate() {
	let $ = jQuery;
	let $header = $(".site-header").height();
	$('a[href^="#"]').on("click", function (event) {
		let target = $(this.getAttribute("href"));
		if (target.length) {
			event.preventDefault();
			$("html, body")
				.stop()
				.animate(
					{
						scrollTop: target.offset().top - $header,
					},
					1000
				);
		}
	});
}

function headerToggleAnimation() {
	let $ = jQuery;
	$("body").on("slidein-in", function (event, $slideElement) {
		if ($slideElement.hasClass("navigation-mobile")) {
			$(".header__toggle").toggleClass("animated");
			$(".header__logo").toggleClass("centered");
		}
	});
	$("body").on("slidein-out", function (event, $slideElement) {
		if ($slideElement.hasClass("navigation-mobile")) {
			$(".header__toggle").toggleClass("animated");
			$(".header__logo").toggleClass("centered");
		}
	});
}

function featuredPostsCarousel() {
	if (document.querySelector(".posts-carousel")) {
		const featuredPostsCarousel = tns({
			container: ".posts-carousel .wp-block-post-template",
			items: 1,
			autoplay: true,
			autoplayButtonOutput: false,
			mode: "gallery",
			mouseDrag: true,
			controlsText: [
				'<i class="far fa-angle-left"></i>',
				'<i class="far fa-angle-right"></i>',
			],
			responsive: {
				0: {
					controls: false,
				},
				1024: {
					controls: true,
				},
			},
		});
	}
}

import Header from "./frontend/header";
import { dropdown } from "./frontend/select";
import { Slidein } from "./frontend/slidein.js";

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

	$(window).on("load", function () {
		if (window.matchMedia("(max-width: 991px)").matches) {
		}
	});

	$("[data-slidein-nav]").slidein({
		toggler: ".slidein-nav__toggle",
	});

	/* $("a").each(function () {
    var my_href = $(this).attr("href");
    if (/\.(?:jpg|jpeg|gif|png)/i.test(my_href)) {
      $(this).addClass("venobox");
    }
  });
  $(".venobox").venobox(); */
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

document.addEventListener("DOMContentLoaded", function (event) {
	const lightbox = GLightbox({
		selector: "figure:not(.article__image) a",
		touchNavigation: true,
		keyboardNavigation: true,
		loop: true,
		autoplayVideos: true,
		zoomable: true,
	});
});

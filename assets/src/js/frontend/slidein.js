let $ = jQuery;
let defaults = {
	toggler: "#slidein-toggle",
};

function Slidein(element, options) {
	this.$el = $(element);
	this.opt = $.extend(true, {}, defaults, options);
	this.$toggler = $(this.opt.toggler);

	this.init(this);
}

Slidein.prototype = {
	init: function (self) {
		self.initToggle(self);
		self.initDropdown(self);
	},

	initToggle: function (self) {
		$(self.opt.toggler).on("click", function (e) {
			if (!self.$el.hasClass("show")) {
				self.$el.addClass("show");
				$("body").addClass("slidein-no-scroll");
				self.toggleOverlay(self.$el);

				$(document).on("click", function (e) {
					var $target = $(e.target);
					if (
						self.$el.is($target) ||
						self.$el.find($target).length > 0
					) {
						return;
					}
					if (!$target.closest(self.$toggler).is(self.$toggler)) {
						self.$el.removeClass("show");
						$("body").removeClass("slidein-no-scroll");

						self.hideOverlay(self.$el);

						$(document).off("click");
					}
				});

				$("[data-slidein-close]").on("click", function () {
					self.$el.removeClass("show");
					$("body").removeClass("slidein-no-scroll");
					self.hideOverlay(self.$el);
					$(document).off("click");
				});
			} else {
			}
		});
	},

	initDropdown: function (self) {
		self.$el.on("click", "[data-slidein-dropdown-toggle]", function (e) {
			var $this = $(this);

			$this.next("[data-slidein-dropdown]").slideinToggle("fast");

			$this.find("[data-slidein-dropdown-icon]").toggleClass("show");

			e.preventDefault();
		});
	},

	toggleOverlay: function ($slideElement) {
		var $overlay = $("[data-slidein-overlay]");

		if (!$overlay[0]) {
			$overlay = $('<div data-slidein-overlay class="slidein-overlay"/>');
			$("body").append($overlay);
		}

		if ($overlay.is(":visible")) {
			$overlay.fadeOut("fast");
			$("body").trigger("slidein-out", [$slideElement]);
		} else {
			$overlay.fadeIn("fast");
			$("body").trigger("slidein-in", [$slideElement]);
		}
	},

	hideOverlay: function ($slideElement) {
		$("[data-slidein-overlay]").fadeOut("fast");
		$("body").trigger("slidein-out", [$slideElement]);
	},
};

export { Slidein };

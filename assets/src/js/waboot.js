(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
;(function () {
	'use strict';

	/**
	 * @preserve FastClick: polyfill to remove click delays on browsers with touch UIs.
	 *
	 * @codingstandard ftlabs-jsv2
	 * @copyright The Financial Times Limited [All Rights Reserved]
	 * @license MIT License (see LICENSE.txt)
	 */

	/*jslint browser:true, node:true*/
	/*global define, Event, Node*/


	/**
	 * Instantiate fast-clicking listeners on the specified layer.
	 *
	 * @constructor
	 * @param {Element} layer The layer to listen on
	 * @param {Object} [options={}] The options to override the defaults
	 */
	function FastClick(layer, options) {
		var oldOnClick;

		options = options || {};

		/**
		 * Whether a click is currently being tracked.
		 *
		 * @type boolean
		 */
		this.trackingClick = false;


		/**
		 * Timestamp for when click tracking started.
		 *
		 * @type number
		 */
		this.trackingClickStart = 0;


		/**
		 * The element being tracked for a click.
		 *
		 * @type EventTarget
		 */
		this.targetElement = null;


		/**
		 * X-coordinate of touch start event.
		 *
		 * @type number
		 */
		this.touchStartX = 0;


		/**
		 * Y-coordinate of touch start event.
		 *
		 * @type number
		 */
		this.touchStartY = 0;


		/**
		 * ID of the last touch, retrieved from Touch.identifier.
		 *
		 * @type number
		 */
		this.lastTouchIdentifier = 0;


		/**
		 * Touchmove boundary, beyond which a click will be cancelled.
		 *
		 * @type number
		 */
		this.touchBoundary = options.touchBoundary || 10;


		/**
		 * The FastClick layer.
		 *
		 * @type Element
		 */
		this.layer = layer;

		/**
		 * The minimum time between tap(touchstart and touchend) events
		 *
		 * @type number
		 */
		this.tapDelay = options.tapDelay || 200;

		/**
		 * The maximum time for a tap
		 *
		 * @type number
		 */
		this.tapTimeout = options.tapTimeout || 700;

		if (FastClick.notNeeded(layer)) {
			return;
		}

		// Some old versions of Android don't have Function.prototype.bind
		function bind(method, context) {
			return function() { return method.apply(context, arguments); };
		}


		var methods = ['onMouse', 'onClick', 'onTouchStart', 'onTouchMove', 'onTouchEnd', 'onTouchCancel'];
		var context = this;
		for (var i = 0, l = methods.length; i < l; i++) {
			context[methods[i]] = bind(context[methods[i]], context);
		}

		// Set up event handlers as required
		if (deviceIsAndroid) {
			layer.addEventListener('mouseover', this.onMouse, true);
			layer.addEventListener('mousedown', this.onMouse, true);
			layer.addEventListener('mouseup', this.onMouse, true);
		}

		layer.addEventListener('click', this.onClick, true);
		layer.addEventListener('touchstart', this.onTouchStart, false);
		layer.addEventListener('touchmove', this.onTouchMove, false);
		layer.addEventListener('touchend', this.onTouchEnd, false);
		layer.addEventListener('touchcancel', this.onTouchCancel, false);

		// Hack is required for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
		// which is how FastClick normally stops click events bubbling to callbacks registered on the FastClick
		// layer when they are cancelled.
		if (!Event.prototype.stopImmediatePropagation) {
			layer.removeEventListener = function(type, callback, capture) {
				var rmv = Node.prototype.removeEventListener;
				if (type === 'click') {
					rmv.call(layer, type, callback.hijacked || callback, capture);
				} else {
					rmv.call(layer, type, callback, capture);
				}
			};

			layer.addEventListener = function(type, callback, capture) {
				var adv = Node.prototype.addEventListener;
				if (type === 'click') {
					adv.call(layer, type, callback.hijacked || (callback.hijacked = function(event) {
						if (!event.propagationStopped) {
							callback(event);
						}
					}), capture);
				} else {
					adv.call(layer, type, callback, capture);
				}
			};
		}

		// If a handler is already declared in the element's onclick attribute, it will be fired before
		// FastClick's onClick handler. Fix this by pulling out the user-defined handler function and
		// adding it as listener.
		if (typeof layer.onclick === 'function') {

			// Android browser on at least 3.2 requires a new reference to the function in layer.onclick
			// - the old one won't work if passed to addEventListener directly.
			oldOnClick = layer.onclick;
			layer.addEventListener('click', function(event) {
				oldOnClick(event);
			}, false);
			layer.onclick = null;
		}
	}

	/**
	* Windows Phone 8.1 fakes user agent string to look like Android and iPhone.
	*
	* @type boolean
	*/
	var deviceIsWindowsPhone = navigator.userAgent.indexOf("Windows Phone") >= 0;

	/**
	 * Android requires exceptions.
	 *
	 * @type boolean
	 */
	var deviceIsAndroid = navigator.userAgent.indexOf('Android') > 0 && !deviceIsWindowsPhone;


	/**
	 * iOS requires exceptions.
	 *
	 * @type boolean
	 */
	var deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent) && !deviceIsWindowsPhone;


	/**
	 * iOS 4 requires an exception for select elements.
	 *
	 * @type boolean
	 */
	var deviceIsIOS4 = deviceIsIOS && (/OS 4_\d(_\d)?/).test(navigator.userAgent);


	/**
	 * iOS 6.0-7.* requires the target element to be manually derived
	 *
	 * @type boolean
	 */
	var deviceIsIOSWithBadTarget = deviceIsIOS && (/OS [6-7]_\d/).test(navigator.userAgent);

	/**
	 * BlackBerry requires exceptions.
	 *
	 * @type boolean
	 */
	var deviceIsBlackBerry10 = navigator.userAgent.indexOf('BB10') > 0;

	/**
	 * Determine whether a given element requires a native click.
	 *
	 * @param {EventTarget|Element} target Target DOM element
	 * @returns {boolean} Returns true if the element needs a native click
	 */
	FastClick.prototype.needsClick = function(target) {
		switch (target.nodeName.toLowerCase()) {

		// Don't send a synthetic click to disabled inputs (issue #62)
		case 'button':
		case 'select':
		case 'textarea':
			if (target.disabled) {
				return true;
			}

			break;
		case 'input':

			// File inputs need real clicks on iOS 6 due to a browser bug (issue #68)
			if ((deviceIsIOS && target.type === 'file') || target.disabled) {
				return true;
			}

			break;
		case 'label':
		case 'iframe': // iOS8 homescreen apps can prevent events bubbling into frames
		case 'video':
			return true;
		}

		return (/\bneedsclick\b/).test(target.className);
	};


	/**
	 * Determine whether a given element requires a call to focus to simulate click into element.
	 *
	 * @param {EventTarget|Element} target Target DOM element
	 * @returns {boolean} Returns true if the element requires a call to focus to simulate native click.
	 */
	FastClick.prototype.needsFocus = function(target) {
		switch (target.nodeName.toLowerCase()) {
		case 'textarea':
			return true;
		case 'select':
			return !deviceIsAndroid;
		case 'input':
			switch (target.type) {
			case 'button':
			case 'checkbox':
			case 'file':
			case 'image':
			case 'radio':
			case 'submit':
				return false;
			}

			// No point in attempting to focus disabled inputs
			return !target.disabled && !target.readOnly;
		default:
			return (/\bneedsfocus\b/).test(target.className);
		}
	};


	/**
	 * Send a click event to the specified element.
	 *
	 * @param {EventTarget|Element} targetElement
	 * @param {Event} event
	 */
	FastClick.prototype.sendClick = function(targetElement, event) {
		var clickEvent, touch;

		// On some Android devices activeElement needs to be blurred otherwise the synthetic click will have no effect (#24)
		if (document.activeElement && document.activeElement !== targetElement) {
			document.activeElement.blur();
		}

		touch = event.changedTouches[0];

		// Synthesise a click event, with an extra attribute so it can be tracked
		clickEvent = document.createEvent('MouseEvents');
		clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null);
		clickEvent.forwardedTouchEvent = true;
		targetElement.dispatchEvent(clickEvent);
	};

	FastClick.prototype.determineEventType = function(targetElement) {

		//Issue #159: Android Chrome Select Box does not open with a synthetic click event
		if (deviceIsAndroid && targetElement.tagName.toLowerCase() === 'select') {
			return 'mousedown';
		}

		return 'click';
	};


	/**
	 * @param {EventTarget|Element} targetElement
	 */
	FastClick.prototype.focus = function(targetElement) {
		var length;

		// Issue #160: on iOS 7, some input elements (e.g. date datetime month) throw a vague TypeError on setSelectionRange. These elements don't have an integer value for the selectionStart and selectionEnd properties, but unfortunately that can't be used for detection because accessing the properties also throws a TypeError. Just check the type instead. Filed as Apple bug #15122724.
		if (deviceIsIOS && targetElement.setSelectionRange && targetElement.type.indexOf('date') !== 0 && targetElement.type !== 'time' && targetElement.type !== 'month') {
			length = targetElement.value.length;
			targetElement.setSelectionRange(length, length);
		} else {
			targetElement.focus();
		}
	};


	/**
	 * Check whether the given target element is a child of a scrollable layer and if so, set a flag on it.
	 *
	 * @param {EventTarget|Element} targetElement
	 */
	FastClick.prototype.updateScrollParent = function(targetElement) {
		var scrollParent, parentElement;

		scrollParent = targetElement.fastClickScrollParent;

		// Attempt to discover whether the target element is contained within a scrollable layer. Re-check if the
		// target element was moved to another parent.
		if (!scrollParent || !scrollParent.contains(targetElement)) {
			parentElement = targetElement;
			do {
				if (parentElement.scrollHeight > parentElement.offsetHeight) {
					scrollParent = parentElement;
					targetElement.fastClickScrollParent = parentElement;
					break;
				}

				parentElement = parentElement.parentElement;
			} while (parentElement);
		}

		// Always update the scroll top tracker if possible.
		if (scrollParent) {
			scrollParent.fastClickLastScrollTop = scrollParent.scrollTop;
		}
	};


	/**
	 * @param {EventTarget} targetElement
	 * @returns {Element|EventTarget}
	 */
	FastClick.prototype.getTargetElementFromEventTarget = function(eventTarget) {

		// On some older browsers (notably Safari on iOS 4.1 - see issue #56) the event target may be a text node.
		if (eventTarget.nodeType === Node.TEXT_NODE) {
			return eventTarget.parentNode;
		}

		return eventTarget;
	};


	/**
	 * On touch start, record the position and scroll offset.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.onTouchStart = function(event) {
		var targetElement, touch, selection;

		// Ignore multiple touches, otherwise pinch-to-zoom is prevented if both fingers are on the FastClick element (issue #111).
		if (event.targetTouches.length > 1) {
			return true;
		}

		targetElement = this.getTargetElementFromEventTarget(event.target);
		touch = event.targetTouches[0];

		if (deviceIsIOS) {

			// Only trusted events will deselect text on iOS (issue #49)
			selection = window.getSelection();
			if (selection.rangeCount && !selection.isCollapsed) {
				return true;
			}

			if (!deviceIsIOS4) {

				// Weird things happen on iOS when an alert or confirm dialog is opened from a click event callback (issue #23):
				// when the user next taps anywhere else on the page, new touchstart and touchend events are dispatched
				// with the same identifier as the touch event that previously triggered the click that triggered the alert.
				// Sadly, there is an issue on iOS 4 that causes some normal touch events to have the same identifier as an
				// immediately preceeding touch event (issue #52), so this fix is unavailable on that platform.
				// Issue 120: touch.identifier is 0 when Chrome dev tools 'Emulate touch events' is set with an iOS device UA string,
				// which causes all touch events to be ignored. As this block only applies to iOS, and iOS identifiers are always long,
				// random integers, it's safe to to continue if the identifier is 0 here.
				if (touch.identifier && touch.identifier === this.lastTouchIdentifier) {
					event.preventDefault();
					return false;
				}

				this.lastTouchIdentifier = touch.identifier;

				// If the target element is a child of a scrollable layer (using -webkit-overflow-scrolling: touch) and:
				// 1) the user does a fling scroll on the scrollable layer
				// 2) the user stops the fling scroll with another tap
				// then the event.target of the last 'touchend' event will be the element that was under the user's finger
				// when the fling scroll was started, causing FastClick to send a click event to that layer - unless a check
				// is made to ensure that a parent layer was not scrolled before sending a synthetic click (issue #42).
				this.updateScrollParent(targetElement);
			}
		}

		this.trackingClick = true;
		this.trackingClickStart = event.timeStamp;
		this.targetElement = targetElement;

		this.touchStartX = touch.pageX;
		this.touchStartY = touch.pageY;

		// Prevent phantom clicks on fast double-tap (issue #36)
		if ((event.timeStamp - this.lastClickTime) < this.tapDelay) {
			event.preventDefault();
		}

		return true;
	};


	/**
	 * Based on a touchmove event object, check whether the touch has moved past a boundary since it started.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.touchHasMoved = function(event) {
		var touch = event.changedTouches[0], boundary = this.touchBoundary;

		if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) {
			return true;
		}

		return false;
	};


	/**
	 * Update the last position.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.onTouchMove = function(event) {
		if (!this.trackingClick) {
			return true;
		}

		// If the touch has moved, cancel the click tracking
		if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) {
			this.trackingClick = false;
			this.targetElement = null;
		}

		return true;
	};


	/**
	 * Attempt to find the labelled control for the given label element.
	 *
	 * @param {EventTarget|HTMLLabelElement} labelElement
	 * @returns {Element|null}
	 */
	FastClick.prototype.findControl = function(labelElement) {

		// Fast path for newer browsers supporting the HTML5 control attribute
		if (labelElement.control !== undefined) {
			return labelElement.control;
		}

		// All browsers under test that support touch events also support the HTML5 htmlFor attribute
		if (labelElement.htmlFor) {
			return document.getElementById(labelElement.htmlFor);
		}

		// If no for attribute exists, attempt to retrieve the first labellable descendant element
		// the list of which is defined here: http://www.w3.org/TR/html5/forms.html#category-label
		return labelElement.querySelector('button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea');
	};


	/**
	 * On touch end, determine whether to send a click event at once.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.onTouchEnd = function(event) {
		var forElement, trackingClickStart, targetTagName, scrollParent, touch, targetElement = this.targetElement;

		if (!this.trackingClick) {
			return true;
		}

		// Prevent phantom clicks on fast double-tap (issue #36)
		if ((event.timeStamp - this.lastClickTime) < this.tapDelay) {
			this.cancelNextClick = true;
			return true;
		}

		if ((event.timeStamp - this.trackingClickStart) > this.tapTimeout) {
			return true;
		}

		// Reset to prevent wrong click cancel on input (issue #156).
		this.cancelNextClick = false;

		this.lastClickTime = event.timeStamp;

		trackingClickStart = this.trackingClickStart;
		this.trackingClick = false;
		this.trackingClickStart = 0;

		// On some iOS devices, the targetElement supplied with the event is invalid if the layer
		// is performing a transition or scroll, and has to be re-detected manually. Note that
		// for this to function correctly, it must be called *after* the event target is checked!
		// See issue #57; also filed as rdar://13048589 .
		if (deviceIsIOSWithBadTarget) {
			touch = event.changedTouches[0];

			// In certain cases arguments of elementFromPoint can be negative, so prevent setting targetElement to null
			targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement;
			targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent;
		}

		targetTagName = targetElement.tagName.toLowerCase();
		if (targetTagName === 'label') {
			forElement = this.findControl(targetElement);
			if (forElement) {
				this.focus(targetElement);
				if (deviceIsAndroid) {
					return false;
				}

				targetElement = forElement;
			}
		} else if (this.needsFocus(targetElement)) {

			// Case 1: If the touch started a while ago (best guess is 100ms based on tests for issue #36) then focus will be triggered anyway. Return early and unset the target element reference so that the subsequent click will be allowed through.
			// Case 2: Without this exception for input elements tapped when the document is contained in an iframe, then any inputted text won't be visible even though the value attribute is updated as the user types (issue #37).
			if ((event.timeStamp - trackingClickStart) > 100 || (deviceIsIOS && window.top !== window && targetTagName === 'input')) {
				this.targetElement = null;
				return false;
			}

			this.focus(targetElement);
			this.sendClick(targetElement, event);

			// Select elements need the event to go through on iOS 4, otherwise the selector menu won't open.
			// Also this breaks opening selects when VoiceOver is active on iOS6, iOS7 (and possibly others)
			if (!deviceIsIOS || targetTagName !== 'select') {
				this.targetElement = null;
				event.preventDefault();
			}

			return false;
		}

		if (deviceIsIOS && !deviceIsIOS4) {

			// Don't send a synthetic click event if the target element is contained within a parent layer that was scrolled
			// and this tap is being used to stop the scrolling (usually initiated by a fling - issue #42).
			scrollParent = targetElement.fastClickScrollParent;
			if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) {
				return true;
			}
		}

		// Prevent the actual click from going though - unless the target node is marked as requiring
		// real clicks or if it is in the whitelist in which case only non-programmatic clicks are permitted.
		if (!this.needsClick(targetElement)) {
			event.preventDefault();
			this.sendClick(targetElement, event);
		}

		return false;
	};


	/**
	 * On touch cancel, stop tracking the click.
	 *
	 * @returns {void}
	 */
	FastClick.prototype.onTouchCancel = function() {
		this.trackingClick = false;
		this.targetElement = null;
	};


	/**
	 * Determine mouse events which should be permitted.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.onMouse = function(event) {

		// If a target element was never set (because a touch event was never fired) allow the event
		if (!this.targetElement) {
			return true;
		}

		if (event.forwardedTouchEvent) {
			return true;
		}

		// Programmatically generated events targeting a specific element should be permitted
		if (!event.cancelable) {
			return true;
		}

		// Derive and check the target element to see whether the mouse event needs to be permitted;
		// unless explicitly enabled, prevent non-touch click events from triggering actions,
		// to prevent ghost/doubleclicks.
		if (!this.needsClick(this.targetElement) || this.cancelNextClick) {

			// Prevent any user-added listeners declared on FastClick element from being fired.
			if (event.stopImmediatePropagation) {
				event.stopImmediatePropagation();
			} else {

				// Part of the hack for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
				event.propagationStopped = true;
			}

			// Cancel the event
			event.stopPropagation();
			event.preventDefault();

			return false;
		}

		// If the mouse event is permitted, return true for the action to go through.
		return true;
	};


	/**
	 * On actual clicks, determine whether this is a touch-generated click, a click action occurring
	 * naturally after a delay after a touch (which needs to be cancelled to avoid duplication), or
	 * an actual click which should be permitted.
	 *
	 * @param {Event} event
	 * @returns {boolean}
	 */
	FastClick.prototype.onClick = function(event) {
		var permitted;

		// It's possible for another FastClick-like library delivered with third-party code to fire a click event before FastClick does (issue #44). In that case, set the click-tracking flag back to false and return early. This will cause onTouchEnd to return early.
		if (this.trackingClick) {
			this.targetElement = null;
			this.trackingClick = false;
			return true;
		}

		// Very odd behaviour on iOS (issue #18): if a submit element is present inside a form and the user hits enter in the iOS simulator or clicks the Go button on the pop-up OS keyboard the a kind of 'fake' click event will be triggered with the submit-type input element as the target.
		if (event.target.type === 'submit' && event.detail === 0) {
			return true;
		}

		permitted = this.onMouse(event);

		// Only unset targetElement if the click is not permitted. This will ensure that the check for !targetElement in onMouse fails and the browser's click doesn't go through.
		if (!permitted) {
			this.targetElement = null;
		}

		// If clicks are permitted, return true for the action to go through.
		return permitted;
	};


	/**
	 * Remove all FastClick's event listeners.
	 *
	 * @returns {void}
	 */
	FastClick.prototype.destroy = function() {
		var layer = this.layer;

		if (deviceIsAndroid) {
			layer.removeEventListener('mouseover', this.onMouse, true);
			layer.removeEventListener('mousedown', this.onMouse, true);
			layer.removeEventListener('mouseup', this.onMouse, true);
		}

		layer.removeEventListener('click', this.onClick, true);
		layer.removeEventListener('touchstart', this.onTouchStart, false);
		layer.removeEventListener('touchmove', this.onTouchMove, false);
		layer.removeEventListener('touchend', this.onTouchEnd, false);
		layer.removeEventListener('touchcancel', this.onTouchCancel, false);
	};


	/**
	 * Check whether FastClick is needed.
	 *
	 * @param {Element} layer The layer to listen on
	 */
	FastClick.notNeeded = function(layer) {
		var metaViewport;
		var chromeVersion;
		var blackberryVersion;
		var firefoxVersion;

		// Devices that don't support touch don't need FastClick
		if (typeof window.ontouchstart === 'undefined') {
			return true;
		}

		// Chrome version - zero for other browsers
		chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [,0])[1];

		if (chromeVersion) {

			if (deviceIsAndroid) {
				metaViewport = document.querySelector('meta[name=viewport]');

				if (metaViewport) {
					// Chrome on Android with user-scalable="no" doesn't need FastClick (issue #89)
					if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
						return true;
					}
					// Chrome 32 and above with width=device-width or less don't need FastClick
					if (chromeVersion > 31 && document.documentElement.scrollWidth <= window.outerWidth) {
						return true;
					}
				}

			// Chrome desktop doesn't need FastClick (issue #15)
			} else {
				return true;
			}
		}

		if (deviceIsBlackBerry10) {
			blackberryVersion = navigator.userAgent.match(/Version\/([0-9]*)\.([0-9]*)/);

			// BlackBerry 10.3+ does not require Fastclick library.
			// https://github.com/ftlabs/fastclick/issues/251
			if (blackberryVersion[1] >= 10 && blackberryVersion[2] >= 3) {
				metaViewport = document.querySelector('meta[name=viewport]');

				if (metaViewport) {
					// user-scalable=no eliminates click delay.
					if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
						return true;
					}
					// width=device-width (or less than device-width) eliminates click delay.
					if (document.documentElement.scrollWidth <= window.outerWidth) {
						return true;
					}
				}
			}
		}

		// IE10 with -ms-touch-action: none or manipulation, which disables double-tap-to-zoom (issue #97)
		if (layer.style.msTouchAction === 'none' || layer.style.touchAction === 'manipulation') {
			return true;
		}

		// Firefox version - zero for other browsers
		firefoxVersion = +(/Firefox\/([0-9]+)/.exec(navigator.userAgent) || [,0])[1];

		if (firefoxVersion >= 27) {
			// Firefox 27+ does not have tap delay if the content is not zoomable - https://bugzilla.mozilla.org/show_bug.cgi?id=922896

			metaViewport = document.querySelector('meta[name=viewport]');
			if (metaViewport && (metaViewport.content.indexOf('user-scalable=no') !== -1 || document.documentElement.scrollWidth <= window.outerWidth)) {
				return true;
			}
		}

		// IE11: prefixed -ms-touch-action is no longer supported and it's recomended to use non-prefixed version
		// http://msdn.microsoft.com/en-us/library/windows/apps/Hh767313.aspx
		if (layer.style.touchAction === 'none' || layer.style.touchAction === 'manipulation') {
			return true;
		}

		return false;
	};


	/**
	 * Factory method for creating a FastClick object
	 *
	 * @param {Element} layer The layer to listen on
	 * @param {Object} [options={}] The options to override the defaults
	 */
	FastClick.attach = function(layer, options) {
		return new FastClick(layer, options);
	};


	if (typeof define === 'function' && typeof define.amd === 'object' && define.amd) {

		// AMD. Register as an anonymous module.
		define(function() {
			return FastClick;
		});
	} else if (typeof module !== 'undefined' && module.exports) {
		module.exports = FastClick.attach;
		module.exports.FastClick = FastClick;
	} else {
		window.FastClick = FastClick;
	}
}());

},{}],2:[function(require,module,exports){
module.exports = Backbone.Model.extend({
    defaults: {
        recipient: {
            name: "",
            mail: "",
            id: ""
        },
        senderInfo: {},
        postID: 0,
        subject: wbData.contactForm.contact_email_subject,
        message: ""
    },
    setData: function(fields) {
        "use strict";
        var error_occurred = false,
            self = this,
            senderInfo = this.get("senderInfo");

        _.each(fields, function(f, iteratee, context) {
            var val = f.$el.val(),
                name = f.$el.attr('name'),
                validation = f.validation;

            switch (validation) {
                case "!empty":
                    if (_.isEmpty(val)) {
                        self.trigger("error", {
                            $el: f.$el,
                            code: "isEmpty"
                        });
                        error_occurred = true;
                    } else {
                        self.updateData(name, val);
                    }
                    break;
                case "checked":{
                    if(!f.$el.is(":checked")){
                        self.trigger("error", {
                            $el: f.$el,
                            code: "isNotChecked"
                        });
                        error_occurred = true;
                    }else{
                        self.updateData(name, val);
                    }
                }
            }

            if(_.isUndefined(validation)){
                self.updateData(name, val);
            }

        });

        this.set("error", error_occurred);
    },
    escapeHtml: function(string) {
        "use strict";
        var entityMap = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': '&quot;',
            "'": '&#39;',
            "/": '&#x2F;'
        };
        return String(string).replace(/[&<>"'\/]/g, function(s) {
            return entityMap[s];
        });
    },
    updateData: function(name, val) {
        var senderInfo = this.get("senderInfo");

        val = this.escapeHtml(val);

        var matches = name.match(/from\[([a-zA-Z]+)\]/);

        if (matches) {
            senderInfo = this.get("senderInfo");
            senderInfo[matches[1]] = val;
            this.set('senderInfo', senderInfo);
        } else {
            if (name == 'message') {
                this.set("message", val);
            } else {
                senderInfo = this.get("senderInfo");
                senderInfo[name] = val;
                this.set('senderInfo', senderInfo);
            }
        }
    },
    sendmail: function() {
        "use strict";
        var recipient = this.get("recipient"),
            data = {
                action: "wbft_send_contact_email",
                to: recipient.mail,
                to_id: recipient.id,
                subject: this.get("subject"),
                message: this.get("message"),
                from: (function(data) {
                    var return_data = {};
                    _.each(data, function(val, key) {
                        return_data[key] = val;
                    });
                    return return_data;
                })(this.get("senderInfo")),
                post_id: this.get("postID")
            };
        return jQuery.ajax(wbData.ajaxurl, {
            data: data,
            dataType: "json",
            method: "POST"
        });
    }
});

},{}],3:[function(require,module,exports){
module.exports = Backbone.Model.extend({
    initialize: function() {
        "use strict";
        console.log("It'admin time!");
        this.do_stuff();
    },
    do_stuff: function(){
        "use strict";
        var $ = jQuery,
            $mailtable = $("#waboot-received-mails-view"),
            $recent_posts_widget_pt_selector = $("#widgets-right [data-wbrw-post-type-selector]");
        /**
         * Init received mails viewerr
         */
        if($mailtable.length > 0){
            var MailListView = require("../views/mailList.js"),
                MailListModel = require("./mailList.js"),
                MailWindow = new MailListView({
                    model: new MailListModel({
                        emails_data: (function(){
                            if(!_.isUndefined(wbData.contactForm.mails)){
                                return jQuery.parseJSON(wbData.contactForm.mails);
                            }else{
                                return [];
                            }
                        })()
                    }),
                    el: $mailtable
                });
        }
        /**
         * RECENT POST WIDGET
         */
        if($recent_posts_widget_pt_selector.length > 0){
            var get_checkboxes_status = function($container){
                //Get the state of all checkboxes
                var $checkboxes = $container.find("input[type=checkbox]"),
                    states = [];
                $checkboxes.each(function(){
                    states.push({
                        name: $(this).attr("value"),
                        checked: $(this).is(":checked") ? 1 : 0
                    });
                });
                return states;
            };

            var make_term_request = function(data){
                return $.ajax(wbData.ajaxurl,{
                    data: data,
                    dataType: "json",
                    method: "POST"
                });
            };

            $recent_posts_widget_pt_selector.find("input[type=checkbox]").on("change",function(){
                var states = get_checkboxes_status($recent_posts_widget_pt_selector),
                    $categories_container = $("#widgets-right [data-wbrw-term-type='category']"),
                    $tags_container = $("#widgets-right [data-wbrw-term-type='tag']");

                //Adding loading classes:
                $categories_container.addClass("loading");
                $tags_container.addClass("loading");

                //Make reguests for new terms:
                var category_request = make_term_request({
                    action: "wbrw_get_terms",
                    states: states,
                    hierarchical: 1
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $categories_container.removeClass("loading");
                });
                var tags_request = make_term_request({
                    action: "wbrw_get_terms",
                    states: states,
                    hierarchical: 0
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    $tags_container.removeClass("loading");
                });

                //Resolve requests
                $.when(category_request,tags_request).done(function(categories_response,tags_response){
                    //console.log(categories_response);
                    //console.log(tags_response);
                    var assign_terms = function(terms,$container){
                        var tpl = _.template($container.find("[type='text/template']").html()),
                            $ul = $container.find("ul"),
                            field_name = $container.data("field-name"), //the value of <?php echo $this->get_field_name( 'cat' ) ?>
                            field_id = $container.data("field-id"); //the value of <?php echo $this->get_field_id( 'cat' ) ?>
                        $ul.html(tpl({
                            terms: terms,
                            field_name: field_name,
                            field_id: field_id
                        }));
                        $container.removeClass("loading");
                    };
                    assign_terms(categories_response[0],$categories_container);
                    assign_terms(tags_response[0],$tags_container);
                });
            });
        }
    }
});

},{"../views/mailList.js":8,"./mailList.js":5}],4:[function(require,module,exports){
module.exports = Backbone.Model.extend({
    initialize: function() {
        "use strict";
        console.log("It'frontend time!");
        this.do_stuff(jQuery);
    },
    do_stuff: function($){
        "use strict";
        /*
         * Bootstrapping html elements
         */
        /*
        $('input[type=text]').addClass('form-control');
        $('input[type=select]').addClass('form-control');
        $('input[type=email]').addClass('form-control');
        $('input[type=tel]').addClass('form-control');
        $('input[type=password]').addClass('form-control');
        $('textarea').addClass('form-control');
        $('select').addClass('form-control');
        $('input[type=submit]').addClass('btn btn-primary');
        $('button[type=submit]').addClass('btn btn-primary');
        */

        // Tables
        //$('table').addClass('table');

        // Gravity Form
        $('.gform_button').addClass('btn btn-primary btn-lg').removeClass('gform_button button');
        $('.validation_error').addClass('alert alert-danger').removeClass('validation_error');
        $('.gform_confirmation_wrapper').addClass('alert alert-success').removeClass('gform_confirmation_wrapper');



        /*
         * These will make any element that has data-wbShow\wbHide="<selector>" act has visibily toggle for <selector>
         */
        $('[data-wbShow]').on('click', function() {
            var itemToShow = $($(this).attr("data-trgShow"));
            if (itemToShow.hasClass('modal')) {
                $('.modal').each(function(index) {
                    $(this).modal("hide");
                });
                itemToShow.modal("show");
            } else {
                itemToShow.show();
            }
        });
        $('[data-wbHide]').on('click', function() {
            var itemToShow = $($(this).attr("data-trgHide"));
            if (itemToShow.hasClass('modal')) {
                itemToShow.modal("hide");
            } else {
                itemToShow.hide();
            }
        });
        /*
         * INIT CONTACT FORM
         */
        var ContactFormView = require("../views/contactForm.js"),
            ContactFormModel = require("./contactForm.js"),
            $contactForm = $("[data-contactForm]");
        //Init search windows
        if ($contactForm.length > 0) {
            var contactWindow = new ContactFormView({
                model: new ContactFormModel(),
                el: $contactForm
            });
        }
        /*
         * MOBILE ACTIONS
         */
        if (wbData.isMobile) {
            var fs = require("FastClick");
            //swipe = require("TouchSwipe");
            //http://getbootstrap.com/getting-started/#support
            if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
                var msViewportStyle = document.createElement('style')
                msViewportStyle.appendChild(
                    document.createTextNode(
                        '@-ms-viewport{width:auto!important}'
                    )
                );
                document.querySelector('head').appendChild(msViewportStyle);
            }
            fs.FastClick.attach(document.body);
            /*$("body").swipe({
                swipeRight: function(event, direction, distance, duration, fingerCount) {
                    if ($(".navbar-mobile-collapse").css('right') == '0px') {
                        $('button.navbar-toggle').trigger('click');
                    }
                },
                swipeLeft: function(event, direction, distance, duration, fingerCount) {
                    if ($(".navbar-mobile-collapse").css('right') == '0px') {
                        $('button.navbar-toggle').trigger('click');
                    }
                }
            });*/
            //Disable for Metaslider
            $(".metaslider").addClass("noSwipe");
        }
        /*
         * WOOCOMMERCE
         */
        //$('.woocommerce a.button').addClass('btn');
        $('.woocommerce a.add_to_cart_button').removeClass('btn-primary');
        $('.woocommerce .single_add_to_cart_button').removeClass('btn-primary');
        //$('.woocommerce a.add_to_cart_button').addClass('btn-success');
        //$('.woocommerce .single_add_to_cart_button').addClass('btn-success');
        $('.woocommerce a.button').removeClass('button');
        //$('.woocommerce table.cart').addClass('table-striped');
        //$('.woocommerce table.cart td.actions input.button').addClass('btn');
        //$('.woocommerce table.cart td.actions input.button').addClass('btn-default');
        $('.woocommerce table.cart td.actions input.button').removeClass('button');
        //$('.wc-proceed-to-checkout a').addClass('btn btn-lg btn-primary');

        //Enabling tab navigation
        /*$("[role=tablist] li a").each(function() {
         var self = this;
         $(this).on("click", function(e) {
         e.preventDefault();
         self.tab("show");
         });
         });*/
        $(".nav-tabs li:first-child").addClass("active");
        $(".tab-content .tab-pane:first-child").addClass("active");

        /*
        $( document.body ).on( 'updated_checkout', function(){
            $('.woocommerce-checkout .woocommerce-checkout-review-order-table').addClass('table');
            $('.woocommerce-checkout-payment input[type=submit]').addClass('btn btn-lg btn-primary');
        });
        */
    }
});

},{"../views/contactForm.js":7,"./contactForm.js":2,"FastClick":1}],5:[function(require,module,exports){
module.exports = Backbone.Model.extend({
    defaults: {
        emails_data: [],
        page: 1,
        results_per_page: 3,
        pages_count: 1,
        emails_count: 0
    },
    initialize: function(){
        "use strict";
        this.set("emails_count",this.get("emails_data").length);
        this.setStats();
    },
    setStats: function(){
        this.set("emails_count",this.get("emails_data").length);
        var pages_count = Math.round(this.get("emails_count") / this.get("results_per_page"));
        if(pages_count <= 1) pages_count = 1;
        this.set("pages_count",pages_count);
    },
    get_emails: function(){
        "use strict";
        var offset = (function(page,results_per_page){
            if(page === 1){
                return 0;
            }else{
                return results_per_page * (page - 1);
            }
        })(this.get("page"),this.get("results_per_page"));

        var target = this.get("page") !== this.get("pages_count") ? this.get("results_per_page") : 0;

        if(target !== 0){
            return this.get("emails_data").slice(offset,target);
        }else{
            return this.get("emails_data").slice(offset);
        }
    },
    setPage: function(n){
        "use strict";
        var max_pages = this.get("pages_count");
        if(n < 1){
            n = 1;
        }
        if(n > max_pages){
            n = max_pages;
        }
        this.set("page",n);
        this.trigger("pageChanged");
    },
    deleteMail: function(n){
       var self = this,
           data = {
            action: "wbft_delete_contact_email",
            id: n
       };

       var call = jQuery.ajax(wbData.ajaxurl, {
           data: data,
           dataType: "json",
           method: "POST"
       });

       call.done(function(data, textStatus, jqXHR){
            //Delete the mail
            var current_emails_data = self.get("emails_data"),
                mail_to_delete = _.findWhere(current_emails_data,{id:""+n+""}),
                new_emails_data = _.difference(current_emails_data,mail_to_delete);
            self.set("emails_data", new_emails_data);
            self.setStats();
            self.trigger("emailDeleted",n);
       }).fail(function(jqXHR, textStatus, errorThrown){
            console.log("Failed to delete mail "+n);
       });
    }
});
},{}],6:[function(require,module,exports){
jQuery(document).ready(function($) {
    "use strict";
    if (wbData.isAdmin) {
        /*************
         *************
         * ADMIN
         *************
         *************/
        var dashboard = require("./controllers/dashboard.js");
        new dashboard();
    }else{
        /*************
         *************
         * PUBLIC
         *************
         *************/
        var frontend = require("./controllers/frontend.js");
        new frontend();
    }
});

},{"./controllers/dashboard.js":3,"./controllers/frontend.js":4}],7:[function(require,module,exports){
module.exports = Backbone.View.extend({
    events: {
        "submit": "onSubmit"
    },
    fields: [],
    message_tpl: null,
    initialize: function() {
        "use strict";
        //Set the profile of the email receiver on the model
        this.model.set("recipient", {
            id: wbData.contactForm.recipient.id,
            name: wbData.contactForm.recipient.name,
            mail: wbData.contactForm.recipient.email
        });
        this.model.set("postID", this.$el.find("[name=fromID]").val()); //Set the post ID on the model
        //Set the fields on the view
        var self = this;
        this.$el.find("[data-field]").each(function() {
            self.fields.push({
                $el: jQuery(this),
                validation: jQuery(this).attr("data-validation")
            });
        });
        //Prevent form submitting
        this.$el.submit(function(e) {
            e.preventDefault();
        });
        //Get the error message TPL
        this.message_tpl = _.template(this.$el.find("[data-messageTPL]").html());
        //Listen to errors
        this.listenTo(this.model, 'error', this.onError);
    },
    onSubmit: function() {
        "use strict";
        this.$el.removeClass("has-error");
        this.$el.find(".form-group").removeClass("has_error");
        this.$el.find("span.error").remove();

        this.model.setData(this.fields);

        if (!this.model.get("error")) {
            var self = this;
            this.model.sendmail().done(function(data, textStatus, jqXHR) {
                switch (data) {
                    case 0:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-danger',
                            msg: wbData.contactForm.labels.error
                        }));
                        break;
                    case 1:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-warning',
                            msg: wbData.contactForm.labels.warning
                        }));
                        break;
                    case 2:
                        self.$el.html(self.message_tpl({
                            msgclass: 'bg-success',
                            msg: wbData.contactForm.labels.success
                        }));
                        break;
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                self.$el.html(self.message_tpl({
                    msgclass: 'bg-warning',
                    msg: wbData.contactForm.labels.warning
                }));
            });
        }
    },
    onError: function(e) {
        "use strict";
        var $error_el = e.$el,
            error_code = e.code;
        switch (error_code) {
            case "isEmpty":
                $error_el.after("<span class='error'>" + wbData.contactForm.labels.errors[error_code] + "</span>");
                break;
            case "isNotChecked":
                $error_el.parents("div").find("label[for='"+$error_el.attr("name")+"']").append("<span class='error'>" + wbData.contactForm.labels.errors[error_code] + "</span>");
                break;
            default:
                $error_el.after("<span class='error'>" + wbData.contactForm.labels.errors['_default_'] + "</span>");
                break;
        }
        $error_el.closest(".form-group").addClass("has-error");
    }
});

},{}],8:[function(require,module,exports){
module.exports = Backbone.View.extend({
    template: null,
    modals: [],
    events: {
        "click .next-page": "goToNextPage",
        "click .prev-page": "goToPrevPage",
        "click .first-page": "goToFirstPage",
        "click .last-page": "goToLastPage",
        "click #cb": "selectAllCombos",
        "click .view a": "openContentModal",
        "click .delete a": "deleteMail"
    },
    initialize: function(){
        "use strict";
        this.template = _.template(this.$el.find("#waboot-received-mails-tpl").html());
        this.listenTo(this.model,"pageChanged",this.render);
        this.listenTo(this.model,"emailDeleted",this.hideMailRow);
        this.render();
    },
    render: function(){
        "use strict";
        var self = this;
        var html;

        html += this.template({
            mails: this.model.get_emails(),
            mails_count: this.model.get("emails_count"),
            pages_count: this.model.get("pages_count"),
            current_page: this.model.get("page")
        });

        _.each(jQuery(html),function(el){
            if(jQuery(el).attr("data-content-of")){
                var mail_id = jQuery(el).data("content-of");
                if(_.isEmpty(_.findWhere(self.modals,{id:mail_id}))){
                    self.modals.push({
                        id: mail_id,
                        $el: jQuery(el).dialog({autoOpen:false,modal:true,draggable:false,resizable:false})
                    })
                }
            }
        });

        this.$el.html(html);
    },
    selectAllCombos: function(){
        "use strict";
        var checkboxes = this.$el.find(".check-column input[name='mails[]']");
        checkboxes.prop("checked",!checkboxes.prop("checked"));
    },
    openContentModal: function(e){
        "use strict";
        var $mail_el = jQuery(e.target),
            target = _.findWhere(this.modals,{id:$mail_el.data("view-content-of")});

        if(!_.isEmpty(target)){
            target.$el.dialog("open");
        }
    },
    deleteMail: function(e){
        var self = this,
            $mail_el = jQuery(e.target),
            mail_id = $mail_el.data("delete"),
            $mail_row = jQuery("#mail-"+mail_id);

        $mail_row.addClass("loading");
        this.model.deleteMail(mail_id);
    },
    hideMailRow: function(id){
        var self = this,
            $mailrow = jQuery("#mail-"+id);
        $mailrow.hide(1000,function(){self.render()});
    },
    goToPage: function(n){
        "use strict";
        this.model.setPage(n);
        //this.render();
    },
    goToFirstPage: function(){
        "use strict";
        this.goToPage(1);
    },
    goToLastPage: function(){
        "use strict";
        this.goToPage(this.model.get("pages_count"));
    },
    goToNextPage: function(){
        "use strict";
        this.goToPage(this.model.get("page") + 1);
    },
    goToPrevPage: function(){
        "use strict";
        this.goToPage(this.model.get("page") - 1);
    }
});

},{}]},{},[6]);

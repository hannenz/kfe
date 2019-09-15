/**
 * src/js/main.js
 *
 * main javascript file
 */

function APP () {

	var self = this;

	self.debug = false;

	this.init = function() {

		document.addEventListener('DOMContentLoaded', this.setup);
	};

	this.setup = function() {
		
		if (this.debug) {
			console.log('APP::init');
		}
		
		self.pageInit();
	};

	this.pageInit = function() {

		if (this.debug) {
			console.log('APP::pageInit');
		}

		document.body.classList.add('page-has-loaded');

		this.initScrollListener();
		this.main();
	};

	this.main = function() {
	};

	this.initScrollListener = function() {
		var last_known_scroll_position = 0;
		var ticking = false;

		function doSomething(scrollPos) {
			document.body.classList.toggle('page-has-scrolled', (scrollPos > 0));
			document.body.classList.toggle('page-has-scrolled-100px', (scrollPos > 100));
		}

		window.addEventListener('scroll', function(e) {

			last_known_scroll_position = window.scrollY;

			if (!ticking) {

				window.requestAnimationFrame(function() {
					doSomething(last_known_scroll_position);
					ticking = false;
				});
				ticking = true;
			}
		});
	};
};


var app = new APP();
app.init();



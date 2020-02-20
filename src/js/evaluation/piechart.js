/**
 * src/js/evaluation/chart.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version date
 */


/**
 * Generates a pie chart from a div which has a data-values attribute with
 * space-separated list of values
 *
 * The resulting SVG will be placed inside the target element
 *
 * @example
 * <div class="pie" data-values="70 312 99" data-colors="#fab #baf tomato rgb(100, 200, 50)"></div>
 *
 * @param DOMNode 	DOM Node of the element to pie-i-fy
 */
var PieChart = function(el) {
	this.circles = [];

	var values = el.dataset.values.split(/\s/);
	if (values.length < 1) {
		return;
	}

	colors = el.dataset.colors.split(/\s/);
	if (values.length != colors.length) {
		return;
	}

	var total = 0;
	values.forEach(function(v) {
		total += parseFloat(v);
	});


	var NS = 'http://www.w3.org/2000/svg';
	var svg = document.createElementNS(NS, 'svg');

	var offset = 0;
	for (var i = 0; i < values.length; i++) {

		var perc = values[i] / total * 100;

		circle = document.createElementNS(NS, 'circle');
		circle.setAttribute('r', 16);
		circle.setAttribute('cx', 20);
		circle.setAttribute('cy', 20);
		circle.setAttribute('fill', 'transparent');
		circle.setAttribute('stroke-width', 8);
		this.updateCircle(circle, perc, offset, colors[i]);
		this.circles.push(circle);

		// circle.setAttribute('data-value', values[i]);
		// circle.setAttribute('data-color', colors[i]);
		svg.appendChild(circle);

		offset += perc;
	}

	svg.classList.add('pie-chart');
	svg.setAttribute('viewBox', '0 0 40 40');
	svg.setAttribute('width', '40');
	svg.setAttribute('height', '40');
	el.innerHTML = '';
	el.appendChild(svg);

	var observer = new MutationObserver(this.onAttributeChange.bind(this));
	observer.observe(el, { attributes: true });
};


PieChart.prototype.updateCircle = function(circle, perc, offset, color) {
	circle.setAttribute('r', 16);
	circle.setAttribute('cx', 20);
	circle.setAttribute('cy', 20);

	circle.setAttribute('stroke-dasharray', perc + ' 100');
	circle.setAttribute('stroke-dashoffset', -offset);
	circle.setAttribute('fill', 'transparent');
	if (color) {
		circle.setAttribute('stroke', color);
	}
	circle.setAttribute('stroke-width', 8);
}

PieChart.prototype.onAttributeChange = function(mutationsList, observer) {
	for (var mutation of mutationsList) {
		if (mutation.type == 'attributes') {
			var values = mutation.target.dataset.values.split(/\s/);
			if (values.length < 1 || values.length != this.circles.length) {
				return;
			}

			var total = 0;
			values.forEach(function(v) {
				total += parseFloat(v);
			});

			var offset = 0;
			for (var i = 0; i < values.length; i++) {
				var perc = values[i] / total * 100;
				this.updateCircle(this.circles[i], perc, offset);
				offset += perc;
			}
		}
	}
};


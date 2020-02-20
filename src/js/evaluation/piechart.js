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
	var values = el.dataset.values.split(/\s/);
	if (values.length < 1) {
		return;
	}
	var colors = el.dataset.colors.split(/\s/);
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
		circle.setAttribute('r', 10);
		circle.setAttribute('cx', 16);
		circle.setAttribute('cy', 16);
		circle.setAttribute('stroke-dasharray', perc + ' 100');
		circle.setAttribute('stroke-dashoffset', offset);
		circle.setAttribute('fill', 'transparent');
		circle.setAttribute('stroke', colors[i]);
		circle.setAttribute('stroke-width', 6);
		circle.setAttribute('data-value', values[i]);
		circle.setAttribute('data-color', colors[i]);
		svg.appendChild(circle);
		offset += values[i];
	}

	// var perc1 = values[0] / total * 100;
	// var perc2 = values[1] / total * 100;
    //
	// circle = document.createElementNS(NS, 'circle');
	// circle.setAttribute('r', 16);
	// circle.setAttribute('cx', 16);
	// circle.setAttribute('cy', 16);
	// circle.setAttribute('fill', colors[0]);
	// svg.appendChild(circle);
    //
	// var offset = 0;
    //
	// for (var i = 1; i < values.length; i++) {
	// 	perc1 = values[i - 1]  / total * 100;
	// 	perc2 = values[i]  / total * 100;
	// 	offset += perc1;
    //
	// 	circle = document.createElementNS(NS, 'circle');
	// 	circle.setAttribute('r', 16);
	// 	circle.setAttribute('cx', 16);
	// 	circle.setAttribute('cy', 16);
	// 	circle.setAttribute('stroke-dasharray', perc2 + ' 100');
	// 	circle.setAttribute('stroke-dashoffset', -offset);
	// 	circle.setAttribute('fill', 'transparent');
	// 	circle.setAttribute('stroke', colors[i]);
	// 	circle.setAttribute('stroke-width', 32);
	// 	svg.appendChild(circle);
	// }
    //
	svg.classList.add('pie-chart');
	svg.setAttribute('viewBox', '0 0 32 32');
	el.innerHTML = '';
	el.appendChild(svg);
};


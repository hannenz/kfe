/**
 * src/js/evaluation/chart.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2020-02-20
 *
 * Generates a pie chart from a div which has a data-values attribute with
 * space-separated list of values and a (matching) space-separated list of
 * colors (CSS). The resulting SVG will be placed inside the target element The
 * SVG will automatically update whenever the data-attributes change
 * (hot-change)
 *
 * @example
 * <div id="my-pie" class="pie" data-values="70 312 99 156" data-colors="#fab #baf tomato rgb(100,200,50)"></div>
 * <script>var pie = new PieChart(document.getElementById('my-pie'));</script>
 *
 * @param DOMNode 	DOM Node of the element to pie-i-fy
 */
var PieChart = function(el) {
	this.circles = [];
	this.values = [];
	this.colors = [];
	this.total = 0;
	this.el = el;

	if (!this.getValues()) {
		return;
	}
	
	if (!this.getColors()) {
		return;
	}


	var NS = 'http://www.w3.org/2000/svg';
	var svg = document.createElementNS(NS, 'svg');

	var offset = 0;
	for (var i = 0; i < this.values.length; i++) {

		var perc = this.values[i] / this.total * 100;

		circle = document.createElementNS(NS, 'circle');
		circle.setAttribute('r', 16);
		circle.setAttribute('cx', 24);
		circle.setAttribute('cy', 24);
		circle.setAttribute('fill', 'transparent');
		circle.setAttribute('stroke-width', 8);
		circle.setAttribute('stroke-dasharray', perc + ' 100');
		circle.setAttribute('stroke-dashoffset', -offset);
		circle.setAttribute('stroke', this.colors[i]);
		this.circles.push(circle);
		svg.appendChild(circle);
		offset += perc;
	}

	svg.classList.add('pie-chart');
	svg.setAttribute('viewBox', '0 0 48 48');
	svg.setAttribute('width', '48');
	svg.setAttribute('height', '48');
	el.innerHTML = '';
	el.appendChild(svg);

	var observer = new MutationObserver(this.onAttributeChange.bind(this));
	observer.observe(el, { attributes: true });

	var rows = this.el.nextElementSibling.querySelectorAll('tr');
	rows.forEach(row => {
		row.addEventListener('mouseover', function(ev) {
			var row = ev.target.closest('tr');
			if (!row || !row.dataset.circle) {
				return;
			}
			var i = parseInt(row.dataset.circle);
			this.circles[i].setAttribute('stroke-width', 10);
		}.bind(this));
		row.addEventListener('mouseout', function(ev) {
			var row = ev.target.closest('tr');
			if (!row || !row.dataset.circle) {
				return;
			}
			var i = parseInt(row.dataset.circle);
			this.circles[i].setAttribute('stroke-width', 8);
		}.bind(this));

		if (row.dataset.circle) {
			var i = parseInt(row.dataset.circle);
			row.style.color = this.colors[i];
		}
	});

	for (var i = 0; i < this.circles.length; i++) {
		this.circles[i].addEventListener('mouseover', function(ev) {
			var circle = ev.target;
			circle.setAttribute('stroke-width', 10);
			var index = Array.prototype.indexOf.call(circle.parentNode.children, circle);
			var tr = this.el.nextElementSibling.querySelector('[data-circle="' + index + '"]');
			tr.style.backgroundColor = this.colors[index];
			tr.style.color = '#fff';
		}.bind(this));
		this.circles[i].addEventListener('mouseout', function(ev) {
			var circle = ev.target;
			circle.setAttribute('stroke-width', 8);
			var index = Array.prototype.indexOf.call(circle.parentNode.children, circle);
			var tr = this.el.nextElementSibling.querySelector('[data-circle="' + index + '"]');
			tr.style.backgroundColor = 'initial';
			tr.style.color = this.colors[index];
		}.bind(this));
	}
};



PieChart.prototype.updateCircle = function(circle, perc, offset) {
	circle.setAttribute('stroke-dasharray', perc + ' 100');
	circle.setAttribute('stroke-dashoffset', -offset);
}



PieChart.prototype.onAttributeChange = function(mutationsList, observer) {
	for (var mutation of mutationsList) {
		if (mutation.type == 'attributes') {

			if (!this.getValues()) {
				return;
			}

			var offset = 0;
			for (var i = 0; i < this.values.length; i++) {
				var perc = this.values[i] / this.total * 100;
				this.updateCircle(this.circles[i], perc, offset);
				offset += perc;
			}
		}
	}
};


PieChart.prototype.getValues = function() {
	if (!this.el.dataset.values) {
		return;
	}
	this.values = this.el.dataset.values.split(/\s/);
	if (this.values.length > 0) {
		this.total = 0;
		this.values.forEach(value => {
			this.total += parseFloat(value);
		});
		return true;
	}
	return false;
}


PieChart.prototype.getColors = function() {
	if (!this.el.dataset.colors) {
		return false;
	}
	this.colors = this.el.dataset.colors.split(/\s/);
	return (this.colors.length == this.values.length);
}



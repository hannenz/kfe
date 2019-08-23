/**
 * src/js/barcode_scanner.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */

function CameraBarcodeScanner() {

	var self = this;

	self.setup = function(element, callback) {

		Quagga.init({
			inputStream: {
				name: "Live",
				type: "LiveStream",
				target: element,
				constraints: {
					width: 640,
					height: 480,
					facingMode: "environment"
				},
				singleChannel: false
			},
			decoder: {
				readers: ["code_128_reader"]
			},
			numOfWorkers: navigator.hardwareConcurrency,
			locate : false
		}, function(err) {
			if (err) {
				console.log(err);
				return;
			}
			// console.log("Quagga successfully initialised, now starting up");
			// Quagga.start();
		});

		Quagga.onDetected(callback);
	};

	self.turnOn = function() {
		Quagga.start();
	};

	self.turnOff = function() {
		Quagga.stop();
	};
};

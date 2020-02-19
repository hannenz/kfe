/**
 * src/js/checkout/logger.js
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2020-02-19
 *
 * Safety log
 * Every submission will be logged in local storage
 * just in case anything goes wrong we have some kind of backup here
 */

/**
 * @class Logger
 */
var Logger = function() {
};


/**
 * @param string
 */
Logger.prototype.log = function(message) {
	this.log = JSON.parse(window.localStorage.getItem('log'));
	if (!this.log) {
		this.log = [];
	}
	this.log.push(message);
	window.localStorage.setItem('log', JSON.stringify(this.log));
};

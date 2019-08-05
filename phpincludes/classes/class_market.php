<?php
namespace KFE;

use KFE\Model;

class Market extends Model {

	public function init() {
		$this->tableName = 'kfe_markets';
	}

	public function getMarketsWithOpenNumberAssignment() {
		$markets = $this->filter([
		])->findAll();

		return $markets;
	}


	/**
	 * Get upcoming markets
	 *
	 * @param int 		$limit 	optional
	 * @return Array
	 */
	public function getUpcoming($limit = null) {

		$this->filter([
			'market_datetime > ' => 'NOW()'
		]);
		if ($limit != null) {
			$this->limit($limit);
		}
		$markets = $this->findAll();

		return $markets;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function afterRead($result) {
		$result['marketNumberAssignmentIsRunning'] = $this->numberAssignmentIsRunning($result);
		return $result;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function numberAssignmentIsRunning($market) {

		if ($market['market_number_assignment_is_closed']) {
			return false;
		}

		$now = mktime();
		$begin = strtotime($market['market_number_assignment_begin']);
		$end = $market['market_number_assignment_end'] == '0000-00-00 00:00:00' ? 0 : strtotime($market['market_number_assignment_end']);

		return (
			($begin < $now) &&
			(($end == 0) || ($end > $now))
		);
	}
}
?>

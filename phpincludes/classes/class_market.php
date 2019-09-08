<?php
namespace KFE;

use Contentomat\Model;
use Contentomat\CmtPage;

class Market extends Model {

	/**
	 * @var int
	 */
	protected $detailPageId;

	/**
	 * @var int
	 */
	protected $registrationPageId;

	/**
	 * @var Contentomat\CmtPage
	 */
	protected $CmtPage;


	public function init() {
		$this->tableName = 'kfe_markets';
		$this->CmtPage = new CmtPage();
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
		$result['registrationUrl'] = sprintf('%s%s?market_id=%u',
			$this->CmtPage->makePageFilePath($this->registrationPageId),
			$this->CmtPage->makePageFileName($this->registrationPageId),
			$result['id']
		);
		$result['detailUrl'] = sprintf('%s%s:%u.html',
			$this->CmtPage->makePageFilePath($this->detailPageId),
			strftime('%Y-%m-%s', strtotime($result['market_datetime'])),
			$result['id']
		);

		return $result;
	}

	/**
	 * Check if a market's number assignment is running at the moment
	 *
	 * @return boolean
	 */
	public function numberAssignmentIsRunning($market) {

		if ($market['market_number_assignment_is_closed']) {
			return false;
		}

		$now = time();
		$begin = $market['market_number_assignment_begin'] == '0000-00-00 00:00:00' ? 0 : strtotime($market['market_number_assignment_begin']);
		$end = $market['market_number_assignment_end'] == '0000-00-00 00:00:00' ? 0 : strtotime($market['market_number_assignment_end']);

		return (
			($begin != 0) &&
			($begin < $now) &&
			(($end == 0) || ($end > $now))
		);
	}

	/**
	 * Getter for registrationPageId
	 */
	public function getRegistrationPageId() {
	    return $this->registrationPageId;
	}
	
	/**
	 * Setter for registrationPageId
	 *
	 * @param  $registrationPageId
	 */
	public function setRegistrationPageId($registrationPageId) {
	    $this->registrationPageId = $registrationPageId;
	}

	/**
	 * Getter for detailPageId
	 *
	 * @return string
	 */
	public function getDetailPageId() {
	    return $this->detailPageId;
	}
	
	/**
	 * Setter for detailPageId
	 *
	 * @param string $detailPageId
	 * @return class_market
	 */
	public function setDetailPageId($detailPageId) {
	    $this->detailPageId = $detailPageId;
	    return $this;
	}
}
?>

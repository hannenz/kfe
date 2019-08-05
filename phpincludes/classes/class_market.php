<?php
namespace KFE;

use KFE\Model;
use Contentomat\CmtPage;

class Market extends Model {

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
		$result['registrationUrl'] = sprintf('%s%s?marketId=%u',
			$this->CmtPage->makePageFilePath($this->registrationPageId),
			$this->CmtPage->makePageFileName($this->registrationPageId),
			$result['id']
		);
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
}
?>

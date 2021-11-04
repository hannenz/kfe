<?php
namespace KFE;

use KFE\Seller;
use KFE\Item;
use Contentomat\Model;
use Contentomat\CmtPage;
use Contentomat\Contentomat;
use Contentomat\Session;

abstract class MarketState {
	const INACTIVE = 0;
	const UPCOMING = 1;
	const NEXT_UPCOMING = 2;
	const RUNNING = 3;
	const CANCELLED = 4;
	const PAST = 5;
}


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

	/**
	 * @var KFE\Seller
	 */
	protected $Seller;

	/**
	 * @var KFE\Item
	 */
	protected $Item;

	protected $Cmt;
	protected $Session;

	/**
	 * Abzug für Spende in Prozent
	 *
	 * @var float;
	 */
	protected $discount = 20.0;


	public function init() {
		$this->tableName = 'kfe_markets';
		$this->CmtPage = new CmtPage();
		$this->Seller = new Seller();
		$this->Item = new Item();
		$this->Cmt = Contentomat::getContentomat();
		$this->Session = $this->Cmt->getSession();
	}

	public function getMarketsWithOpenNumberAssignment() {
		$query = sprintf("SELECT * FROM %s WHERE market_number_assignment_is_closed = 0 AND market_number_assignment_begin < NOW() AND market_number_assignment_end > NOW()", $this->tableName);
		$markets = $this->query($query);
		return $markets;
	}


	/**
	 * Get upcoming markets
	 * A market is "upcoming" as long as it is not over yet
	 * (market_end is the relevant datetime)
	 *
	 * @param int 		$limit 	optional
	 * @return Array
	 */
	public function getUpcoming($limit = null) {

		// A market is of interest as long as it is not over yet (running),
		// so we check for market_end
		$this->filter([
			'market_end > ' => strftime('%Y-%m-%d %H:%M')
		])->order([
			'market_begin' => 'ASC'
		]);
		if ($limit != null) {
			$this->limit($limit);
		}
		
		$markets = $this->findAll(['fetchMedia' => [
			'image' => 'marketImages',
			'document' => 'marketDocuments',
			'map' => 'marketMaps'
		]]);

		return $markets;
	}


	/**
	 * Get the next upcoming market
	 *
	 * @return Array
	 */
	public function getNextUpcoming() {
		$markets = $this->getUpcoming(1);
		return array_shift($markets);
	}
	

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function findArchived() {
		$query = sprintf("SELECT * FROM %s WHERE market_end < NOW() AND market_is_public=1 AND market_charity != ''", $this->tableName);
		return $this->query($query);
	}
	


	/**
	 * Get avaliable seller numbers for a given market
	 *
	 * @access public
	 * @param Integer  		The market's id to check
	 * @return Array 		An array of available numbers
	 */
	public function getAvailableNumbers($marketId) {
		$availableNumbers = [];
		for ($i = 1; $i <= 110; $i++) {
			$availableNumbers[] = $i;
		}

		$allocatedNumbers = [];
		$results = $this->Seller
		->fields([
			'seller_nr'
		])
		->filter([
			'seller_market_id' => $marketId
		])
		->findAll();
		foreach ($results as $result) {
			$allocatedNumbers[] = (int)$result['seller_nr'];
		}
		return (array_diff($availableNumbers, $allocatedNumbers));
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function afterRead($result) {
		$now = time();
		$result['marketNumberAssignmentIsRunning'] = $this->numberAssignmentIsRunning($result);
		$result['marketNumberAssignmentIsUpcoming'] = false;

		$result['marketBeginISO8601'] = date('c', strtotime($result['market_begin']));
		$result['marketEndISO8601'] = date('c', strtotime($result['market_end']));

		if (strtotime($result['market_number_assignment_begin']) > $now && strtotime($result['market_number_assignment_end']) > $now && !(bool)$result['market_number_assignment_is_closed']) {
			$result['marketNumberAssignmentIsUpcoming'] = true;
		}
		$result['numbersLeft'] = count($this->getAvailableNumbers($result['id']));
		$result['registrationUrl'] = sprintf('%s%s?market_id=%u',
			$this->CmtPage->makePageFilePath($this->registrationPageId),
			$this->CmtPage->makePageFileName($this->registrationPageId),
			$result['id']
		);
		$result['detailUrl'] = sprintf('%s%s:%u.html',
			$this->CmtPage->makePageFilePath($this->detailPageId),
			strftime('%Y-%m-%s', strtotime($result['market_begin'])),
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
		if (!empty($this->Session->getSessionVar('bypass'))) {
			return true;
		}

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


	/**
	 * Gather evaluation data
	 * Returns an array of data that can be passed to parser
	 * or via JSON to client (server-sent-event)
	 *
	 * @param int 		$marketId
	 * @return Array
	 */
	public function evaluate($marketId) {
		setlocale(LC_NUMERIC, 'C');

		$items = $this->Item->filter(['item_market_id' => $marketId])->findAll();
		$turnoverTotal = 0;
		$turnoverSellers = 0;
		$turnoverEmployees = 0;
		$bountyTotal = 0;
		$bountySellers = 0;
		$bountyEmployees = 0;
		$turnoverCheckout = [];
		$itemsTotal = 0;
		$itemsSellers = 0;
		$itemsEmployees = 0;

		foreach ($items as $item) {

			$turnoverTotal += $item['item_value'];
			$turnoverCheckout[$item['item_checkout_id']] += $item['item_value'];
			$itemsTotal++;

			if ($this->Seller->isEmployee($item['item_seller_nr'])) {
				$turnoverEmployees += $item['item_value'];

				$itemsEmployees++;
			}
			else {
				$turnoverSellers += $item['item_value'];

				$discountValue = ($item['item_value'] * $this->discount / 100); 
				$bountyTotal += $discountValue;
				$bountySellers += $discountValue;

				$itemsSellers++;
			}
		}

		$data = [
			'turnoverTotal' => $turnoverTotal / 100,
			'turnoverSellers' => $turnoverSellers / 100,
			'turnoverEmployees' => $turnoverEmployees / 100,
			'bountyTotal' => $bountyTotal / 100,
			'bountySellers' => $bountySellers / 100,
			'bountyEmployees' => $bountyEmployees / 100,
			'turnoverCheckout1' => $turnoverCheckout[1] / 100,
			'turnoverCheckout2' => $turnoverCheckout[2] / 100,
			'turnoverCheckout3' => $turnoverCheckout[3] / 100,
			'itemsTotal' => $itemsTotal,
			'itemsSellers' => $itemsSellers,
			'itemsEmployees' => $itemsEmployees
		];

		return $data;
	}
}
?>

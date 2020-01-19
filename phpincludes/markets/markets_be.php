<?php
namespace KFE;

use \Contentomat\Parser;
use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \Contentomat\Contentomat;
use \KFE\Market;
use \KFE\Seller;
use \KFE\Cart;
use \KFE\Item;

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');

class MarketBackendController extends ApplicationController {


	/**
	 * @var Contentomat\Contentomat
	 */
	protected $Cmt;

	/**
	/* @var Contentomat\Parser;
	 */
	protected $Parser;

	/**
	 * @var KFE\Market
	 */
	protected $Market;

	/**
	 * @var KFE\Seller
	 */
	protected $Seller;

	/**
	 * @var KFE\Cart
	 */
	protected $Cart;

	/**
	 * @var KFE\Item
	 */
	protected $Item;

	/**
	 * Abzug für Spende in Prozent
	 *
	 * @var float;
	 */
	protected $discount = 20.0;



	public function init() {
		// setlocale(LC_ALL, 'de_DE.UTF-8');
		$this->Cmt = Contentomat::getContentomat();
		$this->Cmt->setErrorReporting('error');
		$this->Market = new Market();
		$this->Seller = new Seller();
		$this->Cart = new Cart();
		$this->Item = new Item();
		$this->Parser = new \Contentomat\Parser;

		$this->Cmt->setErrorReporting('error');

		if (isset($this->getvars['id'])) {
			$this->marketId = $this->getvars['id'];
			if (is_array($this->marketId)) {
				$this->marketId = array_shift($this->marketId);
			}
		}
		if (isset($_REQUEST['market_id'])) {
			$this->marketId = $_REQUEST['market_id'];
		}
	}

	public function initActions($action = '') {
		parent::initActions();
		if (!empty($_REQUEST['action'])) {
			$this->action = $_REQUEST['action'];
			return;
		}
	}

	protected function actionDefault() {
		$markets = $this->Market->findAll();
		$this->Parser->setParserVar('markets', $markets);

		// $this->Parser->setParserVar('marketId', $this->marketId);
		$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/default.tpl');
	}

	protected function actionEvaluate() {

		if (empty($this->marketId)) {
			return;
		}

		$market = $this->Market->findById($this->marketId);
		$sellerItems = $this->Item->filter([
			'item_market_id' => $this->marketId,
			'item_seller_nr <' => 300
		])->findAll();

		$employeeItems = $this->Item->filter([
			'item_market_id' => $this->marketId,
			'item_seller_nr >=' => 300
		])->findAll();

		$turnover = [ 
			'sellers' => 0,
			'employees' => 0,
			'total' => 0,
			'checkout' => []
		];
		$bounty = [
			'sellers' => 0,
			'employees' => 0,
			'total' => 0
		];
		$items = [
			'sellers' => count($sellerItems),
			'employees' => count($employeeItems),
			'total' => count($sellerItems) + count($employeeItems)
		];

		foreach ($sellerItems as $item) {
			$turnover['sellers'] += $item['item_value'];
			$turnover['total'] += $item['item_value'];
			$turnover['checkout'][$item['item_checkout_id']] += $item['item_value'];

			$bounty['sellers'] += $item['item_value'] * ($this->discount / 100);
			$bounty['total'] += $item['item_value'] * ($this->discount / 100);
		}

		foreach ($employeeItems as $item) {
			$turnover['employees'] += $item['item_value'];
			$turnover['total'] += $item['item_value'];
			$turnover['checkout'][$item['item_checkout_id']] += $item['item_value'];
		}

		$this->Parser->setMultipleParserVars(array_merge(
			$market, [
			'market_id' => $market['id'],
			'turnoverSellers' => $turnover['sellers'] / 100,
			'turnoverEmployees' => $turnover['employees'] / 100,
			'turnoverTotal' => $turnover['total'] / 100,
			'turnoverCheckout1' => $turnover['checkout'][1] / 100,
			'turnoverCheckout2' => $turnover['checkout'][2] / 100,
			'turnoverCheckout3' => $turnover['checkout'][3] / 100,
			'bountySellers' => $bounty['sellers'] / 100,
			'bountyEmployees' => $bounty['employees'] / 100,
			'bountyTotal' => $bounty['total'] / 100,
			'itemsSellers' => $items['sellers'],
			'itemsEmployees' => $items['employees'],
			'itemsTotal' => $items['total']
		]));

		$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/evaluation.tpl');

		// echo '<pre>'; var_dump($total / 100); echo '</pre>';
		// echo '<pre>'; var_dump($bounty / 100); echo '</pre>'; die();
	}


	/**
	 * Generate Sumsheets
	 *
	 * @return void
	 */
	public function actionSumsheets() {
		$this->Seller->generateSumsheets([
			'seller_market_id' => $this->marketId
		], $this->marketId, []);
		return null;
	}
	

	protected function __actionEvaluate() {

		if (empty($this->marketId)) {
			return;
		}

		$market = $this->Market->findById($this->marketId);
		$sellers = $this->Seller->filter(['seller_market_id', $this->marketId])->findAll();

		foreach ($sellers as $n => $seller) {

			$sellers[$n]['items'] = [];
			$sellers[$n]['total'] = 0;
			$sellers[$n]['itemsCount'] = 0;
			$discount = (!$this->Seller->isEmployee($seller['seller_nr'])) ? $this->discount : 0;

			// Get all carts from the seller's market 
			$carts = $this->Cart->filter(['cart_market_id' => $this->marketId])->findAll();
			foreach ($carts as $cart) {
				foreach ($cart['items'] as &$item) {
					if ($item['sellerNr'] == $seller['seller_nr']) {

						$vf = (float)$item['value'] / 100;

						$item['valueEuro'] = $vf;
						$item['valueFmt'] = sprintf('%.2f', $vf);
						$item['datetime'] = strftime('%d.%m.%Y %H:%M:%S', (int)$item['ts']);

						array_push($sellers[$n]['items'], $item);
						$sellers[$n]['total'] += $vf;
						$sellers[$n]['itemsCount']++;
					}
				}

				$sellers[$n]['discount'] = $discount;
				$sellers[$n]['discountValue'] = $sellers[$n]['total'] * ($discount / 100);
				$sellers[$n]['totalNet'] = $sellers[$n]['total'] * ((100 - $discount) / 100);
			}
		}

		$this->Parser->setParserVar('sellers', $sellers);
		$this->Parser->setParserVar('marketId', $market['id']);
		$this->Parser->setParserVar('marketDateFmt', strftime('%d.%m.%Y', strtotime($market['market_begin'])));
		$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/evaluation.tpl');
	}

	/**
	 * Aux method, should not be needed anymore
	 */
	public function actionItems2db() {
		$carts = $this->Cart->findAll();
		foreach ($carts as $cart) {
			foreach ($cart['items'] as $item) {
				echo '<pre>'; var_dump($item); echo '</pre>';
				$query = sprintf("INSERT INTO kfe_items SET item_datetime='%s',item_seller_id=%u,item_market_id=%u,item_checkout_id=%u,item_value=%u,item_code='%s',item_cart_id=%u,item_seller_nr=%u",
					strftime('%Y-%m-%d %H:%M:%S', $item['ts'] / 1000),
					$this->db->dbQuote($item['sellerId']),
					$this->db->dbQuote($item['marketId']),
					$this->db->dbQuote($item['checkoutId']),
					$this->db->dbQuote($item['value']),
					$this->db->dbQuote($item['code']),
					$this->db->dbQuote($cart['id']),
					$this->db->dbQuote($item['sellerNr'])
				);
				$this->db->query($query);
			}
		}
	}
	
}

$ctl = new MarketBackendController();
$marketEvaluation = $ctl->work();
$content = $marketEvaluation;
?>


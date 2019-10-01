<?php
/**
 * Backend controller to be included in the seller's table (`kfe_sellers`)
 * to create a sumsheet for each seller in the detail view
 * Also handles filtering for market in overview table and export to CSV
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2019-10-01
 */
namespace KFE;

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if (!defined("CMT_APPLAUNCHER")) {
	exit();
}

use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \Contentomat\Parser;
use \Contentomat\Contentomat;
use \Contentomat\Session;
use \KFE\Seller;
use \KFE\Market;
use \KFE\Cart;



if (!class_exists('\KFE\SellerBackendController')) {

	class SellerBackendController extends ApplicationController {

		/**
		 * @var \KFE'Seller
		 */
		protected $Seller;

		/**
		 * @var \KFE\Market
		 */
		protected $Market;

		/**
		 * @var \KFE\Cart
		 */
		protected $Cart;

		/**
		 * @var \Contentomat\Parser
		 */
		protected $Parser;

		/**
		 * @var Array
		 */
		private $seller = null;


		public function init() {
			$this->Cmt = Contentomat::getContentomat();
			$this->Session = $this->Cmt->getSession();
			$this->Seller = new Seller();
			$this->Market = new Market();
			$this->Cart = new Cart();
			$this->Parser = new \Contentomat\Parser();

			$this->templatesPath = PATHTOWEBROOT . 'templates/sellers/';

			if (!empty($this->getvars['id'])) {
				$sellerId = array_shift($this->getvars['id']);
				$this->seller = $this->Seller->findById($sellerId);
			}

			// $this->handleFilter();
		}

		public function initActions($action = '') {
			parent::initActions($action);

		}


		/**
		 * Display and handle a filter for markets
		 *
		 * @return void
		 */
		protected function actionDefault() {
			if (isset($_POST['sellerMarketId'])) {
				$sellerMarketId = $_POST['sellerMarketId'];
				$this->Session->setSessionVar('sellerMarketId', $sellerMarketId);
			}
			else {
				$sellerMarketId = $this->Session->getSessionVar('sellerMarketId');
			}

			if (empty($sellerMarketId)) {
				$this->Session->deleteSessionVar('sellerMarketId');
				$sellerMarketId = 0;
				$this->Session->setSessionVar('sellerMarketId', $sellerMarketId);
			}

			$markets = $this->Market->findAll();
			$this->Parser->setParserVar('markets', $markets);
			$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT.'templates/sellers/be_market_filter.tpl');

			// 9999 == all markets, no filter
			if ($sellerMarketId != 9999) {
				$query = sprintf('WHERE seller_market_id=%u', (int)$sellerMarketId);
			}
			$this->Cmt->setVar('cmtAddQuery', $query);
		}

		protected function actionExport() {
			if (isset($_POST['sellerMarketId'])) {
				$sellerMarketId = $_POST['sellerMarketId'];
				$this->Session->setSessionVar('sellerMarketId', $sellerMarketId);
			}
			else {
				$sellerMarketId = $this->Session->getSessionVar('sellerMarketId');
			}

			$this->Seller->export(
				['seller_market_id' => $sellerMarketId],
				[
					'seller_nr',
					'seller_lastname',
					'seller_firstname',
					'seller_email',
					'seller_phone',
					'seller_registration_date',
					'seller_is_activated'
				],
				'csv');
		}

		public function actionSumsheets() {
			if (isset($_POST['sellerMarketId'])) {
				$sellerMarketId = $_POST['sellerMarketId'];
				$this->Session->setSessionVar('sellerMarketId', $sellerMarketId);
			}
			else {
				$sellerMarketId = $this->Session->getSessionVar('sellerMarketId');
			}

			if (empty($sellerMarketId)) {
				die("No market id");
			}

			$this->Seller->generateSumsheets([
				'seller_market_id' => $sellerMarketId
			]);
		}


		/**
		 * Creates a sumsheet for a seller, e.g. sum up all sold items by this
		 * seller
		 *
		 * @access public
		 * @return void
		 */
		public function actionEdit() {

			if (empty($this->seller)) {
				return;
			}

			$sellerItems = [];

			// Get all carts from the seller's market 
			if ($this->Seller->isEmployee($this->seller['seller_nr'])) {
				$market = $this->Market->getNextUpcoming();
				$marketId = $market['id'];
			}
			else {
				$marketId = $this->seller['seller_market_id'];
			}

			$carts = $this->Cart->filter([
				'cart_market_id' => $marketId
			])->findAll();

			$total = 0;
			$discount = 20;
			foreach ($carts as $cart) {
				$items = (array)json_decode($cart['cart_items'], true);
				foreach ($items as $item) {
					if ($item['sellerNr'] == $this->seller['seller_nr']) {
						$item['valueEuro'] = $item['value'] / 100;
						$item['dateTimeFmt'] = strftime('%d.%m.%Y %T', $item['ts'] / 1000);
						$sellerItems[] = $item;
						$total += $item['value'];
					}
				}
			}
			$totalEuro = $total / 100;
			$discountValue = $total * ($discount / 100);
			$discountValueEuro = $discountValue / 100;
			$totalNet = $total - $discountValue;
			$totalNetEuro = $totalNet / 100;

			$this->Parser->setMultipleParserVars([
				'sellerItems' => $sellerItems,
				'total' => $total,
				'totalEuro' => $totalEuro,
				'discount' => $discount,
				'discountValueEuro' => $discountValueEuro,
				'totalNetEuro' => $totalNetEuro,
				'sellerId' => $this->seller['id']
			]);
			$this->content = $this->Parser->parseTemplate($this->templatesPath . 'sumsheet.tpl');

			if (!empty($this->requestvars['print'])) {
				die ($this->content);
			}
		}
	}
}


$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');

$ctl = new SellerBackendController();
$sellerSumSheet = $ctl->work();
$content = $sellerSumSheet;
?>

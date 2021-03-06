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
			$this->Cmt->setErrorReporting('error');
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

			switch ($this->action) {

				case 'duplicate':
				case 'new':
					$this->action = 'void';
					break;

				case 'view':
					$this->action = 'edit';
					break;

				case 'abortNew':
				case 'abortEdit':
				case 'abortDuplicate':
					$this->action = 'overview';
					break;

			}
		}


		protected function actionVoid() {
			// deliberatley left blank!
		}


		protected function actionDefault() {
			$this->changeAction('overview');
		}


		/**
		 * Display and handle a filter for markets
		 *
		 * @return void
		 */
		protected function actionOverview() {
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


			$conditions = ['seller_market_id' => $sellerMarketId];
			$columns = [
				'seller_nr',
				'seller_lastname',
				'seller_firstname',
				'seller_email',
				'seller_phone',
				'seller_registration_date',
				'seller_is_activated'
			];

			$this->Seller->export($conditions, $columns, $this->postvars['exportType']);
		}


		/**
		 * Action to generate all sumsheets for sellers matching a certain
		 * condition
		 *
		 * Parameters are passed via POST
		 */
		public function actionSumsheets() {

			if (isset($_POST['sellerMarketId'])) {
				$sellerMarketId = $_POST['sellerMarketId'];
				$this->Session->setSessionVar('sellerMarketId', $sellerMarketId);
			}
			else {
				$sellerMarketId = $this->Session->getSessionVar('sellerMarketId');
			}

			$this->Seller->generateSumsheets([
				'seller_market_id' => $sellerMarketId
			], $sellerMarketId, []);
		}


		/**
		 * Action to generate a single sumsheet PDF
		 * Seller ID and market ID are passed via GET
		 */
		public function actionSumsheet() {
			$conditions = [ 'id' => $_REQUEST['seller_id'] ];
			$marketId = $_REQUEST['market_id'];
			$options = [
				'skipEmpty' => !empty($_REQUEST['skipEmpty'])
			];
			$this->Seller->generateSumsheets($conditions, $marketId, $options);
		}


		/**
		 * Creates a sumsheet for a seller, e.g. sum up all sold items by this
		 * seller
		 *
		 * @access public
		 * @return void
		 */
		public function actionEdit() {

			$this->Cmt->setErrorReporting('error');

			$content = sprintf('<a class="cmtButton" href="?sid=%s&cmtApplicationID=%u&action=sumsheet&seller_id=%u&market_id=%u">Summenblatt erzeugen</a>',
				SID,
				$this->getvars['cmtApplicationID'],
				$this->seller['id'],
				$this->seller['seller_market_id'] != 0 ? $this->seller['seller_market_id'] : 1
			);
			$this->Cmt->setVar('sellerSumSheet', $content);
		}
	}
}


$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');

$ctl = new SellerBackendController();
// $sellerSumSheet = $ctl->work();
$content = $ctl->work();
?>

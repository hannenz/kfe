<?php
namespace KFE;

use KFE\Market;
use KFE\Seller;
use Contentomat\Contentomat;
use Contentomat\ApplicationController;
use Contentomat\Logger;
use KFE\Cart;

use \Exception;



/**
 * Class checkouts_controller
 *
 * Checkout for markets
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class CheckoutsController extends ApplicationController {


	/**
	 * @var Integer
	 */
	protected $marketId;

	/**
	 * @var Integer
	 */
	protected $checkoutId;

	/**
	 * @var \KFE\Market
	 */
	protected $Market;

	/**
	 * @var \KFE\Seller
	 */
	protected $Seller;

	/**
	 * @var \KFE\Cart
	 */
	protected $Cart;



	/**
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->Market = new Market();
		$this->Seller = new Seller();
		$this->Cart = new Cart();

		$this->parser->setDefaultTemplateBasePath(PATHTOWEBROOT . "templates/checkouts/");

		if (!empty($_REQUEST['marketId'])) {
			$this->marketId = (int)$_REQUEST['marketId'];
		}
		if (!empty($_REQUEST['checkoutId'])) {
			$this->checkoutId = (int)$_REQUEST['checkoutId'];
		}

		if (empty($this->marketId)) {
			$this->marketId = $this->session->getSessionVar('marketId');
		}
		if (empty($this->checkoutId)) {
			$this->checkoutId = $this->session->getSessionVar('checkoutId');
		}

		$this->session->setSessionVar('marketId', $this->marketId);
		$this->session->setSessionVar('checkoutId', $this->checkoutId);
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {

		if (!empty($this->marketId) && !empty($this->checkoutId)) {

			try {
				if ($this->checkoutId <= 0) {
					throw new Exception("Invalid checkoutd");
				}

				if ($this->marketId <= 0) {
					throw new Exception("No marketId or invalid marketId");
				}

				$market = $this->Market->findById($this->marketId);
				if (empty($market)) {
					throw new Exception("No data found for market #{$this->marketId}");
				}

				$sellers = $this->Seller->findByMarket($market['id']);
				// $sellers = $this->Seller->filter([
				// 	'seller_market_id' => $market['id'],
				// 	'seller_is_activated' => true
				// ])
				// ->order(['seller_nr' => 'ASC'])
				// ->findAll();
				$this->parser->setParserVar('sellers', $sellers);

				$this->parser->setMultipleParserVars($market);
				$this->parser->setMultipleParserVars([
					'marketId' => $this->marketId,
					'checkoutId' => $this->checkoutId,
					'applicationId' => $this->applicationID,
					'user_id' => $this->user->getUserID(),
					'user_name' => $this->user->getUserName(),
					'user_alias' => $this->user->getUserAlias(),
					'marketDate' => strftime('%d.%m.%Y', strtotime($market['market_begin']))
				]);

				$this->content = $this->parser->parseTemplate($this->templatesPath . "checkout_standalone.tpl");
				return;
			}
			catch(Exception $e) {
				die ($e->getMessage());
				$this->parser->setParserVar('error', $e->getMessage());
			}
		}

		$markets = $this->Market->findAll();
		$upcomingMarket = $this->Market->getNextUpcoming();
		$this->parser->setParserVar('upcomingMarketId', $upcomingMarket['id']);
		$this->parser->setParserVar('markets', $markets);
		$this->content = $this->parser->parseTemplate("default.tpl");
	}



	/**
	 * Cancel a Cart
	 */
	public function actionCancel() {
		// $this->isAjax = true;
		// $this->isJson = true;
		$cartId = $_REQUEST['id'];
		$success = $this->Cart->delete($cartId);

		if ($success) {
			$this->changeAction('default');
		}

		// $this->content = [
		// 	'success' => $success,
		// 	'cartId' => $cartId
		// ];
		// die (json_encode($this->content));
	}


	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function actionValidateManualEntry() {
		$this->isJson = true;
		$this->isAjax = true;

		$seller = $this->Seller->findBySellerNr((int)$this->getvars['sellerNr'], (int)$this->getvars['marketId']);
		$this->content = [
			'success' => !empty($seller)
		];

	}
}

$ctl = new CheckoutsController();
$content = $ctl->work();
?>

<?php
namespace KFE;

use KFE\Market;
use Contentomat\Contentomat;
use Contentomat\ApplicationController;
use Contentomat\PsrAutoloader;
use KFE\Cart;

use \Exception;

error_reporting(E_ALL & ~E_NOTICE);


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
		$this->Cart = new Cart();

		$this->marketId = (int)$_REQUEST['marketId'];
		$this->checkoutId = (int)$_REQUEST['checkoutId'];

		$this->parser->setDefaultTemplateBasePath(PATHTOWEBROOT . "templates/checkouts/");
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

}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new CheckoutsController();
$content = $ctl->work();
?>

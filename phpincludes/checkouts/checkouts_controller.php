<?php
namespace KFE;

use KFE\Market;
use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use KFE\Cart;

use \Exception;

error_reporting(E_ALL & ~E_NOTICE);


/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class CheckoutsController extends Controller {

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
		$this->templatesPath = PATHTOWEBROOT . "templates/checkouts/";
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		try {
			$marketId = $_REQUEST['marketId'];
			if (empty($marketId) || (int)$marketId <= 0) {
				throw new Exception("No marketId or invalid marketId");
			}

			$market = $this->Market->findById($marketId);
			if (empty($market)) {
				throw new Exception("No data found for market #{$marketId}");
			}

			$checkoutId = $_REQUEST['checkoutId'];
			if (empty($checkoutId) || (int)$checkoutId <=0) {
				throw new Exception("Invalid checkout id");
			}

			$this->parser->setParserVar('marketId', $marketId);
			$this->parser->setParserVar('checkoutId', $checkoutId);
			$this->parser->setMultipleParserVars($market);
			$this->content = $this->parser->parseTemplate($this->templatesPath . "checkout.tpl");
		}
		catch(Exception $e) {
			die ($e->getMessage());
		}
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

<?php
namespace KFE;

use KFE\Cart;
use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;

use \Exception;

error_reporting(E_ALL & ~E_NOTICE);


/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class CartsController extends Controller {

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
		$this->Cart = new Cart;
		$this->templatesPath = PATHTOWEBROOT . "templates/carts/";
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		try {
		}
		catch(Exception $e) {
			die ($e->getMessage());
		}
	}

	public function actionAdd() {
		$this->isAjax = true;
		$this->isJson = true;

		$success = true;

		try {
			$data = [
				'cart_timestamp' => (int)$_REQUEST['timestamp'],
				'cart_market_id' => (int)$_REQUEST['marketId'],
				'cart_checkout_id' => (int)$_REQUEST['checkoutId'],
				'cart_total' => (float)$_REQUEST['total'],
				'cart_items' => $_REQUEST['items']
			];
			$cartId = $this->Cart->add($data);
		}
		catch (Exception $e) {
			$success = false;
			$cartId = 0;
		}

		$this->content = [
			'success' => $success,
			'cartId' => $cartId
		];
		die (json_encode($this->content));
	}
}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new CartsController();
$content = $ctl->work();
?>

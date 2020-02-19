<?php
namespace KFE;

use KFE\Cart;
use KFE\Item;
use KFE\Seller;
use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;

use \Exception;

// error_reporting(E_ALL & ~E_NOTICE);


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
	 * @var \KFE\Item
	 */
	protected $Item;

	/**
	 * @var \KFE\Seller
	 */
	protected $Seller;


	/**
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->Cart = new Cart();
		$this->Item = new Item();
		$this->Seller = new Seller();
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
		$message = '';

		try {

			$items = (array)json_decode($_REQUEST['items'], true);

			$data = [
				'cart_datetime' => strftime('%F %T', (int)$_REQUEST['timestamp'] / 1000),
				'cart_submitted_datetime' => strftime('%F %T', time()),
				'cart_market_id' => (int)$_REQUEST['marketId'],
				'cart_checkout_id' => (int)$_REQUEST['checkoutId'],
				'cart_total' => (float)$_REQUEST['total'],
				'cart_items_count' => count($items),
				'cart_cashier_id' => (int)$_REQUEST['cashierId']
			];

			$cartId = $this->Cart->add($data);
			if (!empty($cartId)) {

				foreach ($items as $item) {
					$itemData = [
						'item_datetime' => strftime('%Y-%m-%d %H:%M:%S', ((int)$item['ts'] / 1000)),
						'item_market_id' => $item['marketId'],
						'item_seller_id' => $this->Seller->getSellerId($item['sellerNr'], $item['marketId']),
						'item_seller_nr' => $item['sellerNr'],
						'item_checkout_id' => $item['checkoutId'],
						'item_code' => $item['code'],
						'item_value' => $item['value'],
						'item_cart_id' => $cartId
					];
					$this->Item->add($itemData);
				}
			}
		}
		catch (Exception $e) {
			$success = false;
			$message = $e->getMessage();
			$cartId = 0;
		}

		$this->content = [
			'success' => $success,
			'message' => $message,
			'cartId' => $cartId,
			// We return the original cart's timestamp and checkout id, so that
			// we can identify this cart client side (remove it from carts cue)
			'cartTimestamp' => $_REQUEST['timestamp'], //$data['cart_timestamp'],
			'cartCheckoutId' => $data['cart_checkout_id']
		];
		// die (json_encode($this->content));
	}

}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new CartsController();
$content = $ctl->work();
?>

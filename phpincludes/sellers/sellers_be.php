<?php
namespace KFE;

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \KFE\Seller;
use \KFE\Cart;

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');

class SellerBackendController extends ApplicationController {

	public function init() {
		$this->Seller = new Seller();
		$this->Cart = new Cart();
		$this->sellerId = array_shift($this->getvars['id']);
	}

	public function actionDefault() {
		$seller = $this->Seller->findById($this->sellerId);

		echo '<pre>'; var_dump($seller); echo '</pre>'; 

		$sellerItems = [];

		// Get all carts from the seller's market 
		$carts = $this->Cart->findAll(); //By('cart_market_id', $seller['seller_market_id']);
		foreach ($carts as $cart) {
			$items = (array)json_decode($cart['cart_items'], true);
			foreach ($items as $item) {
				if ($item['sellerId'] == $this->sellerId) {
					$sellerItems[] = $item;
				}
			}
		}

		echo '<pre>'; var_dump($sellerItems); echo '</pre>'; die();
	}
}

$ctl = new SellerBackendController();
$sellerSumSheet = $ctl->work();
?>

<?php
/**
 * Backend controller to be included in the seller's table (`kfe_sellers`)
 * to create a sumsheet for each seller in the detail view
 */
namespace KFE;

use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \KFE\Seller;
use \KFE\Cart;

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');



class SellerBackendController extends ApplicationController {


	/**
	 * @var Array
	 */
	private $seller = null;


	public function init() {
		$this->Seller = new Seller();
		$this->Cart = new Cart();

		if (!empty($this->getvars['id'])) {
			$sellerId = array_shift($this->getvars['id']);
			$this->seller = $this->Seller->findById($sellerId);
		}
	}


	/**
	 * Creates a sumsheet for a seller, e.g. sum up all sold items by this
	 * seller
	 *
	 * @access public
	 * @return void
	 */
	public function actionDefault() {

		if (empty($this->seller)) {
			return;
		}

		$sellerItems = [];

		// Get all carts from the seller's market 
		$carts = $this->Cart->findAll(); //By('cart_market_id', $seller['seller_market_id']);
		foreach ($carts as $cart) {
			$items = (array)json_decode($cart['cart_items'], true);
			foreach ($items as $item) {
				if ($item['sellerNr'] == $this->seller['seller_nr']) {
					$sellerItems[] = $item;
				}
			}
		}
	}
}

$ctl = new SellerBackendController();
$sellerSumSheet = $ctl->work();
?>

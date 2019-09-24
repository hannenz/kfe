<?php
/**
 * Backend controller to be included in the seller's table (`kfe_sellers`)
 * to create a sumsheet for each seller in the detail view
 */
namespace KFE;

use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \Contentomat\Parser;
use \KFE\Seller;
use \KFE\Market;
use \KFE\Cart;

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');



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
		$this->Seller = new Seller();
		$this->Market = new Market();
		$this->Cart = new Cart();
		$this->Parser = new \Contentomat\Parser();

		$this->templatesPath = PATHTOWEBROOT . 'templates/sellers/';

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

$ctl = new SellerBackendController();
$sellerSumSheet = $ctl->work();
?>

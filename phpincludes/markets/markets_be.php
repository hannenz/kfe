<?php
namespace KFE;

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

use \Contentomat\Parser;
use \Contentomat\PsrAutoloader;
use \Contentomat\ApplicationController;
use \KFE\Market;
use \KFE\Seller;
use \KFE\Cart;

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . 'classes');
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');

class MarketBackendController extends ApplicationController {


	/**
	/* @var Contentomat\Parser;
	 */
	protected $Parser;

	/**
	 * @var KFE\Market
	 */
	protected $Market;

	/**
	 * @var KFE\Seller
	 */
	protected $Seller;

	/**
	 * @var KFE\Cart
	 */
	protected $Cart;

	/**
	 * Abzug für Spende in Prozent
	 *
	 * @var float;
	 */
	protected $discount = 20.0;



	public function init() {
		// setlocale(LC_ALL, 'de_DE.UTF-8');
		$this->Market = new Market();
		$this->Seller = new Seller();
		$this->Cart = new Cart();
		$this->Parser = new \Contentomat\Parser;
		if (isset($this->getvars['id'])) {
			$this->marketId = $this->getvars['id'];
			if (is_array($this->marketId)) {
				$this->marketId = array_shift($this->marketId);
			}
		}
	}

	public function initActions($action = '') {
		parent::initActions();
		if (!empty($_REQUEST['action'])) {
			$this->action = $_REQUEST['action'];
			return;
		}
	}

	protected function actionDefault() {
		$this->Parser->setParserVar('marketId', $this->marketId);
		$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/default.tpl');
	}

	protected function actionEvaluate() {

		if (empty($this->marketId)) {
			return;
		}

		$market = $this->Market->findById($this->marketId);
		$sellers = $this->Seller->filter(['seller_market_id', $this->marketId])->findAll();

		foreach ($sellers as $n => $seller) {

			$sellers[$n]['items'] = [];
			$sellers[$n]['total'] = 0;
			$sellers[$n]['itemsCount'] = 0;
			$discount = (!$this->Seller->isEmployee($seller['seller_nr'])) ? $this->discount : 0;

			// Get all carts from the seller's market 
			$carts = $this->Cart->filter(['cart_market_id' => $this->marketId])->findAll();
			foreach ($carts as $cart) {
				foreach ($cart['items'] as &$item) {
					if ($item['sellerNr'] == $seller['seller_nr']) {

						$vf = (float)$item['value'] / 100;

						$item['valueEuro'] = $vf;
						$item['valueFmt'] = sprintf('%.2f', $vf);
						$item['datetime'] = strftime('%d.%m.%Y %H:%M:%S', (int)$item['ts']);

						array_push($sellers[$n]['items'], $item);
						$sellers[$n]['total'] += $vf;
						$sellers[$n]['itemsCount']++;
					}
				}

				$sellers[$n]['discount'] = $discount;
				$sellers[$n]['discountValue'] = $sellers[$n]['total'] * ($discount / 100);
				$sellers[$n]['totalNet'] = $sellers[$n]['total'] * ((100 - $discount) / 100);
			}
		}

		$this->Parser->setParserVar('sellers', $sellers);
		$this->Parser->setParserVar('marketId', $market['id']);
		$this->Parser->setParserVar('marketDateFmt', strftime('%d.%m.%Y', strtotime($market['market_begin'])));
		$this->content = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/evaluation.tpl');
	}
}

$ctl = new MarketBackendController();
$marketEvaluation = $ctl->work();
$content = $marketEvaluation;
?>


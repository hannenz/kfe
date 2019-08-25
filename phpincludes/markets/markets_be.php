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
		if (isset($this->getvars['id'])) {
			$this->marketId = array_shift($this->getvars['id']);
		}
	}

	public function actionDefault() {

		if (empty($this->marketId)) {
			return;
		}


		$market = $this->Market->findById($this->marketId);

		$sellers = $this->Seller->filter(['seller_market_id', $this->marketId])->findAll();

		foreach ($sellers as $n => $seller) {

			$sellers[$n]['items'] = [];
			$sellers[$n]['total'] = 0;
			$sellers[$n]['itemsCount'] = 0;

			// Get all carts from the seller's market 
			$carts = $this->Cart->filter(['cart_market_id' => $this->marketId])->findAll();
			foreach ($carts as $cart) {
				foreach ($cart['items'] as &$item) {
					if ($item['sellerId'] == $seller['id']) {

						$vf = (float)$item['value'] / 100;

						$item['valueEuro'] = $vf;
						$item['valueFmt'] = sprintf('%.2f', $vf);
						$item['datetime'] = strftime('%d.%m.%Y %H:%M:%S', (int)$item['ts']);

						array_push($sellers[$n]['items'], $item);
						$sellers[$n]['total'] += $vf;
						$sellers[$n]['itemsCount']++;
					}
				}

				$sellers[$n]['discountValue'] = $sellers[$n]['total'] * ($this->discount / 100);
				$sellers[$n]['totalNet'] = $sellers[$n]['total'] * ((100 - $this->discount) / 100);

			}
		}

		$this->myParser = new \Contentomat\Parser();
		$this->myParser->setParserVar('discount', $this->discount);
		$this->myParser->setParserVar('sellers', $sellers);
		$this->content = $this->myParser->parseTemplate(PATHTOWEBROOT . 'templates/markets/evaluation.tpl');
	}
}

$ctl = new MarketBackendController();
$marketEvaluation = $ctl->work();
?>


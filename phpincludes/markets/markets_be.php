<?php
namespace KFE;


use \Contentomat\Logger;
use \Contentomat\Parser;
use \Contentomat\ApplicationController;
use \KFE\Market;
use \KFE\Seller;
use \KFE\Cart;
use \KFE\Item;


class MarketBackendController extends ApplicationController {

	/**
	 * @var int	
	 * Interval in secods for server-side-events / loop
	 */
	protected $delay = 5;

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
	 * @var KFE\Item
	 */
	protected $Item;



	public function init() {

		setlocale(LC_ALL, 'de_DE.UTF-8');

		$this->Market = new Market();
		$this->Seller = new Seller();
		$this->Cart = new Cart();
		$this->Item = new Item();

		if (isset($_REQUEST['marketId'])) {
			if (is_array($_REQUEST['marketId'])) {
				$this->marketId = (int)array_shift($_REQUEST['marketId']);
			}
			else {
				$this->marketId = (int)$_REQUEST['marketId'];
			}
		}

		$this->handleContentomatVars();
	}


	protected function actionDefault() {
		$MyParser = new \Contentomat\Parser();
		$MyParser->setParserVar('marketId', $this->marketId);
		$markets = $this->Market->order(['market_begin' => 'desc'])->findAll();
		$MyParser->setParserVar('markets', $markets);
		$this->content = $MyParser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/default.tpl');
	}



	protected function actionEditSave() {
		die ("Saving");
	}



	protected function actionEvaluate() {

		if (empty($this->marketId)) {
			return;
		}

		$market = $this->Market->findById($this->marketId);
		$data = $this->Market->evaluate($this->marketId);

		$MyParser = new \Contentomat\Parser();

		$MyParser->setMultipleParserVars($data);
		$MyParser->setMultipleParserVars($market);
		$MyParser->setParserVar('sellers', $sellers);
		$MyParser->setParserVar('marketId', $this->marketId);
		// $MyParser->setParserVar('marketDateFmt', strftime('%d.%m.%Y', strtotime($market['market_begin'])));

		$applicationId = 151;

		$loopUrl = sprintf('https://%s/%sApplicationLauncher.php?sid=%s&cmtApplicationID=%u&action=evaluateLoop&marketId=%u',
			$_SERVER['SERVER_NAME'],
			ADMINPATH,
			SID,
			$applicationId,
			$this->marketId
		);
		$MyParser->setParserVar('loopUrl', $loopUrl);

		$this->content = $MyParser->parseTemplate(PATHTOWEBROOT . 'templates/markets/be/evaluation.tpl');
	}
	

	/**
	 * Loop evaluation and send event (Server-sent event for browser)
	 */
	public function actionEvaluateLoop() {

		if (empty($this->marketId)) {
			return;
		}

		if (!empty($this->getvars['delay'])) {
			$this->delay = (int)$this->getvars['delay'];
		}
		// Logger::log(sprintf('Entering actionEvaluateLoop, marketId: %u, delay: %u s', $this->marketId, $delay));

		header('Cache-Control: no-cache');
		header("Content-Type: text/event-stream\n\n");

		while (true) {
			// Logger::log('Sending an event');
			$data = $this->Market->evaluate($this->marketId);
			echo "event: ping\n";
			echo 'data: ' . json_encode($data);
			echo "\n\n";
			ob_end_flush();
			flush();
			sleep ($this->delay);
		}
	}
}

$ctl = new MarketBackendController();
$content = $ctl->work();
?>

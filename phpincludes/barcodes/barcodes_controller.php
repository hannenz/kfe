<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use KFE\BarcodeSheet;
use KFE\Market;
use KFE\Seller;
use \Exception;

/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class BarcodesController extends Controller {

	/**
	 * @var \KFE\BarcodeSheet
	 */
	protected $BarcodeSheet;

	/**
	 * @var \KFE\Market
	 */
	protected $Market;

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
		$this->templatesPath = PATHTOWEBROOT . "templates/barcodes/";
		$this->Market = new Market();
		$this->Seller = new Seller();
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		$this->changeAction('composeSheet');
	}



	public function actionComposeSheet() {

		if (!empty($this->postvars)) {
			$sellerId = (int)$this->postvars['sellerId'];
			$marketId = (int)$this->postvars['marketId'];

			$errors = [];

			try {
				$seller = $this->Seller->findById($sellerId);
				if (empty($seller)) {
					$errors[] = 'errorIllegalSellerId';
					throw new Exception();
				}
				if ($seller['seller_email'] != $this->postvars['sellerEmail']) {
					$errors[] = 'errorEmailMismatch';
					throw new Exception();
				}

				$data = [];
				for ($i = 50; $i <= 1000; $i += 50) {
					if (!isset($this->postvars['amount_' . $i])) {
						continue;
					}
					$amount = (int)$this->postvars['amount_' . $i];
					if ($amount > 0) {
						$data[$i] = $amount;
					}
				};

				for ($i = 1; $i <= 3; $i++) {
					$amount = (int)$this->postvars['amount_custom_' . $i];
					$value = (int)$this->postvars['value_custom_' . $i] * 100;
					if ($amount > 0 && $value > 0) {
						if (isset($data[$value])) {
							$data[$value] += $amount;
						}
						else {
							$data[$value] = $amount;
						}
					}
				}

				$market = $this->Market->findById($marketId);
				if (empty($market)) {
					$errors[] = 'errorIllegalMarketId';
					throw new Exception();
				}
				$this->BarcodeSheet = new BarcodeSheet($market, $seller);
				$this->BarcodeSheet->create($data);
				return;
			}
			catch (Exception $e) {
				foreach ($errors as $error) {
					$this->parser->setParserVar($error, true);
				}
			}
		}

		$markets = $this->Market->getMarketsWithOpenNumberAssignment();
		$this->parser->setParserVar('markets', $markets);
		$this->parser->setMultipleParserVars($this->postvars);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'compose_sheet.tpl');
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function actionValidateSeller() {

		$this->isAjax = true;
		$this->isJson = true;

		$sellerId = $_REQUEST['sellerId'];
		$email = $_REQUEST['email'];

		$seller = $this->Seller->filter([
			'id' => $sellerId,
			'seller_email' => $email
		])->findOne();

		$this->content = [
			'success' => !empty($seller)
		];

		die (json_encode($this->content));
	}
	
}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new BarcodesController();
$content = $ctl->work();
?>

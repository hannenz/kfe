<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use KFE\BarcodeSheet;
use KFE\Market;
use KFE\Seller;

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
		$code = '2019091201700500';
		$type = $this->BarcodeGenerator::TYPE_CODE_128;
		
		$barcode = $this->BarcodeGenerator->getBarcode($code, $type, 5, 80);
		$this->parser->setMultipleParserVars([
			'barcode' => $barcode,
			'code' => $code,
			'type' => 'CODE_128' 
		]);
		$this->content = $this->parser->parseTemplate($this->templatesPath . "test.tpl");
	}



	public function actionComposeSheet() {

		if (!empty($this->postvars)) {

			$data = [];
			for ($i = 50; $i <= 250; $i += 50) {
				$amount = (int)$this->postvars['amount-' . $i];
				if ($amount > 0) {
					$data[$i] = $amount;
				}
			};

			for ($i = 1; $i <= 3; $i++) {
				$amount = (int)$this->postvars['amount-custom-' . $i];
				$value = (int)$this->postvars['value-custom-' . $i] * 100;
				if ($amount > 0 && $value > 0) {
					if (isset($data[$value])) {
						$data[$value] += $amount;
					}
					else {
						$data[$value] = $amount;
					}
				}
			}

			$market = $this->Market->findById($this->postvars['marketId']);
			$seller = $this->Seller->findById(1);
			$this->BarcodeSheet = new BarcodeSheet($market, $seller);
			$this->BarcodeSheet->create($data);
			return;
		}

		$markets = $this->Market->getMarketsWithOpenNumberAssignment();
		$this->parser->setParserVar('markets', $markets);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'compose_sheet.tpl');
	}
}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new BarcodesController();
$content = $ctl->work();
?>

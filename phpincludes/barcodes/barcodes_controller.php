<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use \Picqer\Barcode\BarcodeGeneratorSVG;
use \TCPDF;
use KFE\Market;

error_reporting(E_ERROR);

require_once(PATHTOWEBROOT . "phpincludes/vendor/autoload.php");
require_once(PATHTOWEBROOT . "phpincludes/vendor/laurentbrieu/tcpdf/src/TCPDF/TCPDF.php");

/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class BarcodesController extends Controller {

	var $BarcodeGenerator;

	var $Market;

	/**
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->BarcodeGenerator = new BarcodeGeneratorSVG();
		$this->templatesPath = PATHTOWEBROOT . "templates/barcodes/";
		$this->Market = new Market();
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

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('Johannes Braun');
			$pdf->SetTitle('Kinderflohmarkt Erbach - Barcodes Sheet');

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			$pdf->AddPage();

			$style = array(
				'position' => '',
				'align' => 'C',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => true,
				'hpadding' => 'auto',
				'vpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255),
				'text' => true,
				'font' => 'helvetica',
				'fontsize' => 8,
				'stretchtext' => 4
			);

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
			$this->content = '';
			$y = 0;
			foreach ($data as $value => $amount) {
				for ($i = 0; $i < $amount; $i++) {

					// $code = '2019091201700500';
					$code = sprintf('%s%03u%05u',
						strftime('%Y%m%d', strtotime($market['market_datetime'])),
						$this->postvars['userId'],
						$value
					);
					$type = $this->BarcodeGenerator::TYPE_CODE_128;
					
					$barcode = $this->BarcodeGenerator->getBarcode($code, $type, 3, 100);
					$this->parser->setMultipleParserVars([
						'barcode' => $barcode,
						'code' => $code,
						'type' => 'CODE_128',
						'value' => $value / 100
					]);
					$this->content .= $this->parser->parseTemplate($this->templatesPath . 'test.tpl');

					$pdf->write1DBarcode($code, 'C128', 15 + (($y % 2 == 0) ? 0 : 120), 15 + (($y / 2) * 40), 100, 40, 0.5, $style);
					$y++;
				}
			}
			$pdf->Output(PATHTOTMP . "testsheet.pdf", 'I');
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


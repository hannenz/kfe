<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use \Picqer\Barcode\BarcodeGeneratorSVG;

error_reporting(E_ERROR);

require_once(PATHTOWEBROOT . "phpincludes/vendor/autoload.php");

/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class BarcodesController extends Controller {

	var $BarcodeGenerator;

	/**
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->BarcodeGenerator = new BarcodeGeneratorSVG();
		$this->templatesPath = PATHTOWEBROOT . "templates/barcodes/";
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		$code = '2019091201700500';
		$type = $this->BarcodeGenerator::TYPE_CODE_128;
		
		$this->parser->setMultipleParserVars([
			'barcode' => $this->BarcodeGenerator->getBarcode($code, $type, 5, 80),
			'code' => $code,
			'type' => 'CODE_128' 
		]);
		$this->content = $this->parser->parseTemplate($this->templatesPath . "test.tpl");
	}
	
}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new BarcodesController();
$content = $ctl->work();
?>


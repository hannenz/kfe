<?php
/**
 * phpincludes/classes/class_sellersumsheet.php
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2019-10-02
 */
namespace KFE;

use \TCPDF;
use \Contentomat\Parser;


error_reporting(0);
if (!ISPRODUCTION || true) {
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
	ini_set('display_errors', true);
}

require_once(PATHTOWEBROOT . "phpincludes/vendor/laurentbrieu/tcpdf/src/TCPDF/TCPDF.php");


/**
 * @class LabelSheet
 *
 * Create a sheet of labels
 */
class SellerSumsheet extends TCPDF {


	/**
	 * @var Array
	 */
	protected $sellers;

	protected $currentSeller;

	/**
	 * @var Array
	 */
	protected $market;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var \Contentomat\Parser
	 */
	protected $Parser;
 
	/**
	 * Constructor
	 */
	public function __construct($sellers, $market, $filename) {

		parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false, false);

		$this->sellers = $sellers;
		$this->market = $market;
		$this->filename = $filename;

		$this->Parser = new \Contentomat\Parser();

		// $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false, false);
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Johannes Braun');
		$this->SetTitle('Kinderflohmarkt Erbach - Verkäufer-Auswertung');

		$this->setTopMargin(20);

		// set auto page breaks
		// $this->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
	}


	public function Header() {
		$this->setXY(10, 10);
		$headerHTML = sprintf('<div style="text-align: left; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: #c0c0c0; padding-bottom: 1cm;">Erbacher Kinderflohmarkt am %s | Verkäufer-Nr. <b>%03u:</b> %s %s</div>', strftime('%d.%m.%Y', strtotime($this->market['market_begin'])), $this->currentSeller['seller_nr'], $this->currentSeller['seller_firstname'], $this->currentSeller['seller_lastname']);
		$this->writeHTML($headerHTML);
	}


	/**
	 * Create a barcode sheet
	 *
	 * @return void
	 */
	public function create() {

		setlocale(LC_ALL, 'de_DE.UTF-8');

		foreach ($this->sellers as $seller) {

			$this->currentSeller = $seller;

			$this->AddPage();

			$this->Parser->setMultipleParserVars(array_merge($seller, $this->market));
			$html = $this->Parser->parseTemplate(PATHTOWEBROOT . "templates/sellers/seller_sumsheet.tpl");

			// die ($html);

			$this->writeHTML($html);
		}

		$this->Output($this->filename, 'I');
	}
}
?>

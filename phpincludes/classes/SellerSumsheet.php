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
		$this->setLeftMargin(20);
		$this->setRightMargin(20);


		// set auto page breaks
		$this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	}


	public function Header() {
		$this->setY(10);
		$this->Parser->setMultipleParserVars(array_merge($this->currentSeller, $this->market));
		$headerHTML = $this->Parser->parseTemplate(PATHTOWEBROOT . "templates/sellers/seller_sumsheet_header.tpl");
		$this->writeHTML($headerHTML);
	}

	public function Footer() {
		$this->setY(280);
		$footerHTML = $this->Parser->parseTemplate(PATHTOWEBROOT . "templates/sellers/seller_sumsheet_footer.tpl");
		$this->writeHTML($footerHTML);
	}


	/**
	 * Create a barcode sheet
	 *
	 * @param boolean 		$skipEmpty, if set, only sellers with sales will be
	 * 						output
	 * @return void
	 */
	public function create($skipEmpty = true) {

		setlocale(LC_ALL, 'de_DE.UTF-8');

		foreach ($this->sellers as $seller) {

			if($skipEmpty && empty($seller['sales'])) {
				continue;
			}

			$this->currentSeller = $seller;

			$this->AddPage();

			$this->Parser->setMultipleParserVars(array_merge($seller, $this->market));
			$html = $this->Parser->parseTemplate(PATHTOWEBROOT . "templates/sellers/seller_sumsheet.tpl");

			// jobr@2019-10-12 Don't use writeHTMLCell because of messedup
			// page-breaks with multi-page sellers
			// $this->writeHTMLCell(140, 0, 35, 20, $html, 0, 0, false);
			$this->writeHTML($html);
		}

		$this->Output($this->filename, 'I');
	}
}
?>
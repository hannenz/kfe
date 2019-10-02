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
	protected $sellerData;

	protected $sellers;
	protected $market;
	protected $filename;
 
	/**
	 * Constructor
	 */
	public function __construct($sellers, $market, $filename) {
		parent::__construct();

		$this->sellers = $sellers;
		$this->market = $market;
		$this->filename = $filename;

		// $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false, false);
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Johannes Braun');
		$this->SetTitle('Kinderflohmarkt Erbach - Verkäufer-Auswertung');

		// set auto page breaks
		// $this->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
	}


	public function Header() {
		$this->setXY(10, 10);
		$this->writeHTML(sprintf('Erbacher Kinderflohmart am %s', strftime('%d.%m %Y', strtotime($this->market['market_begin']))));
	}




	/**
	 * Create a barcode sheet
	 *
	 * @return void
	 */
	public function create() {

		setlocale(LC_ALL, 'de_DE.UTF-8');

		foreach ($this->sellers as $seller) {
			$this->AddPage();
			$this->setXY(10, 30);
			$this->writeHTML(sprintf("<b>%u</b> %s %s", $seller['seller_nr'], $seller['seller_firstname'], $seller['seller_lastname']));

			$html = '<table><tbody>';
			foreach ($seller['sales'] as $item) {
				$html .= sprintf('<tr><td>%s</td><td style="text-align:right">%.2f</td></tr>', $item['dateTimeFmt'], $item['valueEuro']);
			}
			$html .= '</tbody></table>';

			$this->writeHTML($html);
		}

		$this->Output($this->filename, 'I');
	}
}
?>

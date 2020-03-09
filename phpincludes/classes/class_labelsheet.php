<?php
/**
 * phpincludes/classes/class_labelsheet.php
 *
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 * @version 2019-09-15
 */
namespace KFE;

use \TCPDF;

// error_reporting(0);
// if (!ISPRODUCTION) {
// 	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 	ini_set('display_errors', true);
// }
//
require_once(PATHTOWEBROOT . "phpincludes/vendor/laurentbrieu/tcpdf/src/TCPDF/TCPDF.php");

class LabelSheetPdf extends TCPDF {

	/**
	 * Header with infos
	 *
	 * @return void
	 */
	public function Header() {
		$this->SetFont('helvetica', 'b', 7);
		$this->SetXY(0, 4);
		$this->SetTextColor(200, 50, 50);
		$this->MultiCell(0, 20, "In Originalgröße auf dickerem Papier (135/m2) drucken (z. Bsp. Tonpapier) oder mit Karton verstärken\nEtiketten sind nur gültig für die darauf gedruckte Verkäufer-Nummer", 0, 'C', false, 1);
	}

	/**
	 * Header with infos
	 *
	 * @return void
	 */
	public function Footer() {
		$this->SetFont('helvetica', 'b', 7);
		$this->SetTextColor(200, 50, 50);
		$this->SetXY(0, -15);
		$this->MultiCell(0, 20, "In Originalgröße auf dickerem Papier (135/m2) drucken (z. Bsp. Tonpapier) oder mit Karton verstärken\nEtiketten sind nur gültig für die darauf gedruckte Verkäufer-Nummer\nhttps://www.kinderflohmarkt-erbach.de", 0, 'C', false, 1);
	}
}


/**
 * @class LabelSheet
 *
 * Create a sheet of labels
 */
class LabelSheet {

	/**
	 * @var \TCPDF
	 */
	protected $pdf;

	/**
	 * @var Array
	 */
	protected $marketData;

	/**
	 * @var Array
	 */
	protected $sellerData;

	/**
	 * @var Array
	 */
	protected $options = [
		'label_width' => 90,
		'label_height' => 44,
		'gutter' => 0,
		'marginX' => 13.5,
		'marginY' => 13.5
	];

	

	/**
	 * @var Array
	 */
	protected $barcodeStyle = [
		'position' => '',
		'align' => 'C',
		'stretch' => true,
		'fitwidth' => false,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 0,
		'vpadding' => 0,
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => true,
		'font' => 'helvetica',
		'fontsize' => 6,
		'stretchtext' => 4
	];


	/**
	 * @param \KFE\Market
	 * @param \KFE\Seller
	 */
	public function __construct($marketData, $sellerData) {

		$this->marketData = $marketData;
		$this->sellerData = $sellerData;

		$this->pdf = new LabelSheetPdf('P', 'mm', 'A4', true, 'UTF-8', false, false);
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Johannes Braun');
		$this->pdf->SetTitle('Kinderflohmarkt Erbach - Etiketten-Bogen');

		// set auto page breaks
		$this->pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);

		$this->pdf->setPrintHeader(true);
		$this->pdf->setPrintFooter(true);
	}



	/**
	 * Create a barcode sheet
	 *
	 * @param Array 	$data
	 * @return void
	 */
	public function create($data, $filename = '') {
		if (empty($filename)) {
			$filename = sprintf('Kinderflohmarkt_Erbach_Etiketten_%u_%s.pdf', $this->sellerData['seller_nr'], strftime('%F-%H%M%S'));
		}
		// $this->pdf->AddPage();

		setlocale(LC_ALL, 'de_DE.UTF-8');


		$n = 0;
		foreach ($data as $value => $amount) {
			for ($i = 0; $i < $amount; $i++) {
				if ($n % 12 == 0) {
					$this->pdf->AddPage();
					$n = 0;
				}

				// Calc X/Y offset for next label
				$row = ((int)($n / 2));
				$col = ($n % 2); 

				$offy = $this->options['marginY'] + ($row * $this->options['label_height']) + $this->options['gutter'];
				$offx = $this->options['marginX'] + ($col * $this->options['label_width']) + $this->options['gutter'];


				/*********
				*  Box  *
				*********/
				// $this->pdf->writeHTMLCell(72, 12, $xoff + 16, $offy + 2, '<span>Gr&ouml;&szlig;e</span>', 1, 0, false, false, 'L', false); 
				$this->pdf->imageSVG(PATHTOWEBROOT . 'dist/img/label_template.svg', $offx, $offy, $this->options['label_width'], $this->options['label_height'], '', '', '', 0, false);
				$this->pdf->setFontSize(8);
				$this->pdf->SetXY($offx + 16, $offy + 3);
				$this->pdf->Cell(16, 18, "Größe", 0, 0, 'L', false, '', 0, false, 'T', 'T');
				$this->pdf->SetXY($offx + 32 , $offy + 3);
				$this->pdf->Cell(16, 18, "Bezeichnung", 0, 0, 'L', false, '', 0, false, 'T', 'T');


				/*************
				*  BarCode  *
				*************/
				$code = sprintf('%04u%s%04u%03u%05u',
					$this->marketData['id'],
					strftime('%Y%m%d', strtotime($this->marketData['market_begin'])),
					$this->sellerData['id'],
					$this->sellerData['seller_nr'],
					$value
				);
				$x = $offx + 16;
				$y = $offy + 22;
				$this->pdf->write1DBarcode($code, 'C128', $x, $y, 72, 14, 0.5, $this->barcodeStyle);


				/*****************
				*  Seller Number *
				*****************/
				$text = sprintf("Verkäufer-Nummer: <span style=\"font-size:12px\"><strong>%03u</strong></span>", $this->sellerData['seller_nr']);
				$this->pdf->SetXY($offx + 15, $offy + 37);
				// $this->pdf->setFontSize(12);
				// $this->pdf->Cell(30, 6, $text, 0, 0, 'L', false, '', 0, false, 'T', 'T');
				$this->pdf->writeHTML($text);


				/***********
				*  Value  *
				***********/
				$html = sprintf("<b>%.2f &euro;<b>", $value / 100);
				$x = $offx + 60;
				$y = $offy + 37;
				$this->pdf->setFontSize(12);
				$this->pdf->writeHTMLCell(30, 10, $x, $y, $html, 0, 0, false, true, 'R');


				/**
				 * Label "Kinderflohmarkt Erbach"
				 */
				$this->pdf->StartTransform();
				$this->pdf->Rotate(-270, $offx, $offy);
				$this->pdf->Translate(-1 * $this->options['label_height'] + 2.5, 2.5);
				$this->pdf->writeHTMLCell($this->options['label_height'], 15, $offx, $offy, "<div style=\"color:#b4b5b2\"><b>Kinderflohmarkt</b><br>Erbach</div>", 0, 0, false, true, 'L');
				$this->pdf->StopTransform();



				// // Write SellerNr
				// $x = $offx + 20;
				// $y = $offy + $this->options['label_height'] - 5;
				// $w = $this->options['label_width'] / 2;
				// $html = sprintf("Verk.-Nr: %03u", $this->sellerData['seller_nr']);
				// $this->pdf->writeHTMLCell($w, 10, $x, $y, $html, 0, 0, false, true, 'L');
                //

				// // Write value
				// $x = $offx + 20 + $this->options['label_width'] / 2;
				// $y = $offy + $this->options['label_height'] - 5 + 15;
				// $w = $this->options['label_width'] / 2;
				// $html = sprintf("<b>%.2f &euro;<b>", $value / 100);
				// $this->pdf->writeHTMLCell($w, 10, $x, $y, $html, 0, 0, false, true, 'R');
                //
				$n++;
			}
		}

		$this->pdf->Output($filename, 'I');
	}
}
?>

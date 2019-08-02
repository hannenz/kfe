<?php
namespace KFE;

use \TCPDF;

require_once(PATHTOWEBROOT . "phpincludes/vendor/laurentbrieu/tcpdf/src/TCPDF/TCPDF.php");

class BarcodeSheet {

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
		'barcode_width' => 50,
		'barcode_height' => 30,
		'gutter' => 10
	];

	/**
	 * @var Array
	 */
	protected $barcodeStyle = [
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
	];


	/**
	 * @param \KFE\Market
	 * @param \KFE\Seller
	 */
	public function __construct($marketData, $sellerData) {

		$this->marketData = $marketData;
		$this->sellerData = $sellerData;

		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Johannes Braun');
		$this->pdf->SetTitle('Kinderflohmarkt Erbach - Barcodes Sheet');

		// set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	}


	/**
	 * Create a barcode sheet
	 *
	 * @param Array 	$data
	 * @return void
	 */
	public function create($data, $filename = '') {
		if (empty($filename)) {
			$filename = sprintf('barcodes-%s.pdf', strftime('%F-%H'));
		}
		$this->pdf->AddPage();

		$n = 0;
		foreach ($data as $value => $amount) {
			for ($i = 0; $i < $amount; $i++) {
				$y = (int)($n / 3);
				$x = $n % 3; 

				$y *= $this->options['barcode_height'] + $this->options['gutter'];
				$x *= $this->options['barcode_width'] + $this->options['gutter'];

				$code = sprintf('%s%03u%05u',
					strftime('%Y%m%d', strtotime($this->marketData['market_datetime'])),
					$this->sellerData['id'],
					$value
				);
				$this->pdf->write1DBarcode($code, 'C128', 15 + $x, 15 + $y, $this->options['barcode_width'], $this->options['barcode_height'], 0.5, $this->barcodeStyle);
				$n++;
			}
		}

		$this->pdf->Output($filename, 'I');
	}
}
?>

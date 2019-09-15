<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use KFE\LabelSheet;
use KFE\Market;
use KFE\Seller;
use \Exception;

/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class LabelsController extends Controller {

	/**
	 * @var \KFE\LabelSheet;
	 */
	protected $LabelSheet;

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
		$this->templatesPath = PATHTOWEBROOT . "templates/labels/";
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
			$sellerNr = (int)$this->postvars['sellerNr'];
			$marketId = (int)$this->postvars['marketId'];

			$errors = [];

			try {
				$seller = $this->Seller->findBySellerNr($sellerNr);
				if (empty($seller)) {
					$errors[] = 'errorIllegalSellerId';
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

				if ($marketId != 0) {
					$market = $this->Market->findById($marketId);
					if (empty($market)) {
						$errors[] = 'errorIllegalMarketId';
						throw new Exception();
					}
				}

				$this->LabelSheet = new LabelSheet($market, $seller);
				$this->LabelSheet->create($data);
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
	 * Deprecated ?
	 *
	 * @return void
	 */
	public function actionValidateSeller() {

		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

		$success = false;

		$this->isAjax = true;
		$this->isJson = true;

		$email = $_REQUEST['email'];
		$sellerNr = (int)$_REQUEST['sellerNr'];
		$marketId = (int)$_REQUEST['marketId'];

		if (!empty($sellerNr) && !empty($email)) {
			$seller = $this->Seller->filter([
				'seller_market_id' => $marketId,
				'seller_nr' => $sellerNr,
				'seller_email' => $email
			])->findOne();

			$success = !empty($seller) || $this->Seller->isEmployee($sellerNr);
		}

		$this->content = compact('success');
	}
}

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new LabelsController();
$content = $ctl->work();
?>

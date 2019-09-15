<?php
namespace KFE;

error_reporting(E_ALL & ~E_NOTICE);

use Contentomat\Logger;
use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use Contentomat\CmtPage;
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
	 * @var Contentomat\CmtPage
	 */
	protected $CmtPage;

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
		$this->CmtPage = new CmtPage();
		// $session = Contentomat::getContentomat()->getSession();
		// echo '<pre>'; var_dump($session->getAllSessionVars()); echo '</pre>'; 
	}

	/**
	 * initActions
	 *
	 * @return void
	 */
	public function initActions($action = '') {
		parent::initActions($action);
		switch ($this->pageId) {
			case 17:
				$this->action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
				break;
			case 6:
				$this->action = 'composeSheet';
				break;
			default:
				$this->action = 'default';
				break;
		}
	}
	
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		// $this->changeAction('composeSheet');
	}


	/**
	 * Login
	 */
	public function actionLogin() {

		try {

			$market = $this->Market->getNextUpcoming();
			if (empty($market)) {
				throw new Exception('errorNoMarkets');
			}

			if (!empty($this->postvars)) {

				$seller = $this->Seller->findBySellerNr($this->postvars['seller_nr']);
				if (empty($seller)) {
					throw new Exception('errorLoginFailed');
				}


				if (!$this->Seller->authenticate($seller['seller_nr'], $this->postvars['seller_email'], $market['id'])) {
					throw new Exception('errorLoginFailed');
				}

				$this->Seller->login($seller);


				$redirectUrl = sprintf('%s%s',
					$this->CmtPage->makePageFilePath(6),
					$this->CmtPage->makePageFileName(6)
				);
				header('Location: ' . $redirectUrl);
				exit(0);
			}
		}
		catch (Exception $e) {
			$this->parser->setParserVar('error', true);
			$this->parser->setParserVar('errorCode', $e->getMessage());
		}

		$this->parser->setParserVar('market_id', $market['id']);
		$this->parser->setMultipleParserVars($market);
		$this->parser->setMultipleParserVars($this->postvars);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'login.tpl');
	}

	/**
	 * Logout
	 *
	 * @return void
	 */
	public function actionLogout() {
		Logger::log('Logging out');
		$this->Seller->logout();
		return $this->changeAction('login');
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

$ctl = new BarcodesController();
$content = $ctl->work();
?>

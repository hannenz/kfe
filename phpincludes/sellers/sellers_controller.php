<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use Contentomat\Mail;
use Contentomat\CmtPage;
use Contentomat\Logger;
use KFE\Seller;
use KFE\Market;
use KFE\SellerExistsForMarketException;
use \Exception;

/**
 * Class checkouts_controller
 * @author Johannes Braun <johannes.braun@hannenz.de>
 * @package kfe
 */
class SellersController extends Controller {

	/**
	 * @var int
	 */
	protected $activationPageId = 7;


	/**
	 * @var KFE\Seller;
	 */
	public $Seller;


	/**
	 * @var KFE\Market;
	 */
	public $Market;


	/**
	 * @var Contentomat\Mail;
	 */
	public $Mail;


	/**
	 * @var Contentomat\CmtPage;
	 */
	public $CmtPage;


	/**
	 * @var boolean
	 */
	public $isAjaxRequest;



	/**
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->Cmt = Contentomat::getContentomat();
		$this->Session = $this->Cmt->getSession();

		$this->Seller = new Seller();
		$this->Market = new Market();
		$this->Mail = new Mail();
		$this->CmtPage = new CmtPage();

		$this->templatesPath = PATHTOWEBROOT . "templates/sellers/";
		// $this->isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}


	/**
	 * undocumented function
	 *
	 * @return void
	 */
	protected function initActions($action = '') {
		// parent::initActions($action);

		if (!empty($_REQUEST['action'])) {
			$this->action = trim($_REQUEST['action']);
			return;
		}
		switch ($this->pageId) {
			case 17:
				$this->action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
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
	protected function actionDefault() {
		$this->changeAction('registrate');
	}


	/**
	 * Handle registration of a seller for a certain market
	 */
	protected function actionRegistrate() {

		try {
			$marketId = (int)$_REQUEST['market_id'];
			if (empty($marketId)) {
				$market = $this->Market->getNextUpcoming();
				$marketId = $market['id'];
			}
			else {
				$market = $this->Market->findById($marketId);
			}
			if (empty($market)) {
				die("Kein Markt mit ID: " . $marketId . "gefunden");
			}
			$this->parser->setParserVar('market_id', $marketId);
			if (!$this->Market->numberAssignmentIsRunning($market) && !$this->Session->checkIsLoggedIn()) {
				throw new RegistrationNotPossibleException();
			}
			$this->parser->setMultipleParserVars($market);

			if (!empty($this->postvars)) {
				$this->parser->setMultipleParserVars($this->postvars);
				$this->parser->setParserVar('market_id', $marketId);
				$this->parser->setMultipleParserVars($this->Market->findById($marketId));
				$email = $this->postvars['seller_email'];

				if (!$this->Seller->validate($this->postvars)) {
					throw new RegistrationValidationException();
				}

				$hash = $this->Seller->registrate($this->postvars);
				$this->sendActivationLink($email, $hash);

				$redirectUrl = sprintf('%s%s?action=activationPending&seller_email=%s&seller_nr=%u&seller_firstname=%s&seller_lastname=%s',
					$this->CmtPage->makePageFilePath(13),
					$this->CmtPage->makePageFileName(13),
					$email,
					$this->postvars['seller_nr'],
					urlencode($this->postvars['seller_firstname']),
					urlencode($this->postvars['seller_lastname'])
				);
				header("Location: " . $redirectUrl);
				exit;
			}
		}
		catch (SellerExistsForMarketException $e) {
			$this->parser->setParserVar('errorSellerExists', true);
		}
		catch (SellerNrAlreadyAllocatedException $e) {
			$this->parser->setParserVar('errorSellerNrAlreadyAllocated', true);
		}
		catch (RegistrationValidationException $e) {
			$this->parser->setParserVar('hasValidationErrors', true);
			$errors = $this->Seller->getValidationErrors();
			foreach ($errors as $field => $error) {
				$this->parser->setParserVar('error_'.$field, true);
			}
		}
		catch (RegistrationNotPossibleException $e) {
			$this->Session->setSessionVar('flashMessage', 'Für den Flohmarkt mit dieser ID ist zur Zeit keine Registrierung möglich');
			$this->Session->saveSessionVars();
			header('Location: /');
			exit;

		}
		catch (\Exception $e) {
			$this->parser->setParserVar('error_other', true);
			$this->parser->setParserVar('errorMessage', $e->getMessage());
		}


		$_availableNumbers = $this->Market->getAvailableNumbers($marketId);
		$availableNumbers = [];
		foreach ($_availableNumbers as $nr) {
			$availableNumbers[] = ['nr' => $nr];
		}
		$this->parser->setParserVar('availableNumbers', $availableNumbers);
		if (!isset($_REQUEST['seller_nr'])) {
			$this->parser->setParserVar('seller_nr', $_availableNumbers[0]);
		}
		$this->parser->setParserVar('seller_nr', isset($_REQUEST['seller_nr']) ? $_REQUEST['seller_nr'] : $_availableNumbers[0]);

		$this->parser->setParserVar('markets', $this->Market->getMarketsWithOpenNumberAssignment());
		$this->content = $this->parser->parseTemplate($this->templatesPath . "registration.tpl");
	}

	// protected function actionTestActivationLink() {
	// 	$this->sendActivationLink("jbhannenz@gmail.com", "5226d09be923305fca3c5c20b052309e68fca775a6f54b1694388e713474662f");
	// }

	/**
	 * Send an email containing an activation link
	 *
	 * @return void
	 */
	protected function sendActivationLink($email, $hash) {
		$activationUrl = sprintf('http%s://%s%s%s?action=activate&hash=%s',
			!empty($_SERVER['HTTPS']) ? 's' : '', 
			$_SERVER['SERVER_NAME'],
			$this->CmtPage->makePageFilePath($this->activationPageId),
			$this->CmtPage->makePageFileName($this->activationPageId),
			$hash
		);
		$this->parser->setParserVar('email', $email);
		$this->parser->setParserVar('activationUrl', $activationUrl);

		// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
		$seller = $this->Seller->findByEmailAndHash($email, $hash);
		$market = $this->Market->findById($seller['seller_market_id']);
		$this->parser->setMultipleParserVars($market);
		$this->parser->setMultipleParserVars($seller);

		$text = $this->parser->parseTemplate($this->templatesPath . "activation_mail.txt.tpl");
		$mailContent = $this->parser->parseTemplate($this->templatesPath . 'activation_mail.html.tpl');
		$this->parser->setParserVar('mailContent', $mailContent);
		$html = $this->parser->parseTemplate(PATHTOWEBROOT . 'templates/email.tpl');

		$check = $this->Mail->send([
			'recipient' => $email,
			'subject' => 'Kinderflohmarkt Erbach: Registrierung abschliessen',
			'text' => $text,
			'html' => $html
		]);

		Logger::log(sprintf("Sending activation mail to <%s>: %s", $email, $check ? "success" : "failed"));

		if ($check !== true) {
			echo '<pre>'; var_dump($this->Mail->getErrorMessage()); echo '</pre>'; die();
		}
	}


	protected function actionActivate() {
		$hash = $this->getvars['hash'];
		if (empty($hash)) {
			// TODO: Handle this case!
			die ("No hash!");
		}

		try {
			$seller = $this->Seller->activate($hash);

			$this->sendWelcomeMail($seller);

			$redirectUrl = sprintf('%s%s?action=success&seller_email=%s&seller_nr=%u&seller_firstname=%s&seller_lastname=%s',
				$this->CmtPage->makePageFilePath(14),
				$this->CmtPage->makePageFileName(14),
				$seller['seller_email'],
				$seller['seller_nr'],
				urlencode($seller['seller_firstname']),
				urlencode($seller['seller_lastname'])
			);
			header("Location: " . $redirectUrl);
			exit;
		}
		catch (ActivationFailedException $e) {
			$this->parser->setParserVar('errorActivationFailed', true);
		}
		catch (Exception $e) {
			$this->parser->setParserVar('errorInternal', true);
		}
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'activation_failed.tpl');
	}


	/**
	 * Activation screen
	 */
	protected function actionActivationPending() {
		$this->parser->setMultipleParserVars($_REQUEST);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'activation_pending.tpl');
	}
	
	/**
	 * Success screen
	 */
	protected function actionSuccess() {
		$this->parser->setMultipleParserVars($_REQUEST);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'registration_success.tpl');
	}
	
	/**
	 * After successful activation, send a welcome mail
	 * with the seller's "number" (id)
	 *
	 * @access public
	 */
	protected function sendWelcomeMail($seller) {

		$market = $this->Market->findById($seller['seller_market_id']);
		$this->parser->setMultipleParserVars(array_merge($seller, $market));
		$this->parser->setParserVar('sellerId', $seller['id']);

		$text = $this->parser->parseTemplate($this->templatesPath . 'welcome.txt.tpl');
		$mailContent = $this->parser->parseTemplate($this->templatesPath . 'welcome.html.tpl');
		$this->parser->setParserVar('mailContent', $mailContent);
		$html = $this->parser->parseTemplate(PATHTOWEBROOT . 'templates/email.tpl');

		$check = $this->Mail->send([
			'recipient' => $seller['seller_email'],
			'subject' => 'Kinderflohmarkt Erbach: Registrierung erfolgreich abgeschlossen',
			'text' => $text,
			'html' => $html
		]);
		Logger::log(sprintf("Sending welcome mail to <%s>: %s", $seller['seller_email'], $check ? "success" : "failed"));

		if (!$check) {
			$this->parser->setParserVar('errorSendMail', true);
		}

		$this->parser->parseTemplate($this->templatesPath . 'welcome.tpl');
	}
	

	protected function actionValidateField() {
		$this->isJson = true;
		$this->isAjax = true;

		$data = $this->postvars;
		$success = $this->Seller->validateFormField($data['fieldName'], $data[$data['fieldName']], $data);
		$this->content = [
			'success' => $success,
			'validationErrors' => $this->Seller->getValidationErrors()
		];
	}


	protected function actionUpdateAvailableSellerNrs() {
		$this->isJson = true;
		$this->isAjax = true;

		$marketId = $this->getvars['marketId'];
		$this->content = $this->Market->getAvailableNumbers($marketId);
	}



	/**
	 * login
	 */
	protected function actionLogin() {

		try {

			$market = $this->Market->getNextUpcoming();
			if (empty($market)) {
				throw new Exception('errorNoMarkets');
			}

			if (!empty($this->postvars)) {

				$seller = $this->Seller->findBySellerNr($this->postvars['seller_nr'], $market['id']);
				if (empty($seller)) {
					throw new Exception('errorLoginFailed');
				}

				if (!$this->Seller->authenticate($seller['seller_nr'], $this->postvars['seller_email'], $market['id'])) {
					throw new Exception('errorLoginFailed');
				}

				$this->Seller->login($seller);

				$redirectUrl = sprintf('%s%s',
					$this->CmtPage->makePageFilepath(6),
					$this->CmtPage->makePageFilename(6)
				);
				header('Location: ' . $redirectUrl);
				exit(0);
			}
		}
		catch (Exception $e) {
			$this->parser->setMultipleParserVars([
				'error' => true,
				'errorCode' => $e->getMessage()
			]);
		}

		$this->parser->setParservar('market_id', $market['id']);
		$this->parser->setMultipleParserVars($market);
		$this->parser->setMultipleParserVars($this->postvars);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'login.tpl');
	}

	/**
	 * logout
	 *
	 * @return void
	 */
	protected function actionLogout() {
		$this->Seller->logout();
		header("Location: /");
		die();
		return $this->changeAction('login');
	}
}

$autoLoader = new PsrAutoloader();
$autoLoader->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$autoLoader->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new SellersController();
$content = $ctl->work();
?>

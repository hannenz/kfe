<?php
namespace KFE;

use Contentomat\Contentomat;
use Contentomat\Controller;
use Contentomat\PsrAutoloader;
use Contentomat\Mail;
use Contentomat\CmtPage;
use KFE\Seller;
use KFE\Market;
use KFE\SellerExistsForMarketException;

error_reporting(E_ALL & ~E_NOTICE);
setlocale(LC_TIME, 'de_DE.UTF-8');

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
	 * Init
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$this->Seller = new Seller();
		$this->Market = new Market();
		$this->Mail = new Mail();
		$this->CmtPage = new CmtPage();
		$this->templatesPath = PATHTOWEBROOT . "templates/sellers/";
	}
	
	/**
	 * Default action
	 *
	 * @return void
	 */
	public function actionDefault() {
		$this->changeAction('registrate');
	}


	/**
	 * Handle registration of a seller for a certain market
	 */
	public function actionRegistrate() {

		$marketId = (int)$_REQUEST['market_id'];
		if (!empty($marketId)) {
			$this->parser->setParserVar('market_id', $marketId);
			$market = $this->Market->findById($marketId);
			$this->parser->setMultipleParserVars($market);
		}
		else {
			die ("No market_id!");
		}

		if (!empty($this->postvars)) {
			$this->parser->setMultipleParserVars($this->postvars);
			$this->parser->setParserVar('market_id', $marketId);
			$this->parser->setMultipleParserVars($this->Market->findById($marketId));
			$email = $this->postvars['seller_email'];

			try {

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
			catch (\Exception $e) {
				$this->parser->setParserVar('errorDatabaseQuery', true);
			}
		}


		$_availableNumbers = $this->Seller->getAvailableNumbers($marketId);
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

	/**
	 * Send an email containing an activation link
	 *
	 * @return void
	 */
	public function sendActivationLink($email, $hash) {
		$activationUrl = sprintf('http%s://%s%s%s?action=activate&hash=%s',
			!empty($_SERVER['HTTPS']) ? 's' : '', 
			$_SERVER['SERVER_NAME'],
			$this->CmtPage->makePageFilePath($this->activationPageId),
			$this->CmtPage->makePageFileName($this->activationPageId),
			$hash
		);
		$this->parser->setParserVar('email', $email);
		$this->parser->setParserVar('activationUrl', $activationUrl);

		$text = $this->parser->parseTemplate($this->templatesPath . "activation_mail.txt.tpl");
		$html = $this->parser->parseTemplate($this->templatesPath . "activation_mail.html.tpl");

		$check = $this->Mail->send([
			'recipient' => $email,
			'subject' => 'Kinderflohmarkt Erbach: Registrierung abschliessen',
			'text' => $text,
			'html' => $html
		]);
		if ($check !== true) {
			echo '<pre>'; var_dump($this->Mail->getErrorMessage()); echo '</pre>'; die();
		}
	}


	public function actionActivate() {
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
	public function actionActivationPending() {
		$this->parser->setMultipleParserVars($_REQUEST);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'activation_pending.tpl');
	}
	
	/**
	 * Success screen
	 */
	public function actionSuccess() {
		$this->parser->setMultipleParserVars($_REQUEST);
		$this->content = $this->parser->parseTemplate($this->templatesPath . 'registration_success.tpl');
	}
	
	/**
	 * After successful activation, send a welcome mail
	 * with the seller's "number" (id)
	 *
	 * @access public
	 */
	public function sendWelcomeMail($seller) {

		$market = $this->Market->findById($seller['seller_market_id']);
		$this->parser->setMultipleParserVars(array_merge($seller, $market));
		$this->parser->setParserVar('sellerId', $seller['id']);

		$text = $this->parser->parseTemplate($this->templatesPath . 'welcome.txt.tpl');
		$html = $this->parser->parseTemplate($this->templatesPath . 'welcome.html.tpl');

		$check = $this->Mail->send([
			'recipient' => $seller['seller_email'],
			'subject' => 'Kinderflohmarkt Erbach: Registrierung erfolgreich abgeschlossen',
			'text' => $text,
			'html' => $html
		]);

		if (!$check) {
			$this->parser->setParserVar('errorSendMail', true);
		}

		$this->parser->parseTemplate($this->templatesPath . 'welcome.tpl');
	}
	
}

$al = new PsrAutoloader();
$al->addNamespace('Contentomat', INCLUDEPATHTOADMIN . "classes");
$al->addNamespace('KFE', PATHTOWEBROOT . "phpincludes/classes");

$ctl = new SellersController();
$content = $ctl->work();
?>

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

	public function actionRegistrate() {
		// var_dump("check"); die();
		$marketId = $_REQUEST['market_id'];
		if (!empty($marketId)) {
			$this->parser->setParserVar('market_id', $marketId);
		}

		if (!empty($this->postvars['email'])) {
			$this->parser->setMultipleParserVars($this->postvars);
			$this->parser->setParserVar('market_id', $marketId);
			$this->parser->setMultipleParserVars($this->Market->findById($marketId));
			$email = $this->postvars['email'];

			try {

				if ($this->postvars['email'] !== $this->postvars['email_confirm']) {
					throw new EmailsDontMatchException();
				}

				$hash = $this->Seller->registrate($email, $marketId);
				$this->sendActivationLink($email, $hash);
				die ("Check your e-mails!");
				// return $this->changeAction('');
			}
			catch (InvalidEmailException $e) {
				$this->parser->setParserVar('errorInvalidEmail', true);
			}
			catch (EmailsDontMatchException $e) {
				$this->parser->setParserVar('errorEmailsDontMatch', true);
			}
			catch (SellerExistsForMarketException $e) {
				$this->parser->setParserVar('errorSellerExists', true);
			}
			catch (Exception $e) {
				$this->parser->setParserVar('errorDatabaseQuery', true);
			}
		}


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

		}

		try {
			$seller = $this->Seller->activate($hash);
		}
		catch (ActivationFailedException $e) {
			$this->parser->setParserVar('errorActivationFailed', true);
		}
		catch (Exception $e) {
			$this->parser->setParserVar('errorInternal', true);
		}

		$this->sendWelcomeMail($seller);
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

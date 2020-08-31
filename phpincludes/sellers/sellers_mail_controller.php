<?php
namespace KFE;

use Contentomat\Mail;
use Contentomat\ApplicationController;
use Contentomat\Logger;
use KFE\Seller;
use KFE\Market;
use \Exception;


class SellersMailController extends ApplicationController {


	/**
	 * @var Contentomat\Mail
	 */
	protected $Mail;

	/**
	 * @var KFE\Market
	 */
	protected $Market;

	/**
	 * @var KFE\Seller
	 */
	protected $Seller;


	public function init() {

		$this->cmt->addAutoloadNamespace('KFE', PATHTOWEBROOT . 'phpincludes/classes');
		$this->parser->setDefaultTemplateBasePath(PATHTOWEBROOT . "templates/sellers/");

		$this->Mail = new Mail();
		$this->Seller = new Seller();
		$this->Market = new Market();
	}



	public function actionDefault() {
		$sellers = $this->Seller->findAll();
		$markets = $this->Market->findAll();
		$this->parser->setParserVar('markets', $markets);
		$this->parser->setMultipleParserVars([
			'senderEmail' => 'info@kinderflohmarkt-erbach.de',
			'batchSize' => 25
		]);
		$tpl = $this->templatesPath . "sellers_mail.tpl";
		$this->content = $this->parser->parseTemplate($this->templatesPath . "sellers_mail.tpl");
	}



	public function actionAddRecipient() {
		$this->isAjax = true;
		$this->isJson = true;
	}



	public function actionAddRecipientsByMarket() {
		$this->isAjax = true;
		$this->isJson = true;

		$sellers = (array)$this->Seller->findByMarket($this->getvars['marketId']);
		$this->content = array_values($sellers);
	}

	public function actionAddRecipientsEmployees() {
		$this->isAjax = true;
		$this->isJson = true;

		$this->content = array_values((array)$this->Seller->getEmployees());
	}

	/**
	 * Send mail to sellers ("Rundmail")
	 * Is this action unused/deprecated?
	 *
	 * @return void
	 */
	public function actionMail() {

		$market = (array)$this->Market->findById($this->postvars['sellerMarketId']);
		$sellers = $this->Seller->findByMarket($this->postvars['sellerMarketId']);

		$this->Parser->setMultipleParserVars(array_merge(
			$this->postvars,
			$market,
			['sellers' => $sellers ]
		));

		$this->content = $this->Parser->parseTemplate($this->templatesPath . 'compose_mail.tpl');
	}



	public function actionSendMail() {

		$this->isAjax = true;
		$this->isJson = true;

		$sellers = [];
		$_sellers = $this->Seller->findAllByIds(explode(',', $this->postvars['id']));
		foreach ($_sellers as $seller) {
			$sellers[] = $seller;
		}

		$mailBatch = [
			'sellers' => $sellers,
			'iter' => 0,
			'subject' => $this->postvars['subject'],
			'message' => $this->postvars['text'],
			'batch_size' => $this->postvars['batch_size']
		];

		$this->session->setSessionVar('mailBatch', $mailBatch);
		$this->session->saveSessionVars();
	}



	public function actionSendMailBatch() {

		$this->isAjax = true;
		$this->isJson = true;

		$success = 0;
		$mailBatch = $this->session->getSessionVar('mailBatch');

		if (!empty($mailBatch)) {

			$k = $mailBatch['batch_size'];

			while ($mailBatch['iter'] < count($mailBatch['sellers'])) {

				if (--$k < 0) {
					break;
				}

				$seller = $mailBatch['sellers'][$mailBatch['iter']];
				try {

					if (!filter_var($seller['seller_email'], FILTER_VALIDATE_EMAIL)) {
						throw new Exception(sprintf("Invalid email: <%s>", $seller['seller_email']));
					}

					// Parse message, e.g. to personalize ..
					$this->parser->setMultipleParserVars($seller);
					$mailContent = $this->parser->parse(nl2br($mailBatch['message']));
					$subject = $this->parser->parse($mailBatch['subject']);

					// Parse mail frame template
					$this->parser->setParserVar('mailContent', $mailContent);
					$html = $this->parser->parseTemplate(PATHTOWEBROOT . '../templates/email.tpl');

					// Send mail
					Logger::log(sprintf("Sending E-Mail to <%s>", $seller['seller_email']));
					$check = $this->Mail->send([
						'recipient' => 'me@hannenz.de', //$seller['seller_email'],
						'subject' => $subject,
						'text' => strip_tags($mailBatch['html']),
						'html' => $html,
						'fake' => false
					]);
					if (!$check) {
						throw new Exception(sprintf("Sending mail to <%s> failed: %s", $seller['seller_email'], $this->Mail->getErrorMessage()));
					}

					$success++;
					break;
				}
				catch (Exception $e) {
					Logger::warn($e->getMessage());
				}

				$mailBatch['iter']++;
			}
		}

		// Update batch counter
		$this->session->setSessionVar('mailBatch', $mailBatch);
		$this->session->saveSessionVars();

		$this->content = [
			'success' => $success,
			'count' => $mailBatch['iter'],
			'total' => count($mailBatch['sellers'])
		];
	}
}

$ctl = new SellersMailController();
$content = $ctl->work();
?>

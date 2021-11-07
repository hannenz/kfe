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

		$this->cmt->setErrorReporting('warning');

		$this->cmt->addAutoloadNamespace('KFE\\', PATHTOWEBROOT . 'phpincludes/classes');
		$this->parser->setDefaultTemplateBasePath(PATHTOWEBROOT . "templates/sellers/");

		$this->Mail = new Mail();
		$this->Seller = new Seller();
		$this->Market = new Market();

		$this->marketId = $this->getvars['marketId'] ?? null;
	}


	/**
	 * New 2021-11-06: Compose for certain market.
	 * This is the common use case and can be called from table index
	 * Now using single Request with Server Sent Events. We don't expect more
	 * than f.e. 500 recipients and with `set_time_limit(0)` this shoul be ok
	 */
	public function actionCompose() {
		$this->parser->setMultipleParserVars(array_merge(
			(array)$this->Market->findById($this->marketId),
			[
				'marketId' => $this->marketId,
				'sellers' => (array)$this->Seller->findByMarket($this->marketId),
				'senderMail' => 'info@kinderflohmarkt-erbach.de',
				'senderName' => 'Kinderflohmarkt Erbach',
				'subject' => 'Test E-Mail. Bitte ignorieren',
				'message' => 'Dies ist eine Test E-Mail die eigentlich nicht wirklich versendet werden soll. Wenn Sie diese Nachricht dennoch erhalten ist leider etwas schief gelaufen. Bitte igorieren Sie diese Nachricht und entschuldigen Sie die Unannehmlichkeiten.'
			]
		));
		$this->content = $this->parser->parseTemplate($this->templatesPath . "compose_mail.tpl");
	}

	public function actionSend() {
		$data = [
			'recipientIds' => $this->postvars['id'],
			'senderName' => $this->postvars['senderName'],
			'senderMail' => $this->postvars['senderMail'],
			'subject' => $this->postvars['subject'],
			'message' => $this->postvars['message'],
		];
		$this->session->setMultipleSessionVars($data);
		$this->session->saveSessionVars();
		$this->isAjax = true;
		$this->content = 'ok';
	}

	public function actionSendLoop() {
		header('Cache-Control: no-cache');
		header("Content-Type: text/event-stream\n\n");

		$recipientIds = $this->session->getSessionVar('recipientIds');
		$senderName = $this->session->getSessionVar('senderName');
		$senderMail = $this->session->getSessionVar('senderMail');
		$subject = $this->session->getSessionVar('subject');
		$message = $this->session->getSessionVar('message');
		$sellers = $this->Seller->findAllByIds($recipientIds);
		$n = 0;
		foreach ($sellers as $seller) {
			if (!filter_var($seller['seller_email'], FILTER_VALIDATE_EMAIL)) {
				Logger::error("Invalid email: " . $seller['seller_email']);
				$n++;
				continue;
			}

			// Parse message, e.g. to personalize ..
			$this->parser->setMultipleParserVars($seller);
			$mailContent = $this->parser->parse(nl2br($message));
			$subject = $this->parser->parse($subject);

			// Parse mail frame template
			$this->parser->setParserVar('mailContent', $mailContent);
			$html = $this->parser->parseTemplate(PATHTOWEBROOT . '../templates/email.tpl');

			// Send mail
			$sendParams = [
				'recipient' => $seller['seller_email'],
				'subject' => $subject,
				'text' => strip_tags($html),
				'html' => $html,
				'fake' => true
			];

			$check = $this->Mail->send($sendParams);
			Logger::log(sprintf("%s E-Mail to <%s>: %s",
				$sendParams['fake'] ? "Faking" : "Sending",
				$sendParams['recipient'],
				$check ? "OK" : "FAILED"
			), $check ? LOG_LEVEL_INFO : LOG_LEVEL_WARNING);
			if (!$check) {
				throw new Exception(sprintf("Sending mail to <%s> failed: %s", $sendParams['recipient'], $this->Mail->getErrorMessage()));
			}

			$data = [
				'success' => $check,
				'recipient' => $seller['seller_email'],
				'fake' => $sendParams['fake'],
				'iter' => ++$n,
				'total' => count($sellers)
			];

			echo "event: mailSent\n";
			echo 'data: ' . json_encode($data);
			echo "\n\n";
			ob_end_flush();
			flush();
			if ($sendParams['fake']) {
				usleep(0.1 * 1000000);
			}
		}
	}


	/**
	 * Old(er)
	 * More flexible function
	 */
	public function actionDefault() {
		$sellers = $this->Seller->findAll();
		$markets = $this->Market->findAll();
		$this->parser->setParserVar('markets', $markets);
		$this->parser->setMultipleParserVars([
			'senderEmail' => 'info@kinderflohmarkt-erbach.de',
			'batchSize' => 20,
			'batchPause' => 5 * 60,
			'subject' => 'Test-Betreff',
			'text' => 'Die ist nur eine Test-Mail. Bitte ignorieren.'
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
		Logger::log(sprintf('%u', $this->getvars['marketId']));

		$sellers = (array)$this->Seller->findByMarket($this->getvars['marketId'], [ 'includeEmployees' => false ]);
		Logger::log(sprintf('%u', count($sellers)));
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
			'batch_size' => $this->postvars['batch_size'],
			'batch_pause' => $this->postvars['batch_pause']
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
					$sendParams = [
						'recipient' => $seller['seller_email'],
						'subject' => $subject,
						'text' => strip_tags($mailBatch['html']),
						'html' => $html,
						'fake' => false
					];

					$check = $this->Mail->send($sendParams);
					Logger::log(sprintf("%s E-Mail to <%s>: %s",
						$sendParams['fake'] ? "Faking" : "Sending",
						$sendParams['recipient'],
						$check ? "OK" : "FAILED"
					), $check ? LOG_LEVEL_INFO : LOG_LEVEL_WARNING);
					if (!$check) {
						throw new Exception(sprintf("Sending mail to <%s> failed: %s", $sendParams['recipient'], $this->Mail->getErrorMessage()));
					}

					$success++;
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

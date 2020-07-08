<?php
namespace KFE;

use Contentomat\Mail;
use Contentomat\ApplicationController;
use KFE\Seller;
use KFE\Market;


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
		$this->Mail = new Mail();
		$this->Seller = new Seller();
		$this->Market = new Market();
		$this->parser->setDefaultTemplateBasePath(PATHTOWEBROOT . "templates/sellers/");
	}



	public function actionDefault() {
		$sellers = $this->Seller->findAll();
		$markets = $this->Market->findAll();
		// echo '<pre>'; var_dump($markets); echo '</pre>'; die();
		$this->parser->setParserVar('markets', $markets);
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
		$query = sprintf("SELECT * FROM kfe_sellers WHERE id IN (%s)", join(',', $this->postvars['id']));

		$sellers = $this->Seller->query($query);



		$mailBatch = [
			'sellers' => $sellers,
			'iter' => 0,
			'subject' => $this->postvars['subject'],
			'message' => $this->postvars['message'],
			'batch_size' => 5
		];
		$this->Session->setSessionVar('mailBatch', $mailBatch);
	}



	public function actionSendMailBatch() {
		$this->isAjax = true;
		$this->isJson = true;

		$maillBatch = $this->Session->getSessionVar('mailBatch');

		$count = 0;
		$success = 0;
		for ($i = 0; $i < $mailBatch['batch_size'] && $i < count($this->mailBatch['sellers']); $i++) {
			$j = $mailBatch['iter'] + $i;
			$seller = $mailBatch['sellers'][$j];

			// Parse message, e.g. to personalize ..
			$this->Parser->setMultipleParserVars($seller);
			$mailContent = $this->Parser->parse($mailBatch['message']);

			// Parse mail frame template
			$this->Parser->setParserVar('mailContent', $mailContent);
			$html = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/email.tpl');

			// Send mail
			$check = $this->Mail->send([
				'recipient' => $seller['seller_email'],
				'subject' => $mailBatch['subject'],
				'text' => strip_tags($mailBatch['html']),
				'html' => $mailBatch['html'],
				'fake' => true
			]);

			$count++;
			if ($check) {
				$success++;
			}
		}

		// Update batch counter
		$mailBatch['iter'] += $i;

		$this->content = [
			'success' => $success,
			'count' => $count,
			'total' => count[$mailBatch]['sellers']
		];
	}


}

$ctl = new SellersMailController();
$content = $ctl->work();
?>

<?php
namespace KFE;

use Contentomat\Model;
use Contentomat\Session;
use Contentomat\Parser;
use Contentomat\Mail;
use Contentomat\Logger;
use \Exception;

class Circular extends Model {

	/**
	 * @var \Contentomat\Session
	 */
	protected $session;

	/**
	 * @var \Contentomat\Parser;
	 */
	protected $parser;

	/**
	 * @var \Contentomat\Mail;
	 */
	protected $Mail;

	public function init() {
		$this->setTablename('kfe_circulars');
		$this->session = $this->cmt->getSession();
		$this->parser = new Parser();
		$this->Mail = new Mail();
	}


	/**
	 * @throws Exception
	 */
	public function send($id) {
		$data = $this->findById($id);
		if (empty($data)) {
			throw new Exception("No circular found for id: ${id}");
		}

		$this->session->setSessionVar('data', $data);
		$this->session->saveSessionVars();
	}


	public function sendLoop() {
		header('Cache-Control: no-cache');
		header("Content-Type: text/event-stream\n\n");

		$circularData = $this->session->getSessionVar('data');
		if (empty($circularData)) {
			throw new Exception("No data");
		}

		$recipients = array_filter((array)json_decode($circularData['circular_recipients'], true), function($r) {
			return ($r['active'] && filter_var($r['email'], FILTER_VALIDATE_EMAIL));
		});
		$recipients = array_values($recipients);
		$total = count($recipients);

		foreach ($recipients as $n => &$recipient) {

			// Parse message, e.g. to personalize ..
			$this->parser->setMultipleParserVars($recipient);
			$this->parser->setMultipleParserVars($this->Market->findById($circularData['circular_market_id']);
			$mailContent = $this->parser->parse(nl2br($circularData['message']));
			$subject = $this->parser->parse($circularData['subject']);

			// Parse mail frame template
			$this->parser->setParserVar('mailContent', $mailContent);
			$html = $this->parser->parseTemplate(PATHTOWEBROOT . '../templates/email.tpl');

			// Send mail
			$sendParams = [
				'recipient' => $recipient['email'],
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
				'iter' => $n,
				'total' => $total
			];

			echo "event: mailSent\n";
			echo 'data: ' . json_encode($data);
			echo "\n\n";
			ob_end_flush();
			flush();

			// Update record
			$recipients[$n]['sent_datetime'] = strftime('%Y-%m-%d %T');
			$recipients[$n]['sent_success'] = $check;

			if ($sendParams['fake']) {
				usleep(0.1 * 1000000);
			}
		}

		try {
			$this->saveField($circularData['id'], 'circular_recipients', json_encode($recipients));
			$this->saveField($circularData['id'], 'circular_status', 'sent');
			$this->saveField($circularData['id'], 'circular_sender_date', strftime('%Y-%m-%d %T'));
		}
		catch (Exception $e) {
			die ($e->getMessage());
		}

		echo "event: done\n";
		echo "data: none\n";
		echo "\n";
		ob_end_flush();
		flush();
	}
}

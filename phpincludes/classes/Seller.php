<?php
namespace KFE;

use Contentomat\Model;
use Contentomat\Mail;
use Contentomat\Contentomat;
use Contentomat\FieldHandler;
use Contentomat\FileHandler;
use Contentomat\Logger;
use Contentomat\CmtPage;
use Contentomat\Parser;
use \Exception;
use \KFE\Market;
use \KFE\Cart;
use \KFE\Item;
use \KFE\SellerSumsheet;

class RegistrationValidationException extends Exception { }
class SellerExistsForMarketException extends Exception { }
class ActivationFailedException extends Exception { }
class InvalidEmailException extends Exception { }
class EmailsDontMatchException extends Exception { }
class SellerNrAlreadyAllocatedException extends Exception{ }
class RegistrationNotPossibleException extends Exception { }
class NumberAssignmetNotRunningException extends Exception { } 



class Seller extends Model {


	/**
	 * @var Contentomat\Mail
	 */
	protected $Mail;

	/**
	 * @var Contentomat\Contentomat
	 */
	protected $Cmt;

	/**
	 *
	 * @var Contentomat\Session
	 */
	protected $Session;

	/**
	 * @var Contentomat\FieldHandler
	 */
	protected $FieldHandler;

	/**
	 * @var Contentomat\FileHandler
	 */
	protected $FileHandler;

	/**
	 * @var Contentomat\CmtPage
	 */
	protected $CmtPage;

	/**
	 * @var Contentomat\Parser
	 */
	protected $Parser;

	/**
	 * @var KFE\Item
	 */
	protected $Item;

	/**
	 * @var KFE\Market
	 */
	protected $Market;


	public function init() {
		$this->Mail = new Mail();
		$this->FieldHandler = new FieldHandler();
		$this->FileHandler = new FileHandler();
		$this->CmtPage = new CmtPage();
		$this->Parser = new Parser();
		$this->Cmt = Contentomat::getContentomat();
		$this->Session = $this->Cmt->getSession();
		$this->tableName = 'kfe_sellers';
		$this->Cart = new Cart();
		$this->Item = new Item();
		$this->setValidationRules([
			'seller_firstname' => ['not-empty' => '/^.+$/'],
			'seller_lastname' => ['not-empty' => '/^.+$/'],
			'seller_email' => [ 'valid-email' =>  '/^.+@.+\..+$/' ],
			'seller_phone' => [ 'valid-phone' =>  '/^[\s0-9\.\/\-\+\(\)]+$/' ],
			'seller_email_confirm' => [ 'match' => 'matchEmails' ],
			'agree' => ['agree' => '/^agreed$/'],
			// 'seller_nr' => ['seller-nr-is-unique' => 'sellerNrIsUnique']
		]);
	}


	/**
	 * Get a seller by its seller nr (and market_id)
	 *
	 * @param int sellerNr
	 * @param int marketId
	 * @return Array
	 */
	public function findBySellerNr($sellerNr, $marketId) {
		$query = sprintf('SELECT * FROM %s WHERE seller_nr = %u AND (seller_market_id = 0 OR seller_market_id = %u)',
			$this->tableName,
			$sellerNr,
			$marketId
		);

		$result = array_shift($this->query($query));
		return $result;
	}



	/**
	 * Get a seller's id from sellerNr & marketId
	 *
	 * @param int sellerNr
	 * @param int marketId
	 * @return int
	 */
	public function getSellerId($sellerNr, $marketId) {
		$seller = $this->findBySellerNr($sellerNr, $marketId);
		if (empty($seller)) {
			return 0;
		}
		return $seller['id'];
	}


	/**
	 * Returns all sellers for a given market, including employees (marketId = 0)
	 * 
	 * @param int 		$marketid
	 * @return Array
	 * @access public
	 */
	public function findByMarket($marketId) {
		$query = sprintf("SELECT * FROM %s WHERE seller_market_id = 0 OR seller_market_id = %u AND seller_is_activated = 1 ORDER BY seller_nr ASC",
			$this->tableName,
			$marketId
		);
		return $this->query($query);
	}



	/**
	 * Validate that both entered email adresses match
	 *
	 * @access public
	 * @param String 		E-Mail address to validate
	 * @param Array 		The remaining request data
	 * @return boolean
	 */
	protected function matchEmails($email, $data) {
		return (
			filter_var($email, FILTER_VALIDATE_EMAIL) && 
			($email == $data['seller_email'])
		);
	}


	/**
	 * Validation rule callback:
	 * Verify that the desired seller nr is not taken yet (is unique)
	 * at the given market
	 *
	 * @param int 		sellerNr to validate
	 * @param Array 	The (remaining) request's data
	 */
	protected function sellerNrIsUnique($sellerNr, $data) {
		return (empty($this->filter([
			'market_id' => $data['market_id'],
			'seller_nr' => $sellerNr
		])));
	}

	/**
	 * Registrate a seller for a certain market
	 *
	 * @param string 		E-Mail
	 * @param int 			Market ID
	 * @access public
	 * @throws SellerExistsForMarketException
	 * @throws Exception
	 */
	public function registrate($data) {

		$email = $data['seller_email'];
		$marketId = $data['market_id'];
		if (empty(trim($email)) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			throw new InvalidEmailException("Missing or Invalid email: " . $email);
		}

		// Todo: Check if market is existing and is open for number assignment
		$this->Market = new Market();
		$market = $this->Market->findById($marketId);
		if (empty($market) || !$this->Market->numberAssignmentIsRunning($market)) {
			throw new NumberAssignmetNotRunningException("Number assignment not running");
		}


		// Check if email is already registered for this market
		// Could also check for the hash?
		if ($this->checkSellerExistsForMarket($email, $marketId)) {
			throw new SellerExistsForMarketException("E-Mail already registered for market");
		}

		// To be sure: Check if seller_nr is allocated already
		if (!$this->checkSellerNrIsNotAllocated($data['seller_nr'], $marketId)) {
			throw new SellerNrAlreadyAllocatedException();
		}

		$hash = hash('sha256', sprintf('%s%04u', $email, $marketId));

		// We let exceptions fall throuhgh to controller to handle it there
		$id = $this->save([
			'seller_email' => $email,
			'seller_phone' => $data['seller_phone'],
			'seller_firstname' => $data['seller_firstname'],
			'seller_lastname' => $data['seller_lastname'],
			'seller_nr' => $data['seller_nr'],
			'seller_activation_hash' => $hash,
			'seller_is_activated' => 0,
			'seller_market_id' => $marketId,
			'seller_registration_date' => strftime('%F-%T')
		]);

		// Just to be safe ;-)
		Logger::log(sprintf("Registrating seller: %s, %s <%s> (%s) as seller-nr %u for market #%u, id: %u",
			$data['seller_lastname'],
			$data['seller_firstname'],
			$email,
			$data['seller_phone'],
			$data['seller_nr'],
			$marketId,
			$id
		));

		return $hash;
	}


	public function checkSellerExistsForMarket($email, $marketId) {
		$sellers = $this->filter([
			'seller_email' => $email,
			'seller_market_id' => $marketId
		])->findAll();

		return (!empty($sellers));
	}

	public function checkSellerNrIsNotAllocated($sellerNr, $marketId) {
		$result = $this->filter([
			'seller_nr' => $sellerNr,
			'seller_market_id' => $marketId
		])->findAll();

		return (empty($result));
	}

	/**
	 * Activate a seller by its hash
	 *
	 * @param string 		The activation hash
	 * @throws ActivationFailedException
	 * @throws Exception
	 *
	 * @return \KFE\Seller
	 */
	public function activate($hash) {

		// Get the seller for hash
		$seller = $this->filter([
			'seller_activation_hash' => $hash,
			'seller_is_activated' => 0,
		])->findOne();

		if (empty($seller)) {
			throw new ActivationFailedException();
		}

		$this->save([
			'id' => $seller['id'],
			'seller_hash' => '-DEVALUATED-',
			'seller_is_activated' => 1,
			'seller_activation_datetime' => strftime('%F-%T')
		]);

		// Just to be safe ;-)
		Logger::log(sprintf("Activating seller: %s, %s <%s> as seller-nr %u for market #%u, id: %u",
			$seller['seller_lastname'],
			$seller['seller_firstname'],
			$seller['seller_email'],
			$seller['seller_nr'],
			$seller['seller_market_id'],
			$seller['id']
		));


		// Re-Read the seller's record and return it
		$seller = $this->findById($seller['id']);
		return $seller;
	}


	/**
	 * Check whether a seller is an employee
	 * Employees have seller numbers 300 - 399 by convention

	 * @access public
	 * @param Integer 		The seller's seller nr to check
	 * @return boolean
	 */
	public function isEmployee($sellerNr) {
		return ($sellerNr >= 300 && $sellerNr < 400);
	}



	/**
	 * Authenticate a seller for a given market
	 *
	 * @param int 			The seller nr
	 * @param string 		Seller email
	 * @param int 			Market ID for which the seller is regiostrated (0 =
	 * 						valid for all markets (employee)
	 * @return boolean 	
	 */
	public function authenticate($sellerNr, $sellerEmail, $marketId) {

		// First we check if the seller_nr / email combination is valid
		$query = 
			"SELECT * FROM {$this->tableName} " .
			"WHERE seller_nr = {$sellerNr} " .
			"AND seller_email = '{$sellerEmail}' " .
			"AND seller_is_activated = 1 " .
			"AND (seller_market_id = 0 OR seller_market_id = {$marketId}) " .
			"LIMIT 1";
		// $query = sprintf("SELECT * FROM %s WHERE seller_nr = %u AND seller_email = '%s' AND seller_is_activated = 1 AND (seller_market_id = 0 OR seller_market_id = %u) LIMIT 1",
		// 	$this->tableName,
		// 	$sellerNr,
		// 	$sellerEmail,
		// 	$marketId
		// );
		$result = array_shift($this->query($query));
		if (empty($result)) {
			return false;
		}

		// TODO: The following checks are obsolete after refactoring the query
		// above? They dont harm either, though.

		// seller_market_id == 0 means: Is valid for ALL markets (employees)
		if ($result['seller_market_id'] == 0) {
			return true;
		}

		// Else check that the seller is valid for the given market
		return ($result['seller_market_id'] == $marketId);
	}



	public function login($seller) {

		$this->Session->setMultipleSessionVars([
			'cmt_visitorloggedin' => true,
			'seller_nr' => $seller['seller_nr'],
			'seller_firstname' => $seller['seller_firstname'],
			'seller_lastname' => $seller['seller_lastname'],
			'seller_email' => $seller['seller_email'],
			'seller_market_id' => $seller['seller_market_id']
		]);
		$this->Session->saveSessionVars();

	}

	public function logout() {
		$this->Session->deleteSessionVar('cmt_visitorloggedin');
		$this->Session->deleteSessionVar('seller_nr');
		$this->Session->deleteSessionVar('seller_firstname');
		$this->Session->deleteSessionVar('seller_lastname');
		$this->Session->deleteSessionVar('seller_email');
		$this->Session->deleteSessionVar('seller_market_id');

		$this->Session->saveSessionVars();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function isLoggedIn() {
		return !empty($this->Session->getSessionVar('cmt_visitorloggedin'));
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 */
	public function findByEmailAndHash($email, $hash) {
		return $this->filter([
			'seller_email' => $email,
			'seller_activation_hash' => $hash
		])->findOne();
	}


	/**
	 * Export sellers
	 *
	 * @param Array 		$conditions: Conditions (as passed to filter)
	 * @param Array 		$columns: Array of field names (columns)
	 * @param string 		$type: Type, currently 'csv' and 'ssv' is supported (ssv stands for 'semicolon delimited values')
	 * @throws Exception
	 * @return void
	 */
	public function export($conditions = [], $columns = null, $type = 'csv') {
		$options = ['filename' => 'Erbacher_Kinderflohmarkt_Verkaeuferliste.csv'];
		switch ($type) {
			case 'ssv':
				$options['delimiter'] = ';';
				$this->exportCSV($conditions, $columns, $options);
				break;
			case 'csv':
			default:
				$options['delimiter']= ',';
				$this->exportCSV($conditions, $columns, $options);
				break;
		}
	}

	
	/**
	 * Export to CSV
	 *
	 * @param Array 		Conditions
	 * @param Array 		Columns (field names)
	 * @param Array 		Options (field names)
	 * @throws Exception
	 * @return void
	 */
	protected function exportCSV($conditions = [], $columns = null, $options = []) {
		$defaultOptions = [
			'delimiter' => ',',
			'enclosure' => '"',
			'escapeChar' => '\\',
			'filename' => 'verkaeufer.csv'
		];
		$options = array_merge($defaultOptions, $options);

		$sellers = $this->filter($conditions)->order(['seller_nr' => 'ASC'])->findAll();

		$fields = $this->FieldHandler->getAllFields([
			'tableName' => $this->tableName,
			'getAll' => true
		]);

		// Get all field names from FieldHandler
		if ($columns == null) {
			$columns = [];
			foreach ($fields as $field) {
				$columns[] = $field['cmt_fieldname'];
			}
		}

		// Open a file
		$tempFile = tempnam(PATHTOTMP, '');
		$fh = fopen($tempFile, 'w');

		// Output header
		$aliases = [];
		foreach ($columns as $column) {
			foreach ($fields as $field) {
				if ($field['cmt_fieldname'] == $column) {
					array_push($aliases, $field['cmt_fieldalias']);
					break;
				}
			}
		}
		fputcsv($fh, $aliases, $options['delimiter'], $options['enclosure'], $options['escapeChar']);

		foreach ($sellers as $seller) {
			$fields = [];
			foreach ($columns as $column) {
				// $content .= sprintf("\"%s\",", $seller[$column]);
				array_push($fields, $seller[$column]);
			}
			// $content .= "\n";
			fputcsv($fh, $fields, $options['delimiter'], $options['enclosure'], $options['escapeChar']);
		}

		fclose($fh);

		$this->FileHandler->handleDownload([
			'downloadFile' => $tempFile,
			'downloadFileAlias' => $options['filename'],
			'deleteFile' => true
		]);
	}

	/**
	 * undocumented function
	 *
	 * @param Array 	$conditions
	 * @param int 		Market ID
	 * @param Array 	Options
	 * 					- `skipEmpty`: Dont print sellers wothout sales
	 * @return void
	 */
	public function generateSumsheets($conditions, $marketId, $options) {
		$options = array_merge([
			'skipEmpty' => false
		], $options);

		if (!$this->Market) {
			$this->Market = new Market();
		}

		if (!$this->Cart) {
			$this->Cart = new Cart();
		}

		if ($marketId == 0) {
			$market = $this->Market->getNextUpcoming();
		}
		else {
			$market = $this->Market->findById($marketId);
		}

		$sellers = $this->findByMarket($marketId);
		// $sellers = [ $sellers[192] ];

		foreach ($sellers as &$seller) {

			// Get the seller's sales (items)
			$seller['sales'] = $this->Item->filter([
				'item_seller_id' => $seller['id']
			])->findAll();

			$seller['salesTotal'] = 0;
			foreach ($seller['sales'] as $item) {
				$seller['salesTotal'] += $item['item_value'];
			}
			$seller['salesTotalEuro'] = $seller['salesTotal'] / 100;
			$seller['salesTotalEuroFmt'] = sprintf('%.2f', $seller['salesTotalEuro']);
			$seller['discountPercent'] = ($this->isEmployee($seller['seller_nr']) ? 0 : 20);
			$seller['discountValue'] = $seller['salesTotal'] * $seller['discountPercent'] / 100;
			$seller['discountValueEuro'] = $seller['discountValue'] / 100;
			$seller['discountValueEuroFmt'] = sprintf('%.2f', $seller['discountValueEuro']);
			$seller['grossValue'] = $seller['salesTotal'] - $seller['discountValue'];
			$seller['grossValueEuro'] = $seller['grossValue'] / 100;
			$seller['grossValueEuroFmt'] = sprintf('%.2f', $seller['grossValueEuro']);
			$seller['itemsCount'] = count($seller['sales']);
		}

		// $sellers = $this->filter($conditions)->order(['seller_nr' => 'ASC'])->findAll();
		// foreach ($sellers as &$seller) {
		// 	$seller = $this->calculateTotals($seller);
		// }
		// echo '<pre>'; var_dump($sellers); echo '</pre>'; die();
		$filename = sprintf('Auswertung-%s.pdf', strftime('%Y-%m-%d-%H%M'));
		$this->SellerSumsheet = new SellerSumsheet($sellers, $market, $filename);
		$this->SellerSumsheet->create($options['skipEmpty']);
	}


	/**
	 * Deprecated
	 */
	public function calculateTotals($seller) {
		$seller['sales'] = $this->getSales($seller['id']);
		$seller['salesTotal'] = 0;
		foreach ($seller['sales'] as $item) {
			$seller['salesTotal'] += $item['value'];
		}
		$seller['salesTotalEuro'] = $seller['salesTotal'] / 100;
		$seller['salesTotalEuroFmt'] = sprintf('%.2f', $seller['salesTotalEuro']);
		$seller['discountPercent'] = ($this->isEmployee($seller['seller_nr']) ? 0 : 20);
		$seller['discountValue'] = $seller['salesTotal'] * $seller['discountPercent'] / 100;
		$seller['discountValueEuro'] = $seller['discountValue'] / 100;
		$seller['discountValueEuroFmt'] = sprintf('%.2f', $seller['discountValueEuro']);
		$seller['grossValue'] = $seller['salesTotal'] - $seller['discountValue'];
		$seller['grossValueEuro'] = $seller['grossValue'] / 100;
		$seller['grossValueEuroFmt'] = sprintf('%.2f', $seller['grossValueEuro']);
		$seller['itemsCount'] = count($seller['sales']);

		return $seller;
	}


	/**
	 * Deprecated
	 *
	 * Get a seller's sale
	 *
	 * @param int 		Seller ID
	 * @return Array 	Array of sales (cart items)
	 */
	public function getSales($sellerId) {


		$seller = $this->findById($sellerId);
		if (!$this->Market) {
			$this->Market = new Market();
		}

		// Get all carts from the seller's market 
		if ($this->isEmployee($seller['seller_nr'])) {
			$market = $this->Market->getNextUpcoming();
			$marketId = $market['id'];
		}
		else {
			$marketId = $seller['seller_market_id'];
		}

		$carts = $this->Cart->filter([
			'cart_market_id' => $marketId
		])->findAll();

		$sales = [];
		foreach ($carts as $cart) {
			$items = (array)json_decode($cart['cart_items'], true);
			foreach ($items as $item) {
				if ($item['sellerNr'] == $seller['seller_nr']) {
					$item['valueEuro'] = $item['value'] / 100;
					$item['valueEuroFmt'] = sprintf('%.2f', ($item['value'] / 100));
					$item['dateTimeFmt'] = strftime('%d.%m.%Y %T', $item['ts'] / 1000);
					array_push($sales, $item);
				}
			}
		}

		return $sales;
	}


	/**
	 * Build a SQL query from the search params of AppShowtable
	 *
	 * @param Array 			search params (as stored in Session)
	 * @return string 			Query
	 */
	public function buildQueryFromSearchParams($params) {
		$query = sprintf("SELECT * FROM %s WHERE ", $this->tableName);
		if (!empty($params['search_field'][1])) {

			$query .= "(";

			foreach ($params['search_field'] as $i => $field) {
				if (empty($field)) {
					break;
				}
				$query .= sprintf('%s %s %s', $field, $params['search_criteria'][$i], $params['search_value'][$i]);
				if (!empty($params['search_field'][$i + 1])) {
					$query .= sprintf(' %s ', strtoupper($params['search_link'][$i]));
				}
			}

			$query .= " ) AND ";
		}

		$query .= sprintf('seller_market_id = %u ', $this->Session->getSessionVar('sellerMarketId'));

		if (!empty($params['sort_by'][1])) {
			$query .= ' ORDER BY ';
		}

		foreach ($params['sort_by'] as $i => $sortBy) {
			if (!empty($params['sort_by'][$i])) {
				$query .= sprintf('%s', $params['sort_by'][$i]);
			}
			if (!empty($params['sort_dir'][$i])) {
				$query .= sprintf(' %s', strtoupper($params['sort_dir'][$i]));
			}
			if (!empty($params['sort_by'][$i + 1])) {
				$query .= ',';
			}
		}

		return $query;
	}



	public function sendActivationMail($email, $activationPageId) {
		$seller = $this->filter(['seller_email' => $email])->findOne();

		if (empty($seller)) {
			throw new Exception('Seller is empty');
		}
		if ((bool)$seller['seller_is_activated'] == true) {
			throw new Exception('Seller is already activated');
		}
		if (!preg_match('/^[a-fA-F0-9]{64}$/', $seller['seller_activation_hash'])) {
			throw new Exception('Invalid hash');
		}
		if (!filter_var($seller['seller_email'], FILTER_VALIDATE_EMAIL)) {
			throw new Exception('Invalid email: ' . $seller['seller_email']);
		}
		// if (
		// 		empty($seller) ||
		// 		(bool)$seller['seller_is_activated'] == true ||
		// 		!preg_match('/[a-fA-F0-9]/', $seller['seller_activation_hash']) ||
		// 		!filter_var($seller['seller_email'], FILTER_VALIDATE_EMAIL)
		// 	) {
		// 		throw new Exception("Cannot send activation link to seller.");
		// }

		$activationUrl = sprintf('http%s://%s%s%s?action=activate&hash=%s',
			!empty($_SERVER['HTTPS']) ? 's' : '', 
			$_SERVER['SERVER_NAME'],
			$this->CmtPage->makePageFilePath($activationPageId),
			$this->CmtPage->makePageFileName($activationPageId),
			$seller['seller_activation_hash']
		);
		
		$this->Market = new Market();
		$market = $this->Market->findById($seller['seller_market_id']);
		$this->Parser->setMultipleParserVars(array_merge( $market, $seller));
		$this->Parser->setParserVar('activationUrl', $activationUrl);

		$text = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/sellers/activation_mail.txt.tpl');
		$mailContent = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/sellers/activation_mail.html.tpl');
		$this->Parser->setParserVar('mailContent', $mailContent);
		$html = $this->Parser->parseTemplate(PATHTOWEBROOT . 'templates/email.tpl');

		$check = $this->Mail->send([
			'recipient' => $seller['seller_email'],
			'subject' => 'Kinderflohmarkt Erbach: Registrierung abschliessen',
			'text' => $text,
			'html' => $html
		]);

		Logger::log(sprintf("Sending activation mail to <%s>: %s", $email, $check ? "success" : "failed"));

		if ($check !== true) {
			echo '<pre>'; var_dump($this->Mail->getErrorMessage()); echo '</pre>'; die();
		}
	}



	/**
	 * Return all employees (seller_nr betw. 300 - 400 and market #0)
	 *
	 * @return Array
	 * @access public
	 */
	public function getEmployees() {
		$employees = $this->filter([
			'seller_nr >=' => 300,
			'seller_nr <' => 400,
			'seller_market_id' => 0
		])->findAll();
		return $employees;
	}


	/**
	 * Get all employees from an array of IDs
	 *
	 * @param Array 		Array of IDs
	 * @return Array 		data
	 * @access public
	 */
	public function findAllByIds($ids) {
		$query = sprintf("SELECT * FROM kfe_sellers WHERE id IN (%s)", join(',', $ids));
		return $this->query($query);
	}
}
?>

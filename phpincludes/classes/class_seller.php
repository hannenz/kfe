<?php
namespace KFE;

use Contentomat\Model;
use Contentomat\Mail;
use Contentomat\Contentomat;
use \Exception;

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



	public function init() {
		$this->Mail = new Mail();
		$this->Cmt = Contentomat::getContentomat();
		$this->Session = $this->Cmt->getSession();
		$this->tableName = 'kfe_sellers';
		$this->setValidationRules([
			'seller_firstname' => ['not-empty' => '/^.+$/'],
			'seller_lastname' => ['not-empty' => '/^.+$/'],
			'seller_email' => [ 'valid-email' =>  '/^.+@.+\..+$/' ],
			'seller_email_confirm' => [ 'match' => 'matchEmails' ],
			'agree' => ['agree' => '/^agreed$/'],
			// 'seller_nr' => ['seller-nr-is-unique' => 'sellerNrIsUnique']
		]);
	}


	/**
	 * Get a seller by its seller nr
	 *
	 * @param int
	 * @return Array
	 */
	public function findBySellerNr($sellerNr) {
		return $this->filter(['seller_nr' => $sellerNr])->findOne();
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
			throw new NumberAssignmetNotRunningException();
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
		$this->save([
			'seller_email' => $email,
			'seller_firstname' => $data['seller_firstname'],
			'seller_lastname' => $data['seller_lastname'],
			'seller_nr' => $data['seller_nr'],
			'seller_activation_hash' => $hash,
			'seller_is_activated' => 0,
			'seller_market_id' => $marketId,
			'seller_registration_date' => strftime('%F-%T')
		]);
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
	 * @return void
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
		$result = $this->filter([
			'seller_nr' => $sellerNr,
			'seller_email' => $sellerEmail
		])->findOne();
		if (empty($result)) {
			return false;
		}

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
	
	
}
?>

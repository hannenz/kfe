<?php
namespace KFE;

use Contentomat\Model;
use Contentomat\Mail;
use KFE\Market;
use \Exception;

class RegistrationValidationException extends Exception { }
class SellerExistsForMarketException extends Exception { }
class ActivationFailedException extends Exception { }
class InvalidEmailException extends Exception { }
class EmailsDontMatchException extends Exception { }
class SellerNrAlreadyAllocatedException { }

class Seller extends Model {


	/**
	 * @var Contentomat\Mail
	 */
	protected $Mail;

	/**
	 * @var \KFE\Market
	 */
	protected $Market;


	public function init() {
		$this->Mail = new Mail();
		$this->Market = new Market();
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
	 * undocumented function
	 *
	 * @return void
	 */
	public function findBySellerNr($param)
	{
		return null;
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

		// Todo: Check if market is existing!


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
	 * Get avaliable seller numbers for a given market
	 *
	 * @access public
	 * @param Integer  		The market's id to check
	 * @return Array 		An array of available numbers
	 */
	public function getAvailableNumbers($marketId) {
		$availableNumbers = [];
		for ($i = 1; $i <= 110; $i++) {
			$availableNumbers[] = $i;
		}

		$allocatedNumbers = [];
		$results = $this
		->fields([
			'seller_nr'
		])
		->filter([
			'seller_market_id' => $marketId
		])
		->findAll();
		foreach ($results as $result) {
			$allocatedNumbers[] = (int)$result['seller_nr'];
		}
		return (array_diff($availableNumbers, $allocatedNumbers));
	}
}
?>

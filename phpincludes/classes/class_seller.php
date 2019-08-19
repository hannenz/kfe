<?php
namespace KFE;

use KFE\Model;
use Contentomat\Mail;
use \Exception;

class SellerExistsForMarketException extends Exception { }
class ActivationFailedException extends Exception { }
class InvalidEmailException extends Exception { }
class EmailsDontMatchException extends Exception { }
class SellerNrAlreadyAllocatedException { }

class Seller extends Model {


	/**
	 * @var Contentomat\Mail
	 */
	public $Mail;


	public function init() {
		$this->Mail = new Mail();
		$this->tableName = 'kfe_sellers';
		$this->setValidationRules([
			'seller_firstname' => ['not-empty' => '/^.+$/'],
			'seller_lastname' => ['not-empty' => '/^.+$/'],
			'seller_email' => [ 'valid-email' =>  '/^.+@.+\..+$/' ],
			'seller_email_confirm' => [ 'match' => 'matchEmails' ],
			'agree' => ['agree' => '/^agreed$/']
		]);
	}

	protected static function matchEmails($email) {
		return ($email == $_POST['seller_email']);
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

		// Check if email is already registered for this market
		// Could also check for the hash?
		if ($this->checkSellerExistsForMarket($email, $marketId)) {
			throw new SellerExistsForMarketException("E-Mail already registered for market");
		}

		// To be sure: Check if seller_nr is allocated already
		if (!$this->checkSellerNrIsNotAllocated($data['seller_nr'], $data['market_id'])) {
			die ("Seller nr is already allocated");
			throw new SellerNrAlreadyAllocatedException();
		}

		$hash = hash('sha256', sprintf('%s%04u', $email, $marketId));

		$query = sprintf("INSERT INTO %s SET %s",
			$this->tableName,
			$this->db->makeSetQuery([
				'seller_email' => $email,
				'seller_firstname' => $data['seller_firstname'],
				'seller_lastname' => $data['seller_lastname'],
				'seller_nr' => $data['seller_nr'],
				'seller_activation_hash' => $hash,
				'seller_is_activated' => 0,
				'seller_market_id' => $marketId,
				'seller_registration_date' => strftime('%F-%T')
			])
		);

		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " . $query);
		}

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
			// 'seller_registration_datetime >= ' . strftime('%F-%T', strtotime('-48 hours'))
		])->findOne();

		if (empty($seller)) {
			throw new ActivationFailedException();
		}

		$query = sprintf("UPDATE %s SET %s WHERE id=%u",
			$this->tableName,
			$this->db->makeSetQuery([
				'seller_is_activated' => 1,
				'seller_activation_datetime' => strftime('%F-%T')
			]),
			$seller['id']
		);
		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " .$query);
		}

		// Re-Read the seller's record and return it
		return $this->findById($seller['id']);
	}

	public function getAvailableNumbers($marketId) {
		$availableNumbers = [];
		for ($i = 1; $i <= 110; $i++) {
			$availableNumbers[] = $i;
		}

		$allocatedNumbers = [];
		$results = $this->fields(['seller_nr'])->filter([
			'seller_market_id' => $marketId
		])->findAll();
		foreach ($results as $result) {
			$allocatedNumbers[] = (int)$result['seller_nr'];
		}
		return (array_diff($availableNumbers, $allocatedNumbers));
	}
}
?>

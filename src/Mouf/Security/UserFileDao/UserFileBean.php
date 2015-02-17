<?php
namespace Mouf\Security\UserFileDao;

use Mouf\Security\UserService\UserInterface;

/**
 * This represents a User the way it is stored by the UserFileDao.
 * Basically, it has a login, password (encoded in sha1), and some options.
 * 
 * @author David
 *
 */
class UserFileBean implements UserInterface {
	
	public $login;
	public $password;
	public $options;
	
	public function __construct($login = null, $password = null, $options = null) {
		$this->login = $login;
		$this->password = $password;
		$this->options = $options;
	}
	
	/**
	 * Returns the ID for the current user.
	 *
	 * @return string
	 */
	public function getId() {
		return $this->login;
	}
	
	/**
	 * Returns the login for the current user.
	 *
	 * @return string
	 */
	public function getLogin() {
		return $this->login;
	}
	
	/**
	 * Returns the password for the current user.
	 *
	 * @return string
	 */
	public function getEncodedPassword() {
		return $this->password;
	}

	/**
	 * Returns the options for the current user.
	 *
	 * @return string
	 */
	public function getOptions() {
		return $this->options;
	}
	
	/**
	 * Sets the login of the user.
	 * 
	 * @param string $login
	 */
	public function setLogin($login) {
		$this->login = $login;	
	}
	
	/**
	 * Sets the password for this user.
	 * You pass a clear text password to the function but it will be directly hashed.
	 * 
	 * @param string $password
	 */
	public function setClearTextPassword($password) {
		$this->password = password_hash($password, PASSWORD_DEFAULT);
	}
	
	/**
	 * Sets the options of the user.
	 * This can be anything as long as it is serializable.
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}
	
	
}
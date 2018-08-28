<?php
namespace Mouf\Security\UserFileDao;

use Mouf\Security\UserService\UserDaoInterface;
use Mouf\Security\UserService\UserInterface;

/**
 * The UserFileDao is an implementation of a UserDao, that stores the application users in a simple PHP file.
 * It can be used together with the UserService to provide easy access management to an application.
 * Since the users are stored in a PHP file, this very special DAO does not require a database!
 * 
 * The DAO can also be used to add/edit/remove users from the file.
 * The users file is dynamically written, but can also be manually edited.
 * 
 * @author David
 */
class UserFileDao implements UserDaoInterface {

    /**
	 * The path to the file containing the list of users and passwords.
	 * The path is relative to the ROOT_PATH. It should not start with a "/".
	 * It is a PHP file, therefore, it is a good idea if it has the ".php" extension.
	 *
	 * @var string
	 */
	private $userFile;

	/**
	 * True if the file has been loaded, false otherwise.
	 * @var boolean
	 */
	private $isFileLoaded;
	
	/**
	 * An array of UserFileBean.
	 * 
	 * @var array<UserFileBean>
	 */
	private $usersAsObjects;

	private $rootPath;

	/**
	 * @param string $userFile The path to the file containing the list of users and passwords. The path is relative to the ROOT_PATH. It should not start with a "/". It is a PHP file, therefore, it is a good idea if it has the ".php" extension.
	 */
	public function __construct(string $userFile) {
		$this->userFile = $userFile;
	}

	public function getUserFilePath(): string {
		return $this->getRootPath().$this->userFile;
	}

	private function getRootPath(): string {
		if ($this->rootPath === null) {
			$this->rootPath = dirname(__DIR__, 7).'/';
		}
		return $this->rootPath;
	}

	/**
	 * Returns a user from its login and its password, or null if the login or credentials are false.
	 *
	 * @param string $login
	 * @param string $password
	 * @return UserFileBean
	 */
	public function getUserByCredentials(string $login, string $password): ?UserInterface {
		$this->load();

		/** @var UserFileBean $userBean */
		$userBean = $this->getUserByLogin($login);
		if ($userBean != null && password_verify($password, $userBean->getEncodedPassword())) {
			return $userBean;
		} else {
			return null;
		}
	}

	/**
	 * Returns a user from its token.
	 *
	 * @param string $token
	 * @return UserInterface
	 */
	public function getUserByToken(string $token): ?UserInterface {
		throw new UserFileDaoException("getUserByToken is not implemented for the UserFileDao.");
	}
	
	/**
	 * Discards a token.
	 *
	 * @param string $token
	 */
	public function discardToken(string $token): void {
		throw new UserFileDaoException("discardToken is not implemented for the UserFileDao.");
	}
	
	/**
	 * Returns a user from its ID
	 *
	 * @param string|int $id
	 * @return UserInterface
	 */
	public function getUserById($id): ?UserInterface {
		// We don't have ID's so ID=login in the UserFileDao.
		return $this->getUserByLogin($id);
	}
	
	/**
	 * Returns a user from its login
	 *
	 * @param string $login
	 * @return UserFileBean
	 */
	public function getUserByLogin(string $login): ?UserInterface {
		$this->load();
		
		if (isset($this->usersAsObjects[$login])) {
			return $this->usersAsObjects[$login];
		}
		return null;
	}
	
	/**
	 * Loads the file containing the users.
	 * Note: you don't have to call the function manually. It will be called for you.
	 * 
	 */	
	private function load(): void {
		if ($this->isFileLoaded) {
			return;
		}
		if (!$this->isUserFileAvailable()) {
			throw new UserFileDaoException("Could not load the file containing the users: '".$this->getUserFilePath()."' does not exist or is not writable.");
		}
		
		include $this->getUserFilePath();
		
		foreach ($users as $login=>$user) {
			$this->usersAsObjects[$login] = new UserFileBean($login, $user['password'], $user['options']);
		}
	}
	
	/**
	 * Checks whether the file containing the users is available or not.
	 * @return bool Returns true on success, false if the file is missing or not readable.
	 */
	public function isUserFileAvailable(): bool {
		return is_readable($this->getUserFilePath());
	}
	
	/**
	 * Writes the file containing the user list.
	 */
	public function write(): void {
		if (!is_writable(dirname($this->getUserFilePath())) || (file_exists($this->getUserFilePath()) && !is_writable($this->getUserFilePath()))) {
			throw new UserFileDaoException("Error, unable to write file ".$this->getUserFilePath());
		}

		$users = array();
		foreach ($this->usersAsObjects as $login=>$userBean) {
			/* @var $userBean UserFileBean  */
			$users[$login] = [
				'password' => $userBean->getEncodedPassword(),
				'options' => $userBean->getOptions()
			];
		}

		$fp = fopen($this->getUserFilePath(), "w");
		fwrite($fp, "<?php\n");
		fwrite($fp, "/**\n");
		fwrite($fp, " * This is a file automatically generated by the Mouf framework. Do not modify its structure, as it could be overwritten.\n");
		fwrite($fp, " * You can however safely add new users, modify users or change passwords.\n");
		fwrite($fp, " */\n");
		fwrite($fp, "\n");
		fwrite($fp, "\$users = ".var_export($users, true));
		fwrite($fp, ";\n");
		fclose($fp);
	}
	
	/**
	 * Registers a new user in the Dao.
	 * You must call the "write" method for this user to be saved.
	 * 
	 * @param UserFileBean $userFileBean
	 */
	public function registerUser(UserFileBean $userFileBean): void {
		$this->usersAsObjects[$userFileBean->getLogin()] = $userFileBean;
	}
	
	/**
	 * Removes a user from the Dao.
	 * You must call the "write" method for this user to be definitely removed.
	 *
	 * @param string $login
	 */
	public function removeUser(string $login): void {
		unset($this->usersAsObjects[$login]);
	}	
}

<?php

namespace Lvlapc\Authentication\TokenAdapter;

use Closure;
use Lvlapc\Authentication\Model\CookieToken;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\AuthenticationInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Cookie
 *
 * @package Backend\Library\Authentication\TokenEngine
 */
class Cookie extends Component implements AuthenticationInterface
{
	/**
	 * @var string
	 */
	protected $cookieName;

	/**
	 * @var string
	 */
	protected $requestParameter;

	/**
	 * @var bool
	 */
	protected $enabledAlways = false;

	/**
	 * @var Closure
	 */
	protected $userProvider;

	/**
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * Cookie constructor.
	 *
	 * @param Closure $userProvider
	 * @param array $options
	 */
	public function __construct(Closure $userProvider, array $options = [])
	{
		$this->userProvider     = $userProvider;
		$this->cookieName       = $options['name'] ?? 'ct';
		$this->requestParameter = $options['requestParameter'] ?? 'rm';
		$this->enabledAlways    = !empty($options['enabledAlways']);

	}

	/**
	 * Saves token to provided engine
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function signIn(?UserInterface $user): bool
	{
		if (!$this->enabled()) {
			return true;
		}

		if ($user === null) {
			return false;
		}

		if ($this->isSigned()) {
			return true;
		}

		$token = $this->generateToken($user);

		$this->cookies->set($this->cookieName, $token);

		$cookieToken = new CookieToken([
			'token'   => $token,
			'user_id' => $user->getId(),
		]);

		if (!$cookieToken->create()) {
			$this->signOut();

			return false;
		}

		return true;
	}

	/**
	 * Removes token from provided engine
	 */
	public function signOut(): void
	{
		$this->modelsManager->executeQuery(sprintf('DELETE FROM %s WHERE token = :token:', CookieToken::class), [
			'token' => $this->getToken(),
		]);

		$this->cookies->delete($this->cookieName);
	}

	/**
	 * Tries to check if token have been set
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		$user = $this->getUser();

		return $user !== null;
	}

	/**
	 * Retrieves user with UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		if (!$this->cookies->has($this->cookieName)) {
			return null;
		}

		$cookieToken = CookieToken::findFirst([
			'conditions' => 'token = :token:',
			'bind'       => [
				'token' => $this->getToken(),
			],
		]);

		if (empty($cookieToken)) {
			$this->signOut();

			return null;
		}

		$userId = (int)$cookieToken->getUserId();

		if (empty($userId)) {
			$this->signOut();

			return null;
		}

		$user = call_user_func($this->userProvider, $userId);

		if ($user === null) {
			$this->signOut();

			return null;
		}

		if ($this->getToken() !== $this->generateToken($user)) {
			$this->signOut();

			return null;
		}

		return $user;
	}

	protected function enabled(): bool
	{
		return $this->enabledAlways ||
			in_array($this->request->get($this->requestParameter, null), ['on', '1', 1, true], true);
	}

	protected function generateToken(UserInterface $user): string
	{
		return sha1($user->getEmail() . $user->getPassword() . $this->request->getUserAgent());
	}

	protected function getToken(): string
	{
		return $this->cookies->get($this->cookieName)->getValue('string', '_');
	}
}
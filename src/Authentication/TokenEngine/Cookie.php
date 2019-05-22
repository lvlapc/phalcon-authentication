<?php

namespace Lvlapc\Authentication\TokenEngine;

use Backend\Models\Sql\TokenCookie;
use Lvlapc\Authentication\TokenEngineInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Cookie
 *
 * @package Backend\Library\Authentication\TokenEngine
 */
class Cookie extends Component implements TokenEngineInterface
{
	/**
	 * @var string
	 */
	protected $cookieName;

	/**
	 * @var string
	 */
	protected $rememberMeParam;

	/**
	 * @var bool
	 */
	protected $enabledAlways = false;

	/**
	 * @var \Closure
	 */
	protected $userProvider;

	/**
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * Cookie constructor.
	 *
	 * @param \Closure $userProvider
	 * @param string   $cookieName
	 * @param string   $rememberMeParam
	 * @param bool     $enabledAlways
	 */
	public function __construct(
		\Closure $userProvider,
		string $cookieName = 'ct',
		string $rememberMeParam = 'rm',
		bool $enabledAlways = false
	) {
		$this->userProvider    = $userProvider;
		$this->cookieName      = $cookieName;
		$this->rememberMeParam = $rememberMeParam;
		$this->enabledAlways   = $enabledAlways;
	}

	/**
	 * Saves token to provided engine
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function create(UserInterface $user): bool
	{
		if (!$this->enabled()) {
			return true;
		}

		$token = $this->generateToken($user);

		$this->cookies->set($this->cookieName, $token);

		if (TokenCookie::count([
			'conditions' => 'token = :token: AND user_id = :user_id:',
			'bind'       => [
				'token'   => $token,
				'user_id' => $user->getId(),
			],
		])) {
			return true;
		}

		$cookieToken = new TokenCookie([
			'token'   => $token,
			'user_id' => $user->getId(),
		]);

		if (!$cookieToken->create()) {
			$this->remove();

			return false;
		}

		return true;
	}

	/**
	 * Tries to check if token have been set
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		if (!$this->cookies->has($this->cookieName)) {
			return false;
		}

		if ($this->getUser() === null) {
			$this->remove();

			return false;
		}

		if ($this->getToken() !== $this->generateToken($this->getUser())) {
			$this->remove();

			return false;
		}

		return true;
	}

	/**
	 * Removes token from provided engine
	 */
	public function remove(): void
	{
		$this->user = null;

		$this->modelsManager->executeQuery(sprintf('DELETE FROM %s WHERE token = :token:', TokenCookie::class), [
			'token' => $this->getToken(),
		]);

		$this->cookies->delete($this->cookieName);
	}

	/**
	 * Retrieves user with UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		if ($this->user instanceof UserInterface) {
			return $this->user;
		}

		$cookieToken = TokenCookie::findFirst([
			'conditions' => 'token = :token:',
			'bind'       => [
				'token' => $this->getToken(),
			],
		]);

		if (empty($cookieToken)) {
			$this->remove();

			return null;
		}

		$this->user = call_user_func($this->userProvider, (int)$cookieToken->getUserId());

		return $this->user;
	}

	protected function enabled(): bool
	{
		return $this->enabledAlways ||
			in_array($this->request->get($this->rememberMeParam, null), ['on', '1', 1, true], true);
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
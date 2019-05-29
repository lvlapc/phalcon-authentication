<?php

namespace Lvlapc\Authentication\Adapter;

use Closure;
use Lvlapc\Authentication\Adapter\Cookie\Storage;
use Lvlapc\Authentication\AdapterInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\User\Component;

/**
 * Class Cookie
 * @package Lvlapc\Authentication\Adapter
 */
class Cookie extends Component implements AdapterInterface
{
	/**
	 * @var Storage
	 */
	protected $storage;

	/**
	 * @var Closure
	 */
	protected $tokenGenerator;

	/**
	 * @var
	 */
	protected $options;

	/**
	 * @var bool
	 */
	protected $eventSignedRequestFired = false;

	/**
	 * Cookie constructor.
	 *
	 * @param Storage $storage
	 * @param array   $options
	 */
	public function __construct(Storage $storage, array $options = [])
	{
		$this->storage = $storage;

		$this->options = array_merge([
			'cookieName'       => 'ct',
			'lifetime'         => 604800,
			'requestParameter' => 'rm',
			'enabledAlways'    => false,
			'enabledValues'    => ['on', '1', 1, true]
		], $options);
	}

	/**
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function signIn(UserInterface $user): bool
	{
		if (!$this->enabled()) {
			return true;
		}

		if ($this->isSigned()) {
			$this->signOut();
		}

		$token = $this->generateToken($user);

		if (!$this->storage->save($token, $user)) {
			return false;
		}

		$this->cookies->set($this->options['cookieName'], $token);

		return true;
	}

	/**
	 * Clears authentication data
	 */
	public function signOut(): void
	{
		$this->storage->remove($this->getToken());

		$this->cookies->delete($this->options['cookieName']);
	}

	/**
	 * Is user authenticated
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		if (!$this->cookies->has($this->options['cookieName'])) {
			return false;
		}

		$user = $this->storage->getUser($this->getToken());

		if ($user === null) {
			$this->signOut();

			return false;
		}

		if ($this->getToken() !== $this->generateToken($user)) {
			$this->signOut();

			return false;
		}

		if ($this->eventSignedRequestFired === false && $this->_eventsManager instanceof Manager) {
			$this->_eventsManager->fire('authenticationAdapterCookie:isSignedRequest', $this, $user);

			$this->eventSignedRequestFired = true;
		}

		return true;
	}

	/**
	 * Retrieves user
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		if (!$this->isSigned()) {
			return null;
		}

		return $this->storage->getUser($this->getToken());
	}

	protected function enabled(): bool
	{
		$requestParameter = $this->request->get($this->options['requestParameter'], null);
		$enabledValues    = $this->options['enabledValues'];

		return $this->options['enabledAlways'] || in_array($requestParameter, $enabledValues, true);
	}

	protected function generateToken(UserInterface $user): string
	{
		if ($this->tokenGenerator instanceof Closure) {
			return call_user_func($this->tokenGenerator, $user);
		}

		return sha1($user->getPassword() . $this->request->getUserAgent());
	}

	protected function getToken(): string
	{
		return $this->cookies->get($this->options['cookieName'])->getValue('string', '');
	}

	/**
	 * @param Closure $tokenGenerator
	 *
	 * @return Cookie
	 */
	public function setTokenGenerator(Closure $tokenGenerator): Cookie
	{
		$this->tokenGenerator = $tokenGenerator;

		return $this;
	}
}
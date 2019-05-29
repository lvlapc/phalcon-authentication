<?php namespace Tests;

use Codeception\Stub\Expected;
use Lvlapc\Authentication;
use Lvlapc\Authentication\AdapterInterface;

class AuthenticationTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Tests\UnitTester
	 */
	protected $tester;

	protected function _before()
	{
	}

	protected function _after()
	{
	}

	// tests
	public function testAuthenticationSuccesfull()
	{
		$adapter = $this->makeEmpty(AdapterInterface::class, [
			'signIn' => Expected::once(true),
		]);

		$userProvider = $this->makeEmpty(Authentication\UserProviderInterface::class, [
			'getUser' => Expected::once($this->makeEmpty(Authentication\UserInterface::class))
		]);

		$credentialsChecked = $this->makeEmpty(Authentication\CredentialsCheckerInterface::class, [
			'check' => Expected::once(true),
		]);

		$auth = new Authentication($adapter);

		$this->assertEquals(true, $auth->signIn($userProvider, $credentialsChecked));
		$this->assertEquals(true, $auth->isSigned());
	}

	public function testAuthenticationUnsuccesfullIfUserNotFound()
	{
		$adapter = $this->makeEmpty(AdapterInterface::class, [
			'signIn' => Expected::never(),
		]);

		$userProvider = $this->makeEmpty(Authentication\UserProviderInterface::class, [
			'getUser' => Expected::once(null)
		]);

		$credentialsChecked = $this->makeEmpty(Authentication\CredentialsCheckerInterface::class, [
			'check' => Expected::never(),
		]);

		$auth = new Authentication($adapter);

		$this->assertEquals(false, $auth->signIn($userProvider, $credentialsChecked));
		$this->assertEquals(false, $auth->isSigned());
	}

	public function testAuthenticationUnsuccesfullIfBadCredentials()
	{
		$adapter = $this->makeEmpty(AdapterInterface::class, [
			'signIn' => Expected::never(),
		]);

		$userProvider = $this->makeEmpty(Authentication\UserProviderInterface::class, [
			'getUser' => Expected::once($this->makeEmpty(Authentication\UserInterface::class))
		]);

		$credentialsChecked = $this->makeEmpty(Authentication\CredentialsCheckerInterface::class, [
			'check' => Expected::once(false),
		]);

		$auth = new Authentication($adapter);

		$this->assertEquals(false, $auth->signIn($userProvider, $credentialsChecked));
		$this->assertEquals(false, $auth->isSigned());
	}

	public function testSingoutAdapterOnlyIfAuthenticated()
	{
		$adapter = $this->makeEmpty(AdapterInterface::class, [
			'signIn'  => Expected::once(true),
			'signOut' => Expected::once(),
		]);

		$userProvider = $this->makeEmpty(Authentication\UserProviderInterface::class, [
			'getUser' => Expected::once($this->makeEmpty(Authentication\UserInterface::class))
		]);

		$credentialsChecked = $this->makeEmpty(Authentication\CredentialsCheckerInterface::class, [
			'check' => Expected::once(true),
		]);

		$auth = new Authentication($adapter);

		$auth->signOut();

		$this->assertEquals(true, $auth->signIn($userProvider, $credentialsChecked));

		$auth->signOut();
	}

	public function testChecksIfAdapterAuthenticatedOnlyOnce()
	{
		$auth = new Authentication($this->makeEmpty(AdapterInterface::class, [
			'isSigned' => Expected::once(true),
		]));

		$this->assertEquals(true, $auth->isSigned());
		$this->assertEquals(true, $auth->isSigned());

		$auth = new Authentication($this->makeEmpty(AdapterInterface::class, [
			'isSigned' => Expected::once(false),
		]));

		$this->assertEquals(false, $auth->isSigned());
		$this->assertEquals(false, $auth->isSigned());
	}

	public function testIfNotAuthenticatedGetUserReturnsNull()
	{
		$auth = new Authentication($this->makeEmpty(AdapterInterface::class, [
			'isSigned' => Expected::once(false),
		]));

		$this->assertEquals(null, $auth->getUser());
	}

	public function testUserReturnsFromAdapter()
	{
		$user = $this->makeEmpty(Authentication\UserInterface::class);

		$auth   = new Authentication($this->makeEmpty(AdapterInterface::class, [
			'isSigned' => Expected::once(true),
			'getUser'  => Expected::once($user)
		]));

		$this->assertEquals($user, $auth->getUser());
	}
}
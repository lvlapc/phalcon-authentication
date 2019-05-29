<?php namespace Tests;

use Codeception\Stub;
use Lvlapc\Authentication\Adapter\Session;
use Lvlapc\Authentication\AdapterInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Di;

class AdapterSessionTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Tests\UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		$di = new Di();

		Di::setDefault($di);

		//Before each test we are creating fake "session" service
		$di->setShared('session', function () {

			$session = [];

			return Stub::makeEmpty(\Phalcon\Session\Adapter::class, [
				'get' => function ($index, $defaultValue = null) use (&$session) {
					return $session[$index] ?? $defaultValue;
				},
				'set' => function ($index, $value) use (&$session) {
					$session[$index] = $value;
				},
				'has' => function ($index) use (&$session) {
					return isset($session[$index]);
				}
			]);
		});

		//Before each test we are creating fake "request" service
		$di->setShared('request', function () {
			return Stub::makeEmpty(\Phalcon\Http\Request::class, [
				'getUserAgent' => 'Some user agent'
			]);
		});
	}

	protected function _after()
	{
	}

	// Authenticates With User
	public function testAuthenticatesIfUserPassed()
	{
		$user = $this->makeEmpty(UserInterface::class, [
			'getId' => 1,
		]);

		$sessionAdapter = new Session(function () {
		});

		$this->assertEquals(true, $sessionAdapter->signIn($user));
		$this->assertEquals(true, $sessionAdapter->isSigned());
	}

	//Not Authenticated if session has no data
	public function testNotAuthenticatedIfSessionHasNoData()
	{
		$sessionAdapter = new Session(function () {
		});

		$this->assertEquals(false, $sessionAdapter->isSigned());
	}

	public function testNotAuthenticatedIfUserAgentHasChanged()
	{
		$userProvider = function () {
		};

		$sessionAdapter = new Session($userProvider);

		$user = $this->makeEmpty(UserInterface::class, [
			'getId' => 1,
		]);

		$this->assertEquals(true, $sessionAdapter->signIn($user));

		//Simulating new request

		Di::getDefault()->remove('request');
		Di::getDefault()->set('request', function () {
			return Stub::makeEmpty(\Phalcon\Http\Request::class, [
				'getUserAgent' => 'Another user agent'
			]);
		});

		$sessionAdapter = new Session($userProvider);

		$this->assertEquals(false, $sessionAdapter->isSigned());
	}

	//When NotAuthenticated returns null instead of User
	public function testWhenNotAuthenticatedReturnsNullInsteadOfUser()
	{
		$fakeUserProvider = $this->makeEmpty(AdapterInterface::class, [
			'getUser' => Stub\Expected::never()
		]);

		$sessionAdapter = new Session(function () use ($fakeUserProvider) {
			return $fakeUserProvider->getUser();
		});

		$this->assertEquals(false, $sessionAdapter->isSigned());
		$this->assertEquals(null, $sessionAdapter->getUser());
	}
}
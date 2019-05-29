<?php namespace Tests;

use Codeception\Stub;
use Codeception\Stub\Expected;
use Lvlapc\Authentication\Adapter\Cookie;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Di;

class AdapterCookieTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Tests\UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		$di = new Di();

		Di::setDefault($di);

		//Before each test we are creating fake "cookies" service
		$di->setShared('cookies', function () {

			$cookies = [];

			return Stub::makeEmpty(\Phalcon\Http\Response\Cookies::class, [
				'get' => function ($name) use (&$cookies) {
					return Stub::makeEmpty(\Phalcon\Http\Cookie::class, [
						'getValue' => function ($filters = null, $defaultValue = null) use (&$name, &$cookies) {
							return $cookies[$name] ?? $defaultValue;
						}
					]);
				},
				'set' => function ($name, $value) use (&$cookies) {
					$cookies[$name] = $value;
				},
				'has' => function ($name) use (&$cookies) {
					return isset($cookies[$name]);
				}
			]);
		});

		//Before each test we are creating fake "request" service
		$di->setShared('request', function () {
			return Stub::makeEmpty(\Phalcon\Http\Request::class, [
				'get'          => 'on',
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
		$user = $this->makeEmpty(UserInterface::class);

		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'save'    => Expected::once(true),
			'getUser' => Expected::once($user),
		]);

		$cookieAdapter = new Cookie($storage);
		$cookieAdapter->setTokenGenerator(function () {
			return 'token';
		});

		$this->assertEquals(true, $cookieAdapter->signIn($user));
		$this->assertEquals(true, $cookieAdapter->isSigned());
	}

	//Does Not Authenticates If Already Authenticated
	public function testAuthenticatesIfAlreadyAuthenticated()
	{
		$user = $this->makeEmpty(UserInterface::class);

		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'save'    => Expected::exactly(2,true),
			'getUser' => $user,
		]);

		$cookieAdapter = new Cookie($storage);

		$user = $this->makeEmpty(UserInterface::class);

		$this->assertEquals(true, $cookieAdapter->signIn($user));
		$this->assertEquals(true, $cookieAdapter->isSigned());
		$this->assertEquals(true, $cookieAdapter->signIn($user));
	}

	//Not Authenticated if session has no data
	public function testNotAuthenticatedIfCookiesEmpty()
	{
		$storage = $this->makeEmpty(Cookie\Storage::class);

		$cookieAdapter = new Cookie($storage);

		$this->assertEquals(false, $cookieAdapter->isSigned());
	}

	public function testNotAuthenticatedAndSignedOutIfTokenHasChanged()
	{
		$user = $this->makeEmpty(UserInterface::class);

		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'save'    => Expected::once(true),
			'getUser' => Expected::once($user),
		]);

		$cookieAdapter = new Cookie($storage);
		$cookieAdapter->setTokenGenerator(function () {
			return 'token1';
		});

		$this->assertEquals(true, $cookieAdapter->signIn($user));
		$this->assertEquals(true, $cookieAdapter->isSigned());

		//Token changed
		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'getUser' => Expected::once($user),
			'remove'  => Expected::once(),
		]);

		$cookieAdapter = new Cookie($storage);

		$cookieAdapter->setTokenGenerator(function () {
			return 'token2';
		});

		$this->assertEquals(false, $cookieAdapter->isSigned());
	}

	public function testWhenNotAuthenticatedReturnsNullInsteadOfUser()
	{
		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'getUser' => Expected::never(),
		]);

		$cookieAdapter = new Cookie($storage);

		$this->assertEquals(false, $cookieAdapter->isSigned());
		$this->assertEquals(null, $cookieAdapter->getUser());
	}

	public function testWhenAuthenticatedStorageGetUserIsCalling()
	{
		$user = $this->makeEmpty(UserInterface::class);

		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'getUser' => Expected::atLeastOnce($user),
			'save'    => Expected::once(true)
		]);

		$cookieAdapter = new Cookie($storage);
		$cookieAdapter->setTokenGenerator(function () {
			return 'token';
		});

		$this->assertEquals(true, $cookieAdapter->signIn($user));
		$this->assertEquals($user, $cookieAdapter->getUser());
	}

	public function testNotAuthenticatedWhenStorageNotSavedUser()
	{
		$user = $this->makeEmpty(UserInterface::class);

		$storage = $this->makeEmpty(Cookie\Storage::class, [
			'save'    => Expected::once(false)
		]);

		$cookieAdapter = new Cookie($storage);
		$cookieAdapter->setTokenGenerator(function () {
			return 'token';
		});

		$this->assertEquals(false, $cookieAdapter->signIn($user));
	}
}
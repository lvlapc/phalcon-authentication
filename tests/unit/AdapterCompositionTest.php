<?php namespace Tests;

use Codeception\Stub\Expected;
use Lvlapc\Authentication\Exception;
use Lvlapc\Authentication\Adapter\Composition;
use Lvlapc\Authentication\AdapterInterface;
use Lvlapc\Authentication\UserInterface;

class AdapterCompositionTest extends \Codeception\Test\Unit
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

	public function testThrowsExceptionInConstructorIfAdaptersArrayIsEmpty()
	{
		$this->expectException(Exception::class);

		$adapter = new Composition([]);
	}

	public function testAuthenticatesAllGivenAdapters()
	{
		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'signIn' => Expected::once(true)
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'signIn' => Expected::once(true)
			])
		]);

		$this->assertEquals(true, $adapter->signIn($this->makeEmpty(UserInterface::class)));
	}

	public function testDeauthenticatesAllGivenAdapters()
	{
		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'signOut' => Expected::once()
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'signOut' => Expected::once()
			])
		]);

		$adapter->signOut();
	}

	public function testIfOneOfGivenAdaptersNotAuthenticatesCompositionDeauthenticatesAllOfThem()
	{
		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'signIn'  => Expected::once(true),
				'signOut' => Expected::once()
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'signIn'  => Expected::once(false),
				'signOut' => Expected::once()
			])
		]);

		$this->assertEquals(false, $adapter->signIn($this->makeEmpty(UserInterface::class)));
	}

	public function testIfOneOfGivenAdaptersIsAuthenticatedRestOfThemNotChecking()
	{
		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::once(true),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::never(true),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::never(false),
			])
		]);

		$this->assertEquals(true, $adapter->isSigned());
	}

	public function testCompositionFindsFirstAuthenticatedAdapter()
	{
		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::once(false),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::once(false),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'isSigned' => Expected::once(true),
			])
		]);

		$this->assertEquals(true, $adapter->isSigned());
	}

	public function testCompositionReturnsFirstFoundUser()
	{
		$user  = $this->makeEmpty(UserInterface::class);

		$adapter = new Composition([
			$this->makeEmpty(AdapterInterface::class, [
				'getUser' => Expected::once(null),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'getUser' => Expected::once(null),
			]),
			$this->makeEmpty(AdapterInterface::class, [
				'getUser' => Expected::once($user),
			])
		]);

		$this->assertEquals($user, $adapter->getUser());
	}
}
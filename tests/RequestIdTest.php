<?php

use \Leo\Middlewares\RequestId;
use \Leo\Fixtures\DummyRequestHandler;
use \PHPUnit\Framework\TestCase;
use \GuzzleHttp\Psr7;

/**
 * @testdox Leo\Middlewares\RequestId
 */
class RequestIdTest extends TestCase
{
	private const HANDLER_ID_LEN = 4;
	private const ITERATIONS = 10;

	/**
	 * Request ids from request attribute
	 * @var array<string>
	 */
	private array $ids_req = [];

	/**
	 * Request ids from response header
	 * @var array<string>
	 */
	private array $ids_res = [];

	public function setUp():void
	{
		$middleware = new RequestId();
		$request = new Psr7\ServerRequest('GET', '/');
		$handler = new DummyRequestHandler();

		for ($i = 0; $i < self::ITERATIONS; $i++) {
			$response = $middleware->process($request, $handler);
			$this->ids_req[] = $handler->getRequest()->getAttribute('REQUEST_ID');
			$this->ids_res[] = $response->getHeaderLine('X-Request-ID');
		}
	}

	public function testSetRequestAttribute():void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
			$this->assertTrue($this->ids_req[$i] !== null);
	}

	public function testSetResponseHeader():void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
			$this->assertTrue($this->ids_res[$i] !== null);
	}

	/**
	 * @depends testSetRequestAttribute
	 * @depends testSetResponseHeader
	 */
	public function testBothIdsAreSame():void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
			$this->assertSame($this->ids_req[$i], $this->ids_res[$i]);
	}

	/**
	 * @depends testBothIdsAreSame
	 */
	public function testIdsPattern():void
	{
		for ($i = 0; $i < self::ITERATIONS; $i++)
			$this->assertMatchesRegularExpression(
				'/[0-9a-f]+-[0-9a-f]+/',
				$this->ids_req[$i]
			);
	}

	/**
	 * @depends testBothIdsAreSame
	 */
	public function testSameHandlerId():void
	{
		$handler_id = $this->getPrefix($this->ids_req[0]);

		foreach ($this->ids_req as $id)
			$this->assertSame($handler_id, $this->getPrefix($id));
	}

	private function getPrefix(string $i):string
	{
		return substr($i, 0, self::HANDLER_ID_LEN);
	}
}

?>

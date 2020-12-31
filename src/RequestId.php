<?php

namespace Leo\Middlewares;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class RequestId implements MiddlewareInterface
{
	private const REQUEST_ID_LEN = 5;
	private const HANDLER_ID_LEN = 2;

	/**
	 * @var string Fixed handler id.
	 */
	private string $handler_id;

	public function __construct()
	{
		$this->handler_id = $this->random(self::HANDLER_ID_LEN);
	}

	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	):ResponseInterface
	{
		$request_id = "{$this->handler_id}-{$this->random(self::REQUEST_ID_LEN)}";

		// Save request id as attribute for requests
		$request = $request
			->withAttribute('REQUEST_ID', $request_id);

		// Append request id to responses as header
		return $handler->handle($request)
			->withHeader('X-Request-Id', $request_id);
	}

	private function random(int $n_bytes):string
	{
		return bin2hex(openssl_random_pseudo_bytes($n_bytes));
	}
}

?>

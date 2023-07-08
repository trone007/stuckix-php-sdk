<?php

namespace Stuckix\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Stuckix\Dsn;
use Stuckix\Model\Event;
use Stuckix\Serializer\PayloadSerializer;

class Http implements Transport
{
	private Client $httpClient;
	private PayloadSerializer $payloadSerializer;

	public function __construct(private Dsn $dsn)
	{
		$this->httpClient = new Client();
		$this->payloadSerializer = new PayloadSerializer();
	}

	public function send(Event $event): PromiseInterface
	{
		try
		{
			/** @var ResponseInterface $response */
			$request = new ServerRequest(
				'POST', $this->dsn->getApiEndpointUrl(), [], $this->payloadSerializer->serialize($event)
			);

			$response = $this->httpClient->sendAsync($request)
				->wait();
		}
		catch (\Throwable $exception)
		{
			return new RejectedPromise(new Response(500));
		}

		if ($response->getStatusCode() === 200)
		{
			return new FulfilledPromise($response);
		}

		return new RejectedPromise($response);
	}

	/**
	 * {@inheritdoc}
	 */
	public function close(?int $timeout = null): PromiseInterface
	{
		return new FulfilledPromise(true);
	}
}
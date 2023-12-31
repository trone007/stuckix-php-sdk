<?php

namespace Stuckix;

use Stuckix\Model\Builder\StacktraceBuilder;
use Stuckix\Model\Event;
use Stuckix\Model\EventException;
use Stuckix\Model\EventIdentifier;
use Stuckix\Model\ExceptionData;
use Stuckix\Model\Level;
use Stuckix\Serializer\PayloadSerializer;
use Stuckix\Transport\Http;

class Client
{
	private Http $transport;
	private StacktraceBuilder $stacktraceBuilder;
	private static ?Client $instance = null;

	private array $extraData;

	public static function get(string $dsn): ?Client
	{
		if (static::$instance)
		{
			return static::$instance;
		}

		static::$instance = new self($dsn);

		return static::$instance;
	}

	private function __construct(string $dsn)
	{
		$this->transport = new Http(Dsn::ofString($dsn));
		$this->stacktraceBuilder = new StacktraceBuilder();
	}

	public function setExtraData(array $extraData)
	{
		$this->extraData = $extraData;
	}

	private function getExtra(string $name)
	{
		return $this->extraData[$name] ?? null;
	}

	public function sendException(\Throwable $exception): ?EventIdentifier
	{
		$exception = new EventException($exception);

		return $this->sendEvent(Event::createEvent(), $exception);
	}

	public function sendEvent(Event $createEvent, ?EventException $exception = null): ?EventIdentifier
	{
		$event = $this->prepareEvent($createEvent, $exception);

		try
		{
			$response = $this->transport->send($event)->wait();
			if ($response->getStatusCode() === 200)
			{
				return $event->getId();
			}
		}
		catch (\Throwable $exception)
		{
		}

		return null;
	}

	private function prepareEvent(Event $event, ?EventException $eventException = null): ?Event
	{
		if ($eventException)
		{
			if ($eventException->exception && empty($event->getExceptions()))
			{
				$this->addThrowableToEvent($event, $eventException);
			}

			if (null !== $eventException->stacktrace && null === $event->getStacktrace())
			{
				$event->setStacktrace($eventException->stacktrace);
			}
		}

		if (empty($event->getStacktrace()) && empty($event->getExceptions()))
		{
			$event->setStacktrace($this->stacktraceBuilder->buildFromBacktrace(
				debug_backtrace(0),
				__FILE__,
				__LINE__ - 3
			));
		}

		if ($this->getExtra('modules'))
		{
			$event->setModules($this->getExtra('modules'));
		}

		if ($this->getExtra('requestContext'))
		{
			$context = $this->getExtra('requestContext');

			$event->setServerName($context['servername'] ?? '');
			$event->setContexts($context);
		}

		// $event->setTags($event->getTags());

		if ($event->getEnvironment())
		{
			$event->setEnvironment('prod');
		}

		return $event;
	}

	private function addThrowableToEvent(Event $event, EventException $eventException): void
	{
		$exception = $eventException->exception;
		if (!$event->getLevel() && $exception instanceof \ErrorException)
		{
			$event->setLevel(Level::fromError($exception->getSeverity()));
		}

		$exceptions = [];

		do
		{
			$exceptions[] = new ExceptionData(
				$exception,
				$this->stacktraceBuilder->buildFromException($exception),
			);
		}
		while ($exception = $eventException->exception->getPrevious());

		$event->setExceptions($exceptions);
	}
}

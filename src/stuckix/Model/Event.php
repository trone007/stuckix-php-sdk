<?php

namespace Stuckix\Model;

final class Event
{
	private EventIdentifier $id;

	private ?float $timestamp;

	private ?Level $level = null;
	private ?string $serverName = null;
	private ?string $message = null;
	private ?string $messageFormatted = null;
	private array $messageParams = [];
	private ?string $environment = null;
	private array $modules = [];
	private array $request = [];
	private array $tags = [];

	private ?string $os = null;
	/**
	 * @var UserData|null The user context data
	 */
	private $user;

	/**
	 * @var array<string, array<string, mixed>> An arbitrary mapping of additional contexts associated to this event
	 */
	private $contexts = [];
	private $extra = [];
	private $fingerprint = [];
	private $breadcrumbs = [];
	private $exceptions = [];

	/**
	 * @var Stacktrace|null The stacktrace that generated this event
	 */
	private $stacktrace;
	private $eventType;

	private function __construct(?EventIdentifier $eventId, EventType $eventType)
	{
		$this->id = $eventId ?? EventIdentifier::generate();
		$this->timestamp = microtime(true);
		$this->eventType = $eventType;
	}

	/**
	 * Creates a new event.
	 *
	 * @param EventIdentifier|null $eventId The ID of the event
	 */
	public static function createEvent(?EventIdentifier $eventId = null): self
	{
		return new self($eventId, EventType::event());
	}

	/**
	 * Sets the error message.
	 *
	 * @param string $message The message
	 * @param string[] $params The parameters to use to format the message
	 * @param string|null $formatted The formatted message
	 */
	public function setMessage(string $message, array $params = [], ?string $formatted = null): void
	{
		$this->message = $message;
		$this->messageParams = $params;
		$this->messageFormatted = $formatted;
	}

	/**
	 * @return string|null
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}

	/**
	 * @return array
	 */
	public function getExceptions(): array
	{
		return $this->exceptions;
	}

	public function setContext(string $name, array $data): self
	{
		if (!empty($data))
		{
			$this->contexts[$name] = $data;
		}

		return $this;
	}

	public function setTag(string $key, string $value): void
	{
		$this->tags[$key] = $value;
	}

	public function removeTag(string $key): void
	{
		unset($this->tags[$key]);
	}

	/**
	 * Gets the breadcrumbs.
	 *
	 * @return Breadcrumb[]
	 */
	public function getBreadcrumbs(): array
	{
		return $this->breadcrumbs;
	}

	/**
	 * Set new breadcrumbs to the event.
	 *
	 * @param Breadcrumb[] $breadcrumbs The breadcrumb array
	 */
	public function setBreadcrumb(array $breadcrumbs): void
	{
		$this->breadcrumbs = $breadcrumbs;
	}

	public function setExceptions(array $exceptions): void
	{
		foreach ($exceptions as $exception)
		{
			if (!$exception instanceof ExceptionData)
			{
				throw new \UnexpectedValueException(
					sprintf(
						'Expected an instance of the "%s" class. Got: "%s".',
						ExceptionData::class,
						get_debug_type($exception)
					)
				);
			}
		}

		$this->exceptions = $exceptions;
	}

	public function getTraceId(): ?string
	{
		$traceId = $this->getContexts()['trace']['trace_id'];

		if (\is_string($traceId) && !empty($traceId))
		{
			return $traceId;
		}

		return null;
	}

	/**
	 * @return \Stuckix\Model\EventIdentifier
	 */
	public function getId(): EventIdentifier
	{
		return $this->id;
	}

	/**
	 * @param \Stuckix\Model\EventIdentifier $id
	 *
	 * @return Event
	 */
	public function setId(EventIdentifier $id): Event
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return float|null
	 */
	public function getTimestamp(): ?float
	{
		return $this->timestamp;
	}

	/**
	 * @param float|null $timestamp
	 *
	 * @return Event
	 */
	public function setTimestamp(?float $timestamp): Event
	{
		$this->timestamp = $timestamp;

		return $this;
	}

	/**
	 * @return \Stuckix\Model\Level|null
	 */
	public function getLevel(): ?Level
	{
		return $this->level;
	}

	/**
	 * @param \Stuckix\Model\Level $level
	 *
	 * @return Event
	 */
	public function setLevel(Level $level): Event
	{
		$this->level = $level;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getServerName()
	{
		return $this->serverName;
	}

	/**
	 * @param mixed $serverName
	 *
	 * @return Event
	 */
	public function setServerName($serverName)
	{
		$this->serverName = $serverName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getMessageFormatted(): ?string
	{
		return $this->messageFormatted;
	}

	/**
	 * @param mixed $messageFormatted
	 *
	 * @return Event
	 */
	public function setMessageFormatted($messageFormatted)
	{
		$this->messageFormatted = $messageFormatted;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMessageParams(): array
	{
		return $this->messageParams;
	}

	/**
	 * @param array $messageParams
	 *
	 * @return Event
	 */
	public function setMessageParams(array $messageParams): Event
	{
		$this->messageParams = $messageParams;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getEnvironment(): ?string
	{
		return $this->environment;
	}

	/**
	 * @param string $environment
	 *
	 * @return Event
	 */
	public function setEnvironment(string $environment)
	{
		$this->environment = $environment;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getModules(): array
	{
		return $this->modules;
	}

	/**
	 * @param array $modules
	 *
	 * @return Event
	 */
	public function setModules(array $modules): Event
	{
		$this->modules = $modules;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequest(): array
	{
		return $this->request;
	}

	/**
	 * @param array $request
	 *
	 * @return Event
	 */
	public function setRequest(array $request): Event
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * @param array $tags
	 *
	 * @return Event
	 */
	public function setTags(array $tags): Event
	{
		$this->tags = $tags;

		return $this;
	}

	/**
	 * @return ?string
	 */
	public function getOs(): ?string
	{
		return $this->os;
	}

	/**
	 * @param string $os
	 *
	 * @return Event
	 */
	public function setOs(string $os): Event
	{
		$this->os = $os;

		return $this;
	}

	/**
	 * @return \Stuckix\Model\UserData|null
	 */
	public function getUser(): ?UserData
	{
		return $this->user;
	}

	/**
	 * @param \Stuckix\Model\UserData|null $user
	 *
	 * @return Event
	 */
	public function setUser(?UserData $user): Event
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return \mixed[][]
	 */
	public function getContexts(): array
	{
		return $this->contexts;
	}

	/**
	 * @param \mixed[][] $contexts
	 *
	 * @return Event
	 */
	public function setContexts(array $contexts): Event
	{
		$this->contexts = $contexts;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getExtra(): array
	{
		return $this->extra;
	}

	/**
	 * @param array $extra
	 *
	 * @return Event
	 */
	public function setExtra(array $extra): Event
	{
		$this->extra = $extra;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFingerprint(): array
	{
		return $this->fingerprint;
	}

	/**
	 * @param array $fingerprint
	 *
	 * @return Event
	 */
	public function setFingerprint(array $fingerprint): Event
	{
		$this->fingerprint = $fingerprint;

		return $this;
	}

	/**
	 * @return \Stuckix\Model\Stacktrace|null
	 */
	public function getStacktrace(): ?Stacktrace
	{
		return $this->stacktrace;
	}

	/**
	 * @param \Stuckix\Model\Stacktrace|null $stacktrace
	 *
	 * @return Event
	 */
	public function setStacktrace(?Stacktrace $stacktrace): Event
	{
		$this->stacktrace = $stacktrace;

		return $this;
	}

	/**
	 * @return \Stuckix\Model\EventType
	 */
	public function getEventType(): EventType
	{
		return $this->eventType;
	}

	/**
	 * @param \Stuckix\Model\EventType $eventType
	 *
	 * @return Event
	 */
	public function setEventType(EventType $eventType): Event
	{
		$this->eventType = $eventType;

		return $this;
	}

}

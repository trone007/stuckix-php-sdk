<?php

namespace Stuckix\Model;

final class ExceptionData
{
	private string $type;
	private string $value;
	private ?Stacktrace $stacktrace;

	public function __construct(
		\Throwable $exception,
		?Stacktrace $stacktrace = null,
	)
	{
		$this->type = \get_class($exception);
		$this->value = $exception->getMessage();
		$this->stacktrace = $stacktrace;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function setValue(string $value): void
	{
		$this->value = $value;
	}

	public function getStacktrace(): ?Stacktrace
	{
		return $this->stacktrace;
	}

	public function setStacktrace(Stacktrace $stacktrace): void
	{
		$this->stacktrace = $stacktrace;
	}
}

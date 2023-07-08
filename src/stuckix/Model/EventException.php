<?php

namespace Stuckix\Model;

final class EventException
{
	public function __construct(
		public \Throwable $exception,
		public ?Stacktrace $stacktrace = null
	)
	{}
}

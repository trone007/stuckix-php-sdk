<?php


namespace Stuckix\Model\Builder;

use Stuckix\Model\Context;
use Stuckix\Model\Stacktrace;
use Stuckix\Serializer\ObjectSerializer;

final class StacktraceBuilder
{
	private ContextBuilder $contextBuilder;

	public function __construct()
	{
		$this->contextBuilder = new ContextBuilder(new ObjectSerializer());
	}

	public function buildFromException(\Throwable $exception): Stacktrace
	{
		return $this->buildFromBacktrace($exception->getTrace(), $exception->getFile(), $exception->getLine());
	}

	public function buildFromBacktrace(array $backtrace, string $file, int $line): Stacktrace
	{
		$contexts = [];

		foreach ($backtrace as $backtraceContextBuilder)
		{
			array_unshift($contexts, $this->contextBuilder->buildFromBacktraceContext($file, $line, $backtraceContextBuilder));

			$file = $backtraceContextBuilder['file'] ?? Context::CONTEXT_FILENAME;
			$line = $backtraceContextBuilder['line'] ?? 0;
		}
		array_unshift($contexts, $this->contextBuilder->buildFromBacktraceContext($file, $line, []));

		return new Stacktrace($contexts);
	}
}

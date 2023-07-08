<?php

namespace Stuckix\Model\Builder;

use Stuckix\Model\Context;
use Stuckix\Serializer\ObjectSerializer;

final class ContextBuilder
{
	public function __construct(
		private ObjectSerializer $serializer
	)
	{
	}

	public function buildFromBacktraceContext(string $file, int $line, array $backtraceContext): Context
	{
		$functionName = null;
		$rawFunctionName = null;

		if (isset($backtraceContext['class']) && isset($backtraceContext['function']))
		{
			$functionName = $backtraceContext['class'];

			if (str_starts_with($functionName, Context::ANONYMOUS_CLASS_PREFIX))
			{
				$functionName = Context::ANONYMOUS_CLASS_PREFIX.$this->removePrefixFromFilePath(
						substr($backtraceContext['class'], \strlen(Context::ANONYMOUS_CLASS_PREFIX))
					);
			}

			$rawFunctionName = sprintf('%s::%s', $backtraceContext['class'], $backtraceContext['function']);
			$functionName = sprintf(
				'%s::%s',
				preg_replace('/(?::\d+\$|0x)[a-fA-F0-9]+$/', '', $functionName),
				$backtraceContext['function']
			);
		}
		elseif (isset($backtraceContext['function']))
		{
			$functionName = $backtraceContext['function'];
		}

		return new Context(
			$functionName,
			basename($file),
			$line,
			$rawFunctionName,
			Context::CONTEXT_FILENAME !== $file ? $file : null,
			$this->getMethodArguments($backtraceContext),
		);
	}

	private function removePrefixFromFilePath(string $filePath): string
	{
		foreach (['\\', '//'] as $prefix)
		{
			if (str_starts_with($filePath, $prefix))
			{
				return mb_substr($filePath, mb_strlen($prefix));
			}
		}

		return $filePath;
	}

	private function getMethodArguments(array $backtraceContext): array
	{
		if (!isset($backtraceContext['function'], $backtraceContext['args']))
		{
			return [];
		}

		$reflectionFunction = null;

		try
		{
			if (isset($backtraceContext['class']))
			{
				if (method_exists($backtraceContext['class'], $backtraceContext['function']))
				{
					$reflectionFunction = new \ReflectionMethod(
						$backtraceContext['class'], $backtraceContext['function']
					);
				}
				elseif (isset($backtraceContext['type']) && '::' === $backtraceContext['type'])
				{
					$reflectionFunction = new \ReflectionMethod($backtraceContext['class'], '__callStatic');
				}
				else
				{
					$reflectionFunction = new \ReflectionMethod($backtraceContext['class'], '__call');
				}
			}
			elseif (!\in_array($backtraceContext['function'], ['{closure}', '__lambda_func'], true) && \function_exists(
					$backtraceContext['function']
				))
			{
				$reflectionFunction = new \ReflectionFunction($backtraceContext['function']);
			}
		}
		catch (\ReflectionException $e)
		{
		}

		$argumentValues = [];

		if ($reflectionFunction)
		{
			$argumentValues = $this->getMethodArgumentValues($reflectionFunction, $backtraceContext['args']);
		}
		else
		{
			foreach ($backtraceContext['args'] as $parameterPosition => $parameterValue)
			{
				$argumentValues['param'.$parameterPosition] = $parameterValue;
			}
		}

		foreach ($argumentValues as $argumentName => $argumentValue)
		{
			$argumentValues[$argumentName] = $this->serializer->serialize($argumentValue);
		}

		return $argumentValues;
	}

	private function getMethodArgumentValues(
		\ReflectionFunctionAbstract $reflectionFunction,
		array $backtraceContextArgs
	): array
	{
		$argumentValues = [];

		foreach ($reflectionFunction->getParameters() as $reflectionParameter)
		{
			$parameterPosition = $reflectionParameter->getPosition();

			if (!isset($backtraceContextArgs[$parameterPosition]))
			{
				continue;
			}

			$argumentValues[$reflectionParameter->getName()] = $backtraceContextArgs[$parameterPosition];
		}

		return $argumentValues;
	}
}

<?php

namespace Stuckix\Serializer;

use Stuckix\Model\Breadcrumb;
use Stuckix\Model\Context;
use Stuckix\Model\Event;
use Stuckix\Model\ExceptionData;

final class PayloadSerializer
{
	public function serialize(Event $event): string
	{
		return $this->serializeAsEvent($event);
	}

	private function serializeAsEvent(Event $event): string
	{
		$result = $this->toArray($event);

		return json_encode($result, JSON_ERROR_NONE);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Event $event): array
	{
		$result = [
			'event_id' => (string)$event->getId(),
			'timestamp' => $event->getTimestamp(),
			'platform' => 'php',
		];

		if (null !== $event->getLevel())
		{
			$result['level'] = (string)$event->getLevel();
		}
		if (null !== $event->getServerName())
		{
			$result['server_name'] = $event->getServerName();
		}

		if (null !== $event->getEnvironment())
		{
			$result['environment'] = $event->getEnvironment();
		}

		if (!empty($event->getFingerprint()))
		{
			$result['fingerprint'] = $event->getFingerprint();
		}

		if (!empty($event->getModules()))
		{
			$result['modules'] = $event->getModules();
		}

		if (!empty($event->getExtra()))
		{
			$result['extra'] = $event->getExtra();
		}

		if (!empty($event->getTags()))
		{
			$result['tags'] = $event->getTags();
		}

		$user = $event->getUser();

		if (null !== $user)
		{
			$result['user'] = array_merge($user->getMetadata(), [
				'id' => $user->getId(),
				'username' => $user->getUsername(),
				'email' => $user->getEmail(),
				'ip_address' => $user->getIpAddress(),
				'segment' => $user->getSegment(),
			]);
		}

		if ($event->getOs())
		{
			$result['contexts']['os'] = $event->getOs();
		}

		if (!empty($event->getContexts()))
		{
			$result['contexts'] = array_merge($result['contexts'] ?? [], $event->getContexts());
		}

		if (!empty($event->getBreadcrumbs()))
		{
			$result['breadcrumbs']['values'] = array_map([$this, 'serializeBreadcrumb'], $event->getBreadcrumbs());
		}

		if (!empty($event->getRequest()))
		{
			$result['request'] = $event->getRequest();
		}

		if (null !== $event->getMessage())
		{
			if (empty($event->getMessageParams()))
			{
				$result['message'] = $event->getMessage();
			}
			else
			{
				$result['message'] = [
					'message' => $event->getMessage(),
					'params' => $event->getMessageParams(),
					'formatted' => $event->getMessageFormatted() ?? vsprintf(
							$event->getMessage(),
							$event->getMessageParams()
						),
				];
			}
		}

		$exceptions = $event->getExceptions();

		for ($i = \count($exceptions) - 1; $i >= 0; --$i)
		{
			$result['exception']['values'][] = $this->serializeException($exceptions[$i]);
		}

		$stacktrace = $event->getStacktrace();

		if (null !== $stacktrace)
		{
			$result['stacktrace'] = [
				'frames' => array_map([$this, 'serializeStacktraceFrame'], $stacktrace->getContexts()),
			];
		}

		return $result;
	}

	private function serializeBreadcrumb(Breadcrumb $breadcrumb): array
	{
		$result = [
			'type' => $breadcrumb->getType(),
			'category' => $breadcrumb->getCategory(),
			'level' => $breadcrumb->getLevel(),
			'timestamp' => $breadcrumb->getTimestamp(),
		];

		if (null !== $breadcrumb->getMessage())
		{
			$result['message'] = $breadcrumb->getMessage();
		}

		if (!empty($breadcrumb->getMetadata()))
		{
			$result['data'] = $breadcrumb->getMetadata();
		}

		return $result;
	}

	private function serializeException(ExceptionData $exception): array
	{
		$exceptionStacktrace = $exception->getStacktrace();
		$result = [
			'type' => $exception->getType(),
			'value' => $exception->getValue(),
		];

		if (null !== $exceptionStacktrace)
		{
			$result['stacktrace'] = [
				'contexts' => array_map([$this, 'serializeStacktraceContext'], $exceptionStacktrace->getContexts()),
			];
		}

		return $result;
	}

	private function serializeStacktraceContext(Context $context): array
	{
		$result = [
			'filename' => $context->getFile(),
			'line_number' => $context->getLine(),
		];

		if (null !== $context->getAbsoluteFilePath())
		{
			$result['abs_path'] = $context->getAbsoluteFilePath();
		}

		if (null !== $context->getFunctionName())
		{
			$result['function'] = $context->getFunctionName();
		}

		if (null !== $context->getRawFunctionName())
		{
			$result['raw_function'] = $context->getRawFunctionName();
		}

		if (!empty($context->getPreContext()))
		{
			$result['pre_context'] = $context->getPreContext();
		}

		if (null !== $context->getContextLine())
		{
			$result['context_line'] = $context->getContextLine();
		}

		if (!empty($context->getPostContext()))
		{
			$result['post_context'] = $context->getPostContext();
		}

		if (!empty($context->getVariables()))
		{
			$result['vars'] = $context->getVariables();
		}

		return $result;
	}
}

<?php

namespace Stuckix\Transport;

use GuzzleHttp\Promise\PromiseInterface;
use Stuckix\Model\Event;

interface Transport
{
	public function close(?int $timeout = null): PromiseInterface;
    public function send(Event $event): PromiseInterface;
}

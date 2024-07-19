<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\simplepackethandler;

use InvalidArgumentException;
use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\simplepackethandler\interceptor\IPacketInterceptor;
use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\simplepackethandler\interceptor\PacketInterceptor;
use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\simplepackethandler\monitor\IPacketMonitor;
use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handle_cancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, $priority, $handle_cancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handle_cancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, $handle_cancelled);
	}
}
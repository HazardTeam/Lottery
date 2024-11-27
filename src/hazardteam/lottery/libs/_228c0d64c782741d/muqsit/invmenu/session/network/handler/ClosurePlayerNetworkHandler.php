<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_228c0d64c782741d\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_228c0d64c782741d\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

final class ClosurePlayerNetworkHandler implements PlayerNetworkHandler{

	/**
	 * @param Closure(Closure) : NetworkStackLatencyEntry $creator
	 */
	public function __construct(
		readonly private Closure $creator
	){}

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry{
		return ($this->creator)($then);
	}
}
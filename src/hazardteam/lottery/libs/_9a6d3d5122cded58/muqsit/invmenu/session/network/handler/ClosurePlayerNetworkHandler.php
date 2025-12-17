<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9a6d3d5122cded58\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_9a6d3d5122cded58\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

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
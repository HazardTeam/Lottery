<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_22bcecf331b7b6cf\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_22bcecf331b7b6cf\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

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
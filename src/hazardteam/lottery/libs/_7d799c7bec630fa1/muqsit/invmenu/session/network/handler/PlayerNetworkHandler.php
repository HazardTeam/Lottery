<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_7d799c7bec630fa1\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_7d799c7bec630fa1\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
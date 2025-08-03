<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_f5f0db04d11d46cc\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_f5f0db04d11d46cc\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
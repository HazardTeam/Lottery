<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_4e5cd0d2976235cb\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_4e5cd0d2976235cb\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
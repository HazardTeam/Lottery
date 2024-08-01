<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b3c2a4de011b1e75\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_b3c2a4de011b1e75\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
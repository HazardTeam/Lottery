<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_97cb8c5932b18c75\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_97cb8c5932b18c75\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
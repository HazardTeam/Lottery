<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_6d19c0889371a630\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_6d19c0889371a630\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
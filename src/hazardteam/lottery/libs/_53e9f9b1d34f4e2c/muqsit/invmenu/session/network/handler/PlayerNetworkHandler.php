<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
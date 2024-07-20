<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8da566e0b52ad2f8\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_8da566e0b52ad2f8\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
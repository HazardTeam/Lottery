<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_21a1ef339905aa20\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_21a1ef339905aa20\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
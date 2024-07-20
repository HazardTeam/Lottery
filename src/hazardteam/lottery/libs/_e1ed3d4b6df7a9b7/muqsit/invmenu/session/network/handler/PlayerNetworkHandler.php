<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_e1ed3d4b6df7a9b7\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_e1ed3d4b6df7a9b7\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
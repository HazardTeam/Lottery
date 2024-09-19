<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_30fad1f7f153f7a3\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_30fad1f7f153f7a3\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
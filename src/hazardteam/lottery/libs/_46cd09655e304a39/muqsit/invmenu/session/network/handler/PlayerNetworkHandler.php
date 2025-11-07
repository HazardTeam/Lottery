<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_46cd09655e304a39\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_46cd09655e304a39\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
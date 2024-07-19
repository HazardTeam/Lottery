<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a289d7e73bad0d48\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_a289d7e73bad0d48\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
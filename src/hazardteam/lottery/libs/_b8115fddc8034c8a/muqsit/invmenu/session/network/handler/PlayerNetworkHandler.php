<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b8115fddc8034c8a\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_b8115fddc8034c8a\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
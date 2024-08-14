<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
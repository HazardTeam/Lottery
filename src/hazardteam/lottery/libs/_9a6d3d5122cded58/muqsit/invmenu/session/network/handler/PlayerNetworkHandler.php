<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9a6d3d5122cded58\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_9a6d3d5122cded58\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
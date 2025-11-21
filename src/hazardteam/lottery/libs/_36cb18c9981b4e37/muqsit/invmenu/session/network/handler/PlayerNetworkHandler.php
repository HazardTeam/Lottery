<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_36cb18c9981b4e37\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_36cb18c9981b4e37\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
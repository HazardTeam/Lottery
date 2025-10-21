<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ea3bd7608284511f\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_ea3bd7608284511f\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
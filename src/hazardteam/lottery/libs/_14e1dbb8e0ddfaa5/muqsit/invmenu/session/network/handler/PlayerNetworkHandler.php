<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
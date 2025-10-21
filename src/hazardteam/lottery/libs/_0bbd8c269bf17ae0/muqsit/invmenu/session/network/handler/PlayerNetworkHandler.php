<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_0bbd8c269bf17ae0\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_0bbd8c269bf17ae0\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
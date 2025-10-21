<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
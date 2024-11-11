<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9ecf393e343e2c88\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_9ecf393e343e2c88\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_231af81575281f7e\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_231af81575281f7e\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_998d1f79d7805887\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_998d1f79d7805887\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
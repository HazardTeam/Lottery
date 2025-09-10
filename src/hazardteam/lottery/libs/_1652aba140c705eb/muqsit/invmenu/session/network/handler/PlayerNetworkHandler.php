<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_1652aba140c705eb\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_1652aba140c705eb\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
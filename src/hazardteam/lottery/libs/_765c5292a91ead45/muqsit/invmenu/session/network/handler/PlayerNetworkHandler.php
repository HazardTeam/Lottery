<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
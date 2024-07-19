<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_63cdd9d5b0576348\muqsit\invmenu\type\util\builder;

use hazardteam\lottery\libs\_63cdd9d5b0576348\muqsit\invmenu\type\BlockFixedInvMenuType;
use hazardteam\lottery\libs\_63cdd9d5b0576348\muqsit\invmenu\type\graphic\network\BlockInvMenuGraphicNetworkTranslator;

final class BlockFixedInvMenuTypeBuilder implements InvMenuTypeBuilder{
	use BlockInvMenuTypeBuilderTrait;
	use FixedInvMenuTypeBuilderTrait;
	use GraphicNetworkTranslatableInvMenuTypeBuilderTrait;

	public function __construct(){
		$this->addGraphicNetworkTranslator(BlockInvMenuGraphicNetworkTranslator::instance());
	}

	public function build() : BlockFixedInvMenuType{
		return new BlockFixedInvMenuType($this->getBlock(), $this->getSize(), $this->getGraphicNetworkTranslator());
	}
}
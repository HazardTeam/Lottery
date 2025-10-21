<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type\util\builder;

use hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type\BlockFixedInvMenuType;
use hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type\graphic\network\BlockInvMenuGraphicNetworkTranslator;

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
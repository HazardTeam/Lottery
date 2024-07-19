<?php

/*
 * Copyright (c) 2024 HazardTeam
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/HazardTeam/Lottery
 */

declare(strict_types=1);

namespace hazardteam\lottery\utils;

use pocketmine\block\GlazedTerracotta;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;

class Converter {
	public static function WoolToGlazed(Wool $wool) : GlazedTerracotta {
		return VanillaBlocks::GLAZED_TERRACOTTA()->setColor($wool->getColor());
	}

	public static function GlazedToWool(GlazedTerracotta $glazed) : Wool {
		return VanillaBlocks::WOOL()->setColor($glazed->getColor());
	}
}
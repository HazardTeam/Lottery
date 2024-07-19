<?php

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
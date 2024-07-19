<?php


namespace hazardteam\lottery;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class LotteryManager {

    /**
     * Summary of ranges
     * @var LotteryRange[]
     */
    private $ranges = [];

    public function __construct(){
        $config = Main::getInstance()->getConfig();
        $range = $config->get("range");
        foreach($range as $key => $value){
            $ranges = explode("=", $key);
            $this->ranges[] = new LotteryRange(floatval($ranges[0]), floatval($ranges[1]), intval($value));
        }
    }

    public function getTables($tables = []) {
        foreach($this->ranges as $range){
            $tables = array_merge($tables, $range->getTable());
        }

        for($i = 1; $i < mt_rand(5, 11); $i++){
            shuffle($tables);
        }

        if(count($tables) < 28){
            $tables = $this->getTables($tables);
        }
        return $tables;
    }
}
<?php

declare(strict_types=1);

namespace hazardteam\lottery;

use hazardteam\lottery\commands\LotteryCommand;
use CortexPE\Commando\PacketHooker;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    private static $instance;
    private LotteryManager $lottmanager;

    public static function getInstance() : self {
        return self::$instance;
    }

    public function onEnable() : void {
        self::$instance = $this;
        $this->saveDefaultConfig();
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        if(!PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }

        $this->lottmanager = new LotteryManager();

        $this->getServer()->getCommandMap()->register("Lottery", new LotteryCommand($this, "lottery", "Try your hand at the Lottery and win big prizes!", ["ltry"]));
    }

    public function reload(){
        $this->getConfig()->reload();
        $this->lottmanager = new LotteryManager();
    }

    public function getLotteryManager() : LotteryManager {
        return $this->lottmanager;
    }

}

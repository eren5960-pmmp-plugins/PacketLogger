<?php
/**
 *  _____                    ____   ___    __     ___
 * | ____| _ __  ___  _ __  | ___| / _ \  / /_   / _ \
 * |  _|  | '__|/ _ \| '_ \ |___ \| (_) || '_ \ | | | |
 * | |___ | |  |  __/| | | | ___) |\__, || (_) || |_| |
 * |_____||_|   \___||_| |_||____/   /_/  \___/  \___/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 */
declare(strict_types=1);

namespace Eren5960\PacketInfo;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\Terminal;

class PacketInfo extends PluginBase implements Listener
{
    /** @var string[] */
    private $not = [];
    /** @var int */
    private $count = 0;

    public function onLoad()
    {
        $this->saveDefaultConfig();
    }

    public function onEnable(): void{
        $this->not = $this->getConfig()->get("no-send-info-packets", []);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task{private $plugin;public function __construct(PacketInfo $plugin){$this->plugin = $plugin;}public function onRun(int $currentTick){$this->plugin->sendReceivedPacketCount();}}, 1200);
    }

    public function onReceivePacket(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        if ($this->canSend($packet)) {
            $this->sendPacketInfo($packet, $event->getPlayer());
        }
        $this->count++;
    }

    /**
     * @param DataPacket $packet
     * @return bool
     */
    private function canSend(DataPacket $packet): bool{
        return !in_array($packet->getName(), $this->not) && $this->getConfig()->get("send-packet-info");
    }

    /**
     * @param DataPacket $packet
     * @param Player $player
     */
    private function sendPacketInfo(DataPacket $packet, Player $player): void{
        echo Terminal::$COLOR_AQUA . "Received packet: " . Terminal::$COLOR_GOLD . $packet->getName() . Terminal::$COLOR_AQUA . " from " . Terminal::$COLOR_GREEN . $player->getName() . "\n";
    }

    public function sendReceivedPacketCount(): void{
        echo Terminal::$COLOR_AQUA . "Received packet count in 1 minute: " . Terminal::$COLOR_GOLD . $this->count . "\n";
        $this->count = 0;
    }
}
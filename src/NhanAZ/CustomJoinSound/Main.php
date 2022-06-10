<?php

declare(strict_types=1);

namespace NhanAZ\CustomJoinSound;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\resourcepacks\ZippedResourcePack;
use libCustomPack\libCustomPack;

class Main extends PluginBase implements Listener
{
    private ZippedResourcePack $libRegRsp;

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        // $this->saveDefaultConfig();
        $this->libRegRsp = libCustomPack::generatePackFromResources($plugin);
        libCustomPack::registerResourcePack($this->libRegRsp);
    }

    protected function onDisable(): void
    {
        libCustomPack::unregisterResourcePack($this->libRegRsp);
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $position = $player->getPosition();
        $packet = new PlaySoundPacket();
        $packet->soundName = "CustomJoinSound";
        $packet->x = $position->getX();
        $packet->y = $position->getY();
        $packet->z = $position->getZ();
        $packet->volume = 1;
        $packet->pitch = 1;
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}

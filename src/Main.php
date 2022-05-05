<?php

declare(strict_types=1);

namespace NhanAZ\CustomJoinSound;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use NhanAZ\libRegRsp\libRegRsp;

class Main extends PluginBase implements Listener {

	private libRegRsp $libRegRsp;

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->libRegRsp = new libRegRsp($this);
		$this->libRegRsp->regRsp("CustomJoinSound.mcpack");
	}

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$pos = $player->getPosition();
		$packet = new PlaySoundPacket();
		$packet->soundName = "CustomJoinSound";
		$packet->x = $pos->getX();
		$packet->y = $pos->getY();
		$packet->z = $pos->getZ();
		$packet->volume = 1;
		$packet->pitch = 1;
		$player->getNetworkSession()->sendDataPacket($packet);
	}
}

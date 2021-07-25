<?php

namespace Prim\ProtocolSupport;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PacketViolationWarningPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use function var_dump;

class EventListener implements Listener {

	public Main $main;

	public function __construct(Main $main){
		$this->main = $main;
	}

	function onQuit(PlayerQuitEvent $event) : void {
		unset($this->main->sessions[$event->getPlayer()->getName()]);
	}

	function onReceive(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		if($packet instanceof LoginPacket){
			$this->main->sessions[$packet->username] = new Session($event->getPlayer(), $packet->protocol);
			$packet->clientData['PlayFabId'] = '';
			$packet->protocol = ProtocolInfo::CURRENT_PROTOCOL;
		} elseif($packet instanceof PacketViolationWarningPacket){
			$this->main->getLogger()->warning("Client error: {$packet->getMessage()}");
			var_dump($this->main->lastSentPackets[$event->getPlayer()->getName()] ?? 'Cannot find last sent packet');
		}
		$protocol = $this->main->sessions[$event->getPlayer()->getName()]->protocol ?? null;
		if($protocol !== ProtocolInfo::CURRENT_PROTOCOL && isset($this->main->protocols[$protocol])){
			if($this->main->protocols[$protocol]->handleInbound($event->getPlayer(), $packet)){
				$event->setCancelled();
			}
		}
		var_dump($packet->getName() . ' (Receive)');
	}

	function onSend(DataPacketSendEvent $event) : void {
		$protocol = $this->main->sessions[$event->getPlayer()->getName()]->protocol ?? null;
		if($protocol !== ProtocolInfo::CURRENT_PROTOCOL && isset($this->main->protocols[$protocol])){
			if($this->main->protocols[$protocol]->handleOutbound($event->getPlayer(), $event->getPacket())){
				$event->setCancelled();
			}
		}
		$this->main->lastSentPackets[$event->getPlayer()->getName()] = $event->getPacket();
		var_dump($event->getPacket()->getName() . ' (Send)');
	}

}
<?php

namespace Prim\ProtocolSupport;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PacketViolationWarningPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use function var_dump;

class EventListener implements Listener {

	public Main $main;

	public function __construct(Main $main){
		$this->main = $main;
	}

	public function onQuit(PlayerQuitEvent $event) : void {
		unset($this->main->sessions[$event->getPlayer()->getName()]);
	}

	public function onReceive(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		if($packet instanceof LoginPacket){
			$this->main->sessions[$packet->username] = new Session($event->getPlayer(), $packet->protocol);
			$packet->clientData['PlayFabId'] = '';
			$packet->clientData['SkinGeometryDataEngineVersion'] = '';
			$packet->protocol = ProtocolInfo::CURRENT_PROTOCOL;
		} elseif($packet instanceof PacketViolationWarningPacket){
			$name = PacketPool::getPacketById($packet->getPacketId())->getName();
			$this->main->getLogger()->warning("Client error ($name): {$packet->getMessage()}");
			var_dump($this->main->lastSentPackets[$event->getPlayer()->getName()] ?? 'Cannot find last sent packet');
		}
		$protocol = $this->main->sessions[$event->getPlayer()->getName()]->protocol ?? null;
		if($protocol !== ProtocolInfo::CURRENT_PROTOCOL && isset($this->main->protocols[$protocol])){
			if($this->main->protocols[$protocol]->handleInbound($event->getPlayer(), $packet)){
				$event->setCancelled();
			}
		}
		//var_dump($packet->getName() . ' (Receive)');
	}

	public function onSend(DataPacketSendEvent $event) : void {
		if(Utils::$customPacket){
			Utils::$customPacket = false;
			return;
		}
		$protocol = $this->main->sessions[$event->getPlayer()->getName()]->protocol ?? null;
		if($protocol !== ProtocolInfo::CURRENT_PROTOCOL && isset($this->main->protocols[$protocol])){
			if($this->main->protocols[$protocol]->handleOutbound($event->getPlayer(), $event->getPacket())){
				$event->setCancelled();
			}
		}
		$this->main->lastSentPackets[$event->getPlayer()->getName()] = $event->getPacket();
		//var_dump($event->getPacket()->getName() . ' (Send)');
	}

}

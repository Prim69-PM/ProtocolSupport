<?php

namespace Prim\ProtocolSupport;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;

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
			$this->main->sessions[$event->getPlayer()->getName()] = new Session($event->getPlayer(), $packet->protocol);
			$packet->clientData['PlayFabId'] = '';
		}
		$this->main->protocols[$this->main->sessions[$event->getPlayer()->getName()]->protocol]->handleInbound($packet);
	}

	function onSend(DataPacketSendEvent $event) : void {
		$this->main->protocols[$this->main->sessions[$event->getPlayer()->getName()]->protocol]->handleOutbound($event->getPacket());
	}

}
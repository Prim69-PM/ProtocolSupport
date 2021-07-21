<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\protocol\DataPacket;
use Prim\ProtocolSupport\Protocol\PacketHandler;

class PacketHandler419 extends PacketHandler {

	public function handleInbound(DataPacket $packet) : void {
		switch($packet::class){

		}
	}

	public function handleOutbound(DataPacket $packet) : void {
		switch($packet::class){
			case StartGamePacket419::class:
				//
				break;
			case ResourcePacksInfoPacket419::class:
				//
				break;
		}
	}

}
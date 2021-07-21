<?php

namespace Prim\ProtocolSupport\Protocol;

use pocketmine\network\mcpe\protocol\DataPacket;

abstract class PacketHandler {

	abstract function handleInbound(DataPacket $packet) : void;

	abstract function handleOutbound(DataPacket $packet) : void;

}
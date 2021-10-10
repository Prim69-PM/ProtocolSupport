<?php

namespace Prim\ProtocolSupport\Protocol\v431;

use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use Prim\ProtocolSupport\Protocol\PacketUtils;

class GameRulesChangedPacket431 extends GameRulesChangedPacket {

	protected function decodePayload(){
		$this->gameRules = PacketUtils::getGameRules428($this);
	}

	protected function encodePayload(){
		PacketUtils::putGameRules428($this->gameRules, $this);
	}

}
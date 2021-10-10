<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use Prim\ProtocolSupport\Protocol\PacketUtils;

class MobArmorEquipmentPacket428 extends MobArmorEquipmentPacket {

	protected function encodePayload(){
		$this->putEntityRuntimeId($this->entityRuntimeId);
		PacketUtils::putSlot428($this->head->getItemStack(), $this);
		PacketUtils::putSlot428($this->chest->getItemStack(), $this);
		PacketUtils::putSlot428($this->legs->getItemStack(), $this);
		PacketUtils::putSlot428($this->feet->getItemStack(), $this);
	}

}
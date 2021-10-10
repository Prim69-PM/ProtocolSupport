<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use Prim\ProtocolSupport\Protocol\PacketUtils;

class MobEquipmentPacket428 extends MobEquipmentPacket {

	public static function decode428(MobEquipmentPacket $packet){
		$packet->setOffset(0);
		$packet->getUnsignedVarInt(); // decodeHeader
		self::decodePayload428($packet);
	}

	public static function decodePayload428(MobEquipmentPacket $packet){
		$packet->entityRuntimeId = $packet->getEntityRuntimeId();
		$packet->item = PacketUtils::getSlot428($packet);
		$packet->inventorySlot = (\ord($packet->get(1)));
		$packet->hotbarSlot = (\ord($packet->get(1)));
		$packet->windowId = (\ord($packet->get(1)));
	}

	protected function decodePayload(){
		self::decodePayload428($this);
	}

	protected function encodePayload(){
		$this->putEntityRuntimeId($this->entityRuntimeId);
		PacketUtils::putSlot428($this->item->getItemStack(), $this);
		($this->buffer .= \chr($this->inventorySlot));
		($this->buffer .= \chr($this->hotbarSlot));
		($this->buffer .= \chr($this->windowId));
	}

}
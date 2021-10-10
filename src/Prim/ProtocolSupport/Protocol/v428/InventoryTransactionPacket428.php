<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use Prim\ProtocolSupport\Protocol\PacketUtils;
use stdClass;
use UnexpectedValueException;

class InventoryTransactionPacket428 extends InventoryTransactionPacket {

	public bool $hasItemStackIds;

	/** @var NetworkInventoryAction428[] */
	public array $actions = [];

	/** @var stdClass */
	public $trData;

	protected function decodePayload() : void {
		$this->requestId = $this->readGenericTypeNetworkId();
		$this->requestChangedSlots = [];
		if($this->requestId !== 0){
			for($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i){
				$this->requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($this);
			}
		}

		$this->trData = new stdClass();
		$this->trData->transactionType = $this->getUnsignedVarInt();

		$this->hasItemStackIds = $this->getBool();

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->actions[] = (new NetworkInventoryAction428())->read428($this, $this->hasItemStackIds);
		}

		switch($this->trData->transactionType){
			case self::TYPE_NORMAL:
			case self::TYPE_MISMATCH:
				//Regular ComplexInventoryTransaction doesn't read any extra data
				break;
			case self::TYPE_USE_ITEM:
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->getBlockPosition($this->trData->x, $this->trData->y, $this->trData->z);
				$this->trData->face = $this->getVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = PacketUtils::getSlot428($this);
				$this->trData->playerPos = $this->getVector3();
				$this->trData->clickPos = $this->getVector3();
				$this->trData->blockRuntimeId = $this->getUnsignedVarInt();
				break;
			case self::TYPE_USE_ITEM_ON_ENTITY:
				$this->trData->entityRuntimeId = $this->getEntityRuntimeId();
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = PacketUtils::getSlot428($this);
				$this->trData->playerPos = $this->getVector3();
				$this->trData->clickPos = $this->getVector3();
				break;
			case self::TYPE_RELEASE_ITEM:
				$this->trData->actionType = $this->getUnsignedVarInt();
				$this->trData->hotbarSlot = $this->getVarInt();
				$this->trData->itemInHand = PacketUtils::getSlot428($this);
				$this->trData->headPos = $this->getVector3();
				break;
			default:
				throw new UnexpectedValueException("Unknown transaction type {$this->trData->transactionType}");
		}
	}

	protected function encodePayload() : void {
		$this->writeGenericTypeNetworkId($this->requestId);
		if($this->requestId !== 0){
			$this->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach($this->requestChangedSlots as $changedSlots){
				$changedSlots->write($this);
			}
		}

		$this->putUnsignedVarInt($this->trData->transactionType);

		$this->putBool($this->hasItemStackIds);

		$this->putUnsignedVarInt(count($this->actions));
		foreach($this->actions as $action){
			$action->write428($this, $this->hasItemStackIds);
		}

		switch($this->trData->transactionType){
			case self::TYPE_NORMAL:
			case self::TYPE_MISMATCH:
				break;
			case self::TYPE_USE_ITEM:
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putBlockPosition($this->trData->x, $this->trData->y, $this->trData->z);
				$this->putVarInt($this->trData->face);
				$this->putVarInt($this->trData->hotbarSlot);
				PacketUtils::putSlot428($this->trData->itemInHand, $this);
				$this->putVector3($this->trData->playerPos);
				$this->putVector3($this->trData->clickPos);
				$this->putUnsignedVarInt($this->trData->blockRuntimeId);
				break;
			case self::TYPE_USE_ITEM_ON_ENTITY:
				$this->putEntityRuntimeId($this->trData->entityRuntimeId);
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putVarInt($this->trData->hotbarSlot);
				PacketUtils::putSlot428($this->trData->itemInHand, $this);
				$this->putVector3($this->trData->playerPos);
				$this->putVector3($this->trData->clickPos);
				break;
			case self::TYPE_RELEASE_ITEM:
				$this->putUnsignedVarInt($this->trData->actionType);
				$this->putVarInt($this->trData->hotbarSlot);
				PacketUtils::putSlot428($this->trData->itemInHand, $this);
				$this->putVector3($this->trData->headPos);
				break;
			default:
				throw new InvalidArgumentException("Unknown transaction type {$this->trData->transactionType}");
		}
	}

}
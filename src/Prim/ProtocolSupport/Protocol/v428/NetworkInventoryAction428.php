<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use InvalidArgumentException;
use InvalidStateException;
use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use Prim\ProtocolSupport\Protocol\PacketUtils;
use UnexpectedValueException;
use function is_null;

class NetworkInventoryAction428 extends NetworkInventoryAction {

	/** @var Item */
	public $oldItem;
	/** @var Item */
	public $newItem;

	public ?int $newItemStackId = null;

	public function read428(NetworkBinaryStream $packet, bool $hasItemStackIds) : self {
		$this->sourceType = $packet->getUnsignedVarInt();

		switch($this->sourceType){
			case self::SOURCE_TODO:
			case self::SOURCE_CONTAINER:
				$this->windowId = $packet->getVarInt();
				break;
			case self::SOURCE_WORLD:
				$this->sourceFlags = $packet->getUnsignedVarInt();
				break;
			case self::SOURCE_CREATIVE:
				break;
			default:
				throw new UnexpectedValueException("Unknown inventory action source type $this->sourceType");
		}

		$this->inventorySlot = $packet->getUnsignedVarInt();
		$this->oldItem = PacketUtils::getSlot428($packet);
		$this->newItem = PacketUtils::getSlot428($packet);
		if($hasItemStackIds){
			$this->newItemStackId = $packet->readGenericTypeNetworkId();
		}

		return $this;
	}

	public function write428(NetworkBinaryStream $packet, bool $hasItemStackIds) : void {
		$packet->putUnsignedVarInt($this->sourceType);

		switch($this->sourceType){
			case self::SOURCE_TODO:
			case self::SOURCE_CONTAINER:
				$packet->putVarInt($this->windowId);
				break;
			case self::SOURCE_WORLD:
				$packet->putUnsignedVarInt($this->sourceFlags);
				break;
			case self::SOURCE_CREATIVE:
				break;
			default:
				throw new InvalidArgumentException("Unknown inventory action source type $this->sourceType");
		}

		$packet->putUnsignedVarInt($this->inventorySlot);
		PacketUtils::putSlot428($this->oldItem, $packet);
		PacketUtils::putSlot428($this->newItem, $packet);
		if($hasItemStackIds){
			if(is_null($this->newItemStackId)){
				throw new InvalidStateException("Item stack ID for newItem must be provided");
			}
			$packet->writeGenericTypeNetworkId($this->newItemStackId);
		}
	}

}
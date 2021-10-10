<?php

namespace Prim\ProtocolSupport\Protocol;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\GameRuleType;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use function var_dump;

class PacketUtils {

	private const DAMAGE_TAG = "Damage"; //TAG_Int
	private const DAMAGE_TAG_CONFLICT_RESOLUTION = "___Damage_ProtocolCollisionResolution___";
	private const PM_META_TAG = "___Meta___";

	/**
	 * @param NetworkBinaryStream $in
	 * @return Item
	 * Replacement for ItemStackWrapper::read()
	 */
	public static function getSlot428(NetworkBinaryStream $in) : Item {
		$netId = $in->getVarInt();
		if($netId === 0){
			return ItemFactory::get(0, 0, 0);
		}

		$auxValue = $in->getVarInt();
		$netData = $auxValue >> 8;
		$cnt = $auxValue & 0xff;

		[$id, $meta] = ItemTranslator::getInstance()->fromNetworkId($netId, $netData);

		$nbtLen = $in->getLShort();

		/** @var CompoundTag|null $nbt */
		$nbt = null;
		if($nbtLen === 0xffff){
			$nbtDataVersion = $in->getByte();
			if($nbtDataVersion !== 1){
				throw new \UnexpectedValueException("Unexpected NBT data version $nbtDataVersion");
			}
			$decodedNBT = (new NetworkLittleEndianNBTStream())->read($in->buffer, false, $in->offset, 512);
			if(!($decodedNBT instanceof CompoundTag)){
				throw new \UnexpectedValueException("Unexpected root tag type for itemstack");
			}
			$nbt = $decodedNBT;
		}elseif($nbtLen !== 0){
			throw new \UnexpectedValueException("Unexpected fake NBT length $nbtLen");
		}

		//TODO
		for($i = 0, $canPlaceOn = $in->getVarInt(); $i < $canPlaceOn; ++$i){
			$in->getString();
		}

		//TODO
		for($i = 0, $canDestroy = $in->getVarInt(); $i < $canDestroy; ++$i){
			$in->getString();
		}

		if($netId === ItemTypeDictionary::getInstance()->fromStringId("minecraft:shield")){
			$in->getVarLong(); //"blocking tick" (ffs mojang)
		}
		if($nbt !== null){
			if($nbt->hasTag(self::DAMAGE_TAG, IntTag::class)){
				$meta = $nbt->getInt(self::DAMAGE_TAG);
				$nbt->removeTag(self::DAMAGE_TAG);
				if($nbt->count() === 0){
					$nbt = null;
					goto end;
				}
			}
			if(($conflicted = $nbt->getTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION)) !== null){
				$nbt->removeTag(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
				$conflicted->setName(self::DAMAGE_TAG);
				$nbt->setTag($conflicted);
			}
		}
		end:
		return ItemFactory::get($id, $meta, $cnt, $nbt);
	}

	/**
	 * @param Item $item
	 * @param NetworkBinaryStream $in
	 * Replacement for ItemStackWrapper::write()
	 */
	public static function putSlot428(Item $item, NetworkBinaryStream $in) : void {
		if($item->getId() === 0){
			$in->putVarInt(0);
			return;
		}

		[$netId, $netData] = ItemTranslator::getInstance()->toNetworkId($item->getId(), $item->getDamage());

		$in->putVarInt($netId);
		$auxValue = (($netData & 0x7fff) << 8) | $item->getCount();
		$in->putVarInt($auxValue);

		$nbt = null;
		if($item->hasCompoundTag()){
			$nbt = clone $item->getNamedTag();
		}
		if($item instanceof Durable and $item->getDamage() > 0){
			if($nbt !== null){
				if(($existing = $nbt->getTag(self::DAMAGE_TAG)) !== null){
					$nbt->removeTag(self::DAMAGE_TAG);
					$existing->setName(self::DAMAGE_TAG_CONFLICT_RESOLUTION);
					$nbt->setTag($existing);
				}
			}else{
				$nbt = new CompoundTag();
			}
			$nbt->setInt(self::DAMAGE_TAG, $item->getDamage());
		}

		if($nbt !== null){
			$in->putLShort(0xffff);
			$in->putByte(1); //TODO: NBT data version (?)
			$in->put((new NetworkLittleEndianNBTStream())->write($nbt));
		}else{
			$in->putLShort(0);
		}

		$in->putVarInt(0); //CanPlaceOn entry count (TODO)
		$in->putVarInt(0); //CanDestroy entry count (TODO)

		if($netId === ItemTypeDictionary::getInstance()->fromStringId("minecraft:shield")){
			$in->putVarLong(0); //"blocking tick" (ffs mojang)
		}
	}

	public static function newTo428Wrappers(int $count, NetworkBinaryStream $in) : array {
		$items = [];
		for($i = 0; $i < $count; $i++){
			$list[] = new ItemStackWrapper($in->readGenericTypeNetworkId(), PacketUtils::getSlot428($in));
		}
		return $items;
	}

	/**
	 * Reads gamerules
	 * TODO: implement this properly
	 *
	 * @return array[], members are in the structure [name => [type, value]]
	 * @phpstan-return array<string, array{0: int, 1: bool|int|float}>
	 */
	public static function getGameRules428(NetworkBinaryStream $in) : array{
		$count = $in->getUnsignedVarInt();
		$rules = [];
		for($i = 0; $i < $count; ++$i){
			$name = $in->getString();
			$type = $in->getUnsignedVarInt();
			$value = match ($type) {
				GameRuleType::BOOL => $in->getBool(),
				GameRuleType::INT => $in->getUnsignedVarInt(),
				GameRuleType::FLOAT => $in->getLFloat(),
				default => null,
			};

			$rules[$name] = [$type, $value];
		}

		return $rules;
	}

	/**
	 * Writes a gamerule array, members should be in the structure [name => [type, value]]
	 * TODO: implement this properly
	 *
	 * @param array[] $rules
	 * @phpstan-param array<string, array{0: int, 1: bool|int|float}> $rules
	 */
	public static function putGameRules428(array $rules, NetworkBinaryStream $in) : void{
		$in->putUnsignedVarInt(count($rules));
		foreach($rules as $name => $rule){
			$in->putString($name);
			$in->putUnsignedVarInt($rule[0]);
			switch($rule[0]){
				case GameRuleType::BOOL:
					$in->putBool($rule[1]);
					break;
				case GameRuleType::INT:
					$in->putUnsignedVarInt($rule[1]);
					break;
				case GameRuleType::FLOAT:
					$in->putLFloat($rule[1]);
					break;
			}
		}
	}

}
<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use Exception;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemStackResponsePacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\Server;
use Prim\ProtocolSupport\Protocol\PacketHandler;
use Prim\ProtocolSupport\Protocol\v422\ResourcePacksInfoPacket422;
use Prim\ProtocolSupport\Protocol\v428\CraftingDataPacket428;
use Prim\ProtocolSupport\Protocol\v428\CreativeContentPacket428;
use Prim\ProtocolSupport\Protocol\v428\InventoryContentPacket428;
use Prim\ProtocolSupport\Protocol\v428\InventorySlotPacket428;
use Prim\ProtocolSupport\Protocol\v428\InventoryTransactionPacket428;
use Prim\ProtocolSupport\Protocol\v428\MobArmorEquipmentPacket428;
use Prim\ProtocolSupport\Protocol\v428\MobEquipmentPacket428;
use Prim\ProtocolSupport\Protocol\v428\PlayerSkinPacket428;
use Prim\ProtocolSupport\Protocol\v428\SkinData428;
use Prim\ProtocolSupport\Protocol\v422\StartGamePacket422;
use Prim\ProtocolSupport\Protocol\v431\GameRulesChangedPacket431;
use Prim\ProtocolSupport\Protocol\v440\AvailableCommandsPacket440;
use Prim\ProtocolSupport\Protocol\v440\SetTitlePacket440;
use Prim\ProtocolSupport\Utils;
use function in_array;
use function var_dump;

class PacketHandler419 extends PacketHandler {

	public const USED_INBOUND = [
		MobEquipmentPacket::NETWORK_ID, PlayerSkinPacket::NETWORK_ID
	];

	public const USED_OUTBOUND = [
		StartGamePacket::NETWORK_ID, ResourcePacksInfoPacket::NETWORK_ID, PlayerSkinPacket::NETWORK_ID, PlayerListPacket::NETWORK_ID,
		AvailableCommandsPacket::NETWORK_ID, ItemStackResponsePacket::NETWORK_ID, InventoryContentPacket::NETWORK_ID, InventorySlotPacket::NETWORK_ID,
		CreativeContentPacket::NETWORK_ID, MobEquipmentPacket::NETWORK_ID, CraftingDataPacket::NETWORK_ID, SetTitlePacket::NETWORK_ID,
		GameRulesChangedPacket::NETWORK_ID, MobArmorEquipmentPacket::NETWORK_ID, InventoryTransactionPacket::NETWORK_ID, CraftingEventPacket::NETWORK_ID,
		AddPlayerPacket::NETWORK_ID, AddItemActorPacket::NETWORK_ID
	];

	public function handleInbound(Player $player, DataPacket $packet) : bool {
		switch($packet::NETWORK_ID){
			case BatchPacket::NETWORK_ID:
				/** @var BatchPacket $packet */
				$cancel = false;
				foreach($packet->getPackets() as $buff){
					$packet = PacketPool::getPacket($buff);
					var_dump($packet->getName() . ' (Batch Receive)');
					try {
						$packet->decode();
					} catch(Exception){
						var_dump("FAILED TO DECODE PACKET: {$packet->getName()}");
						switch($packet::NETWORK_ID){
							case MobEquipmentPacket::NETWORK_ID:
								/** @var MobEquipmentPacket $packet */
								Server::getInstance()->getLogger()->warning('MobEquipmentPacket handled in Receive (Batch)');
								var_dump($player->getInventory()->getItemInHand()->getId() . ':' . $player->getInventory()->getItemInHand()->getDamage());
								if($player->getInventory()->getItemInHand()->getId() == -1){
									var_dump($player->getInventory()->getItemInHand());
								}
								$this->handleInbound($player, $packet);
								$cancel = true;
								break;
							default:
								if(in_array($packet::NETWORK_ID, self::USED_INBOUND, true)){
									Server::getInstance()->getLogger()->warning("{$packet->getName()} received in Batch (Decoded)");
									$this->handleInbound($player, $packet);
									$cancel = true;
								}
						}
						continue;
					}
					if(in_array($packet::NETWORK_ID, self::USED_INBOUND, true)){
						Server::getInstance()->getLogger()->warning("{$packet->getName()} received in Batch");
						$this->handleInbound($player, $packet);
						$cancel = true;
					}
				}
				return $cancel;
			case PlayerSkinPacket::NETWORK_ID:
				/** @var PlayerSkinPacket $packet */
				Server::getInstance()->getLogger()->warning('sent player skin packet receive');
				$packet->skin = SkinData428::toServer($packet->skin);
				return false;
			case MobEquipmentPacket::NETWORK_ID:
				/** @var MobEquipmentPacket $packet */
				Server::getInstance()->getLogger()->warning('MobEquipmentPacket handled in Receive');
				MobEquipmentPacket428::decode428($packet);
				//$player->handleMobEquipment($packet); todo
				return true;
			case InventoryTransactionPacket::NETWORK_ID:
				/** @var InventoryTransactionPacket $packet */
				// todo
				return true;
		}
		return false;
	}

	public function handleOutbound(Player $player, DataPacket $packet) : bool {
		if($packet::NETWORK_ID === BatchPacket::NETWORK_ID || in_array($packet::NETWORK_ID, self::USED_OUTBOUND, true)){
			switch($packet::NETWORK_ID){
				case BatchPacket::NETWORK_ID:
					/** @var BatchPacket $packet */
					$cancel = false;
					foreach($packet->getPackets() as $buff){
						$packet = PacketPool::getPacket($buff);
						var_dump($packet->getName() . ' (Batch Send)');
						try {
							$packet->decode();
						} catch(Exception){
							var_dump("FAILED TO DECODE PACKET: {$packet->getName()} << Outbound");
							continue;
						}
						if(in_array($packet::NETWORK_ID, self::USED_OUTBOUND, true)){
							$this->handleOutbound($player, $packet);
							Server::getInstance()->getLogger()->warning("{$packet->getName()} (Batch Send)");
							$cancel = true;
						}
					}
					return $cancel;
				case StartGamePacket::NETWORK_ID:
					/** @var StartGamePacket $packet */
					Utils::sendSilentPacket($player, StartGamePacket422::from($packet), false, true);
					return true;
				case ResourcePacksInfoPacket::NETWORK_ID:
					/** @var ResourcePacksInfoPacket $packet */
					$pk = new ResourcePacksInfoPacket422;
					$pk->mustAccept = $packet->mustAccept;
					$pk->hasScripts = $packet->hasScripts;
					$pk->behaviorPackEntries = $packet->behaviorPackEntries;
					$pk->resourcePackEntries = $packet->resourcePackEntries;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case PlayerSkinPacket::NETWORK_ID:
					/** @var PlayerSkinPacket $packet */
					Server::getInstance()->getLogger()->warning('PlayerSkinPacket428 (Send)');
					Utils::sendSilentPacket($player, PlayerSkinPacket428::from($packet, false), false, true);
					return true;
				case PlayerListPacket::NETWORK_ID:
					/** @var PlayerListPacket $packet */
					Server::getInstance()->getLogger()->warning('PlayerListPacket428 (Send)');
					$entries = [];
					foreach($packet->entries as $entry){
						$entry->skinData = SkinData428::toClient($entry->skinData);
						$entries[] = $entry;
					}
					$pk = new PlayerListPacket419;
					$pk->entries = $entries;
					$pk->type = $packet->type;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case AvailableCommandsPacket::NETWORK_ID:
					/** @var AvailableCommandsPacket $packet */
					Server::getInstance()->getLogger()->warning('AvailableCommandsPacket440 (Send)');
					Utils::sendSilentPacket($player, AvailableCommandsPacket440::from($packet), false, true);
					return true;
				case ItemStackResponsePacket::NETWORK_ID:
					/** @var ItemStackResponsePacket $packet */
					Server::getInstance()->getLogger()->warning('ItemStackResponsePacket419 (Send)');
					$pk = new ItemStackResponsePacket419;
					$pk->responses = $packet->getResponses();
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case InventoryContentPacket::NETWORK_ID:
					/** @var InventoryContentPacket $packet */
					Server::getInstance()->getLogger()->warning('InventoryContentPacket428 (Send)');
					$pk = new InventoryContentPacket428;
					$pk->windowId = $packet->windowId;
					$pk->items = $packet->items;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case InventorySlotPacket::NETWORK_ID:
					/** @var InventorySlotPacket $packet */
					Server::getInstance()->getLogger()->warning('InventorySlotPacket428 (Send)');
					$pk = new InventorySlotPacket428;
					$pk->windowId = $packet->windowId;
					$pk->inventorySlot = $packet->inventorySlot;
					$pk->item = $packet->item;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case CreativeContentPacket::NETWORK_ID:
					/** @var CreativeContentPacket $packet */
					Server::getInstance()->getLogger()->warning('CreativeContentPacket428 (Send)');
					$pk = new CreativeContentPacket428;
					$pk->entries = $packet->getEntries();
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case MobEquipmentPacket::NETWORK_ID:
					/** @var MobEquipmentPacket $packet */
					Server::getInstance()->getLogger()->warning('MobEquipmentPacket428 (Send)');
					$pk = new MobEquipmentPacket428;
					$pk->entityRuntimeId = $packet->entityRuntimeId;
					$pk->item = $packet->item;
					$pk->inventorySlot = $packet->inventorySlot;
					$pk->hotbarSlot = $packet->hotbarSlot;
					$pk->windowId = $packet->windowId;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case CraftingDataPacket::NETWORK_ID:
					/** @var CraftingDataPacket $packet */
					Server::getInstance()->getLogger()->warning('CraftingDataPacket428 (Send)');
					$pk = new CraftingDataPacket428;
					$pk->entries = $packet->entries;
					$pk->encode();
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case SetTitlePacket::NETWORK_ID:
					/** @var SetTitlePacket $packet */
					$pk = new SetTitlePacket440;
					$pk->type = $packet->type;
					$pk->text = $packet->text;
					$pk->fadeInTime = $packet->fadeInTime;
					$pk->stayTime = $packet->stayTime;
					$pk->fadeOutTime = $packet->fadeOutTime;
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case GameRulesChangedPacket::NETWORK_ID:
					/** @var GameRulesChangedPacket $packet */
					$pk = new GameRulesChangedPacket431;
					$pk->gameRules = $packet->gameRules;
					$pk->encode();
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
				case MobArmorEquipmentPacket::NETWORK_ID:
					/** @var MobArmorEquipmentPacket $packet */
					$pk = new MobArmorEquipmentPacket428;
					$pk->head = $packet->head;
					$pk->chest = $packet->chest;
					$pk->legs = $packet->legs;
					$pk->feet = $packet->feet;
					$pk->encode();
					Utils::sendSilentPacket($player, $pk, false, true);
					return true;
			}
		}
		return false;
	}

}
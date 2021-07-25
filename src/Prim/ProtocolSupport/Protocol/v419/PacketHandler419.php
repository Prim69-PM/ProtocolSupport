<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use Exception;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\Player;
use pocketmine\Server;
use Prim\ProtocolSupport\Protocol\PacketHandler;
use Prim\ProtocolSupport\Protocol\v422\ResourcePacksInfoPacket422;
use Prim\ProtocolSupport\Protocol\v428\PlayerSkinPacket428;
use Prim\ProtocolSupport\Protocol\v428\SkinData428;
use Prim\ProtocolSupport\Protocol\v428\StartGamePacket428;
use Prim\ProtocolSupport\Utils;
use function var_dump;

class PacketHandler419 extends PacketHandler {

	public function handleInbound(Player $player, DataPacket $packet) : bool {
		switch($packet::class){
			case BatchPacket::class:
				/** @var BatchPacket $packet */
				foreach($packet->getPackets() as $buff){
					$packet = PacketPool::getPacket($buff);
					var_dump($packet->getName() . ' (Batch Send)');
					try {
						$packet->decode();
					} catch(Exception){
						var_dump("FAILED TO DECODE PACKET: {$packet->getName()}");
						continue;
					}
					switch($packet::class){
						case PlayerSkinPacket::class:
							/** @var PlayerSkinPacket $packet */
							Server::getInstance()->getLogger()->warning('sent player skin packet receive batch');
							Utils::sendSilentPacket($player, PlayerSkinPacket428::from($packet), false, true);
							return true;
					}
				}
				break;
			case PlayerSkinPacket::class:
				/** @var PlayerSkinPacket $packet */
				Server::getInstance()->getLogger()->warning('sent player skin packet receive');
				Utils::sendSilentPacket($player, PlayerSkinPacket428::from($packet), false, true);
				return true;
		}
		return false;
	}

	public function handleOutbound(Player $player, DataPacket $packet) : bool {
		switch($packet::class){
			case BatchPacket::class:
				/** @var BatchPacket $packet */
				foreach($packet->getPackets() as $buff){
					$packet = PacketPool::getPacket($buff);
					var_dump($packet->getName() . ' (Batch Send)');
					try {
						$packet->decode();
					} catch(Exception){
						var_dump("FAILED TO DECODE PACKET: {$packet->getName()}");
						continue;
					}
					switch($packet::class){
						case ResourcePacksInfoPacket::class:
							/** @var ResourcePacksInfoPacket $packet */
							$pk = new ResourcePacksInfoPacket422;
							$pk->mustAccept = $packet->mustAccept;
							$pk->hasScripts = $packet->hasScripts;
							$pk->behaviorPackEntries = $packet->behaviorPackEntries;
							$pk->resourcePackEntries = $packet->resourcePackEntries;
							Utils::sendSilentPacket($player, $pk, false, true);
							Server::getInstance()->getLogger()->warning('sent ResourcePacksInfo packet batch send');
							return true;
						case PlayerSkinPacket::class:
							/** @var PlayerSkinPacket $packet */
							Server::getInstance()->getLogger()->warning('sent player skin packet send batch');
							Utils::sendSilentPacket($player, PlayerSkinPacket428::from($packet, false), false, true);
							return true;
						case PlayerListPacket::class:
							/** @var PlayerListPacket $packet */
							Server::getInstance()->getLogger()->warning('sent player list packet batch send');
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
					}
				}
				break;
			case StartGamePacket::class:
				/** @var StartGamePacket $packet */
				Utils::sendSilentPacket($player, StartGamePacket428::from($packet), false, true);
				return true;
			case ResourcePacksInfoPacket::class:
				/** @var ResourcePacksInfoPacket $packet */
				$pk = new ResourcePacksInfoPacket422;
				$pk->mustAccept = $packet->mustAccept;
				$pk->hasScripts = $packet->hasScripts;
				$pk->behaviorPackEntries = $packet->behaviorPackEntries;
				$pk->resourcePackEntries = $packet->resourcePackEntries;
				Utils::sendSilentPacket($player, $pk, false, true);
				return true;
			case PlayerSkinPacket::class:
				/** @var PlayerSkinPacket $packet */
				Server::getInstance()->getLogger()->warning('sent player skin packet send');
				Utils::sendSilentPacket($player, PlayerSkinPacket428::from($packet, false), false, true);
				return true;
			case PlayerListPacket::class:
				/** @var PlayerListPacket $packet */
				Server::getInstance()->getLogger()->warning('sent player list packet send');
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
		}
		return false;
	}

}
<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\SkinData;
use pocketmine\network\mcpe\protocol\types\SkinImage;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use Prim\ProtocolSupport\Protocol\v428\SkinData428;

class PlayerListPacket419 extends PlayerListPacket {

	public function getSkin() : SkinData {
		$skinId = $this->getString();
		$this->getString();
		$skinResourcePatch = $this->getString();
		$skinData = $this->getSkinImage();
		$animationCount = $this->getLInt();
		$animations = [];
		for($i = 0; $i < $animationCount; ++$i){
			$skinImage = $this->getSkinImage();
			$animationType = $this->getLInt();
			$animationFrames = $this->getLFloat();
			$expressionType = $this->getLInt();
			$animations[] = new SkinAnimation($skinImage, $animationType, $animationFrames, $expressionType);
		}
		$capeData = $this->getSkinImage();
		$geometryData = $this->getString();
		$animationData = $this->getString();
		$premium = $this->getBool();
		$persona = $this->getBool();
		$capeOnClassic = $this->getBool();
		$capeId = $this->getString();
		$fullSkinId = $this->getString();
		$armSize = $this->getString();
		$skinColor = $this->getString();
		$personaPieceCount = $this->getLInt();
		$personaPieces = [];
		for($i = 0; $i < $personaPieceCount; ++$i){
			$pieceId = $this->getString();
			$pieceType = $this->getString();
			$packId = $this->getString();
			$isDefaultPiece = $this->getBool();
			$productId = $this->getString();
			$personaPieces[] = new PersonaSkinPiece($pieceId, $pieceType, $packId, $isDefaultPiece, $productId);
		}
		$pieceTintColorCount = $this->getLInt();
		$pieceTintColors = [];
		for($i = 0; $i < $pieceTintColorCount; ++$i){
			$pieceType = $this->getString();
			$colorCount = $this->getLInt();
			$colors = [];
			for($j = 0; $j < $colorCount; ++$j){
				$colors[] = $this->getString();
			}
			$pieceTintColors[] = new PersonaPieceTintColor(
				$pieceType,
				$colors
			);
		}

		return new SkinData428($skinId, $skinResourcePatch, $skinData, $animations, $capeData, $geometryData, $animationData, $premium, $persona, $capeOnClassic, $capeId, $fullSkinId, $armSize, $skinColor, $personaPieces, $pieceTintColors);
	}

	private function getSkinImage() : SkinImage{
		$width = $this->getLInt();
		$height = $this->getLInt();
		$data = $this->getString();
		try{
			return new SkinImage($height, $width, $data);
		}catch(\InvalidArgumentException $e){
			throw new PacketDecodeException($e->getMessage(), 0, $e);
		}
	}

    public function putSkin(SkinData $skin){
        $this->putString($skin->getSkinId());
        $this->putString($skin->getResourcePatch());
        $this->putSkinImage($skin->getSkinImage());
        $this->putLInt(count($skin->getAnimations()));
        foreach($skin->getAnimations() as $animation){
            $this->putSkinImage($animation->getImage());
            $this->putLInt($animation->getType());
            $this->putLFloat($animation->getFrames());
        }
        $this->putSkinImage($skin->getCapeImage());
        $this->putString($skin->getGeometryData());
        $this->putString($skin->getAnimationData());
        $this->putBool($skin->isPremium());
        $this->putBool($skin->isPersona());
        $this->putBool($skin->isPersonaCapeOnClassic());
        $this->putString($skin->getCapeId());
        $this->putString($skin->getFullSkinId());
        $this->putString($skin->getArmSize());
        $this->putString($skin->getSkinColor());
        $this->putLInt(count($skin->getPersonaPieces()));
        foreach($skin->getPersonaPieces() as $piece){
            $this->putString($piece->getPieceId());
            $this->putString($piece->getPieceType());
            $this->putString($piece->getPackId());
            $this->putBool($piece->isDefaultPiece());
            $this->putString($piece->getProductId());
        }
        $this->putLInt(count($skin->getPieceTintColors()));
        foreach($skin->getPieceTintColors() as $tint){
            $this->putString($tint->getPieceType());
            $this->putLInt(count($tint->getColors()));
            foreach($tint->getColors() as $color){
                $this->putString($color);
            }
        }
    }

    private function putSkinImage(SkinImage $image) : void{
        $this->putLInt($image->getWidth());
        $this->putLInt($image->getHeight());
        $this->putString($image->getData());
    }

	protected function decodePayload(){
		$this->type = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$entry = new PlayerListEntry();

			if($this->type === self::TYPE_ADD){
				$entry->uuid = $this->getUUID();
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->username = $this->getString();
				$entry->xboxUserId = $this->getString();
				$entry->platformChatId = $this->getString();
				$entry->buildPlatform = $this->getLInt();
				$entry->skinData = $this->getSkin();
				$entry->isTeacher = $this->getBool();
				$entry->isHost = $this->getBool();
			}else{
				$entry->uuid = $this->getUUID();
			}

			$this->entries[$i] = $entry;
		}
		if($this->type === self::TYPE_ADD){
			for($i = 0; $i < $count; ++$i){
				$this->entries[$i]->skinData->setVerified($this->getBool());
			}
		}
	}

	protected function encodePayload(){
		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			if($this->type === self::TYPE_ADD){
				$this->putUUID($entry->uuid);
				$this->putEntityUniqueId($entry->entityUniqueId);
				$this->putString($entry->username);
				$this->putString($entry->xboxUserId);
				$this->putString($entry->platformChatId);
				$this->putLInt($entry->buildPlatform);
				$this->putSkin($entry->skinData);
				$this->putBool($entry->isTeacher);
				$this->putBool($entry->isHost);
			}else{
				$this->putUUID($entry->uuid);
			}
		}
		if($this->type === self::TYPE_ADD){
			foreach($this->entries as $entry){
				$this->putBool($entry->skinData->isVerified());
			}
		}
	}

}
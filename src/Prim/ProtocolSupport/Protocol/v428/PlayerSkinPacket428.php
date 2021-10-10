<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\SkinData;
use pocketmine\network\mcpe\protocol\types\SkinImage;

class PlayerSkinPacket428 extends PlayerSkinPacket {

	public static function from(PlayerSkinPacket $pk, bool $fromClient = true) : self {
		$p = new self;
		$p->uuid = $pk->uuid;
		$p->oldSkinName = $pk->oldSkinName;
		$p->newSkinName = $pk->newSkinName;
		$p->skin = $fromClient ? SkinData428::toServer($pk->skin) : SkinData428::toClient($pk->skin);
		return $p;
	}

	public function putSkin(SkinData $skin){
		$this->putString($skin->getSkinId());
		$this->putString('');
		$this->putString($skin->getResourcePatch());
		$this->putSkinImage($skin->getSkinImage());
		$this->putLInt(count($skin->getAnimations()));
		foreach($skin->getAnimations() as $animation){
			$this->putSkinImage($animation->getImage());
			$this->putLInt($animation->getType());
			$this->putLFloat($animation->getFrames());
			$this->putLInt($animation->getExpressionType()); //new
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

}
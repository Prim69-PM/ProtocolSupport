<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\protocol\types\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\SkinData;
use pocketmine\network\mcpe\protocol\types\SkinImage;
use pocketmine\utils\UUID;

class SkinData428 extends SkinData {

	private string $skinId;
	private string $resourcePatch;
	private SkinImage $skinImage;
	/** @var SkinAnimation[] */
	private array $animations;
	private SkinImage $capeImage;
	private string $geometryData;
	private string $animationData;
	private bool $persona;
	private bool $premium;
	private bool $personaCapeOnClassic;
	private string $capeId;
	private string $fullSkinId;
	private string $armSize;
	private string $skinColor;
	/** @var PersonaSkinPiece[] */
	private array $personaPieces;
	/** @var PersonaPieceTintColor[] */
	private array $pieceTintColors;
	private bool $isVerified;

	/**
	 * @param SkinAnimation[]         $animations
	 * @param PersonaSkinPiece[]      $personaPieces
	 * @param PersonaPieceTintColor[] $pieceTintColors
	 */
    public function __construct(string $skinId, string $resourcePatch, SkinImage $skinImage, array $animations = [], SkinImage $capeImage = null, string $geometryData = "", string $animationData = "", bool $premium = false, bool $persona = false, bool $personaCapeOnClassic = false, string $capeId = "", ?string $fullSkinId = null, string $armSize = SkinData::ARM_SIZE_WIDE, string $skinColor = "", array $personaPieces = [], array $pieceTintColors = [], bool $isVerified = true){
		$this->skinId = $skinId;
		$this->resourcePatch = $resourcePatch;
		$this->skinImage = $skinImage;
		$this->animations = $animations;
		$this->capeImage = $capeImage ?? new SkinImage(0, 0, '');
		$this->geometryData = $geometryData;
		$this->animationData = $animationData;
		$this->premium = $premium;
		$this->persona = $persona;
		$this->personaCapeOnClassic = $personaCapeOnClassic;
		$this->capeId = $capeId;
		//this has to be unique or the client will do stupid things
		$this->fullSkinId = $fullSkinId ?? UUID::fromRandom()->toString();
		$this->armSize = $armSize;
		$this->skinColor = $skinColor;
		$this->personaPieces = $personaPieces;
		$this->pieceTintColors = $pieceTintColors;
		$this->isVerified = $isVerified;
    }

	public static function toClient(SkinData $d) : self {
		return new SkinData428($d->getSkinId(), $d->getResourcePatch(), $d->getSkinImage(), $d->getAnimations(), $d->getCapeImage(), $d->getGeometryData(), $d->getAnimationData(), $d->isPremium(), $d->isPersona(), $d->isPersonaCapeOnClassic(), $d->getCapeId(), $d->getFullSkinId(), $d->getArmSize(), $d->getSkinColor(), $d->getPersonaPieces(), $d->getPieceTintColors(), $d->isVerified());
	}

	public static function toServer(SkinData $d) : SkinData {
		return new SkinData($d->getSkinId(), '', $d->getResourcePatch(), $d->getSkinImage(), $d->getAnimations(), $d->getCapeImage(), $d->getGeometryData(), $d->getAnimationData(), $d->isPremium(), $d->isPersona(), $d->isPersonaCapeOnClassic(), $d->getCapeId(), $d->getFullSkinId(), $d->getArmSize(), $d->getSkinColor(), $d->getPersonaPieces(), $d->getPieceTintColors(), $d->isVerified());
	}

	public function getSkinId() : string{
		return $this->skinId;
	}

	public function getResourcePatch() : string{
		return $this->resourcePatch;
	}

	public function getSkinImage() : SkinImage{
		return $this->skinImage;
	}

	/**
	 * @return SkinAnimation[]
	 */
	public function getAnimations() : array{
		return $this->animations;
	}

	public function getCapeImage() : SkinImage{
		return $this->capeImage;
	}

	public function getGeometryData() : string{
		return $this->geometryData;
	}

	public function getAnimationData() : string{
		return $this->animationData;
	}

	public function isPersona() : bool{
		return $this->persona;
	}

	public function isPremium() : bool{
		return $this->premium;
	}

	public function isPersonaCapeOnClassic() : bool{
		return $this->personaCapeOnClassic;
	}

	public function getCapeId() : string{
		return $this->capeId;
	}

	public function getFullSkinId() : string{
		return $this->fullSkinId;
	}

	public function getArmSize() : string{
		return $this->armSize;
	}

	public function getSkinColor() : string{
		return $this->skinColor;
	}

	/**
	 * @return PersonaSkinPiece[]
	 */
	public function getPersonaPieces() : array{
		return $this->personaPieces;
	}

	/**
	 * @return PersonaPieceTintColor[]
	 */
	public function getPieceTintColors() : array{
		return $this->pieceTintColors;
	}

	public function isVerified() : bool{
		return $this->isVerified;
	}

	/**
	 * @internal
	 */
	public function setVerified(bool $verified) : void{
		$this->isVerified = $verified;
	}

}
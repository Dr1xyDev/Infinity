<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level\particle;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;

class FloatingTextParticle extends Particle
{
	protected string $text;
	protected string $title;
	protected ?int $entityId = null;
	protected bool $invisible = false;

	public function __construct(Vector3 $pos, string $text, string $title = "")
	{
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->text = $text;
		$this->title = $title;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function setText(string $text) : void
	{
		$this->text = $text;
	}

	public function getTitle() : string
	{
		return $this->title;
	}

	public function setTitle(string $title) : void
	{
		$this->title = $title;
	}

	public function isInvisible() : bool
	{
		return $this->invisible;
	}

	public function setInvisible(bool $value = true) : void
	{
		$this->invisible = $value;
	}

	public function encode()
	{
		$p = [];

		if ($this->entityId === null) {
			$this->entityId = Entity::$entityCount++;
		} else {
			$pk0 = new RemoveActorPacket();
			$pk0->entityUniqueId = $this->entityId;

			$p[] = $pk0;
		}

		if (!$this->invisible) {
			$name = $this->title . ($this->text !== "" ? "\n" . $this->text : "");

			$actorFlags = (
				(1 << Entity::DATA_FLAG_NO_AI) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
			);

			$actorMetadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $actorFlags],
				Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.01], //zero causes problems on debug builds
				Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.0],
				Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0.0],
				Entity::DATA_VARIANT => [Entity::DATA_TYPE_INT, BlockIds::AIR],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $name],
			];

			if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
				$actorMetadata[Entity::DATA_VARIANT][1] = RuntimeBlockMapping::getInstance($this->protocol)->toRuntimeId(BlockFactory::get(BlockIds::AIR)->getFullId());
			}

			$p[] = AddActorPacket::create(
				$this->entityId,
				$this->entityId,
				EntityIds::FALLING_BLOCK,
				$this->asVector3(),
				null,
				0,
				0,
				0,
				0,
				[],
				$actorMetadata,
				new PropertySyncData([], []),
				[]
			);
		}

		return $p;
	}

	public function isUseProtocol() : bool
	{
		return true;
	}
}

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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\sound\BlazeShootSound;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;

use function cos;
use function mt_rand;
use function sin;

use const M_PI;

class Fireworks extends Item
{
	/** @var float */
	public const BOOST_POWER = 1.25;

	public const TYPE_SMALL_SPHERE = 0;
	public const TYPE_HUGE_SPHERE = 1;
	public const TYPE_STAR = 2;
	public const TYPE_CREEPER_HEAD = 3;
	public const TYPE_BURST = 4;

	public const COLOR_BLACK = "\x00";
	public const COLOR_RED = "\x01";
	public const COLOR_DARK_GREEN = "\x02";
	public const COLOR_BROWN = "\x03";
	public const COLOR_BLUE = "\x04";
	public const COLOR_DARK_PURPLE = "\x05";
	public const COLOR_DARK_AQUA = "\x06";
	public const COLOR_GRAY = "\x07";
	public const COLOR_DARK_GRAY = "\x08";
	public const COLOR_PINK = "\x09";
	public const COLOR_GREEN = "\x0a";
	public const COLOR_YELLOW = "\x0b";
	public const COLOR_LIGHT_AQUA = "\x0c";
	public const COLOR_DARK_PINK = "\x0d";
	public const COLOR_GOLD = "\x0e";
	public const COLOR_WHITE = "\x0f";

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::FIREWORKS, $meta, "Fireworks");
	}

	public function getFlightDuration() : int
	{
		return $this->getExplosionsTag()->getByte("Flight", 1);
	}

	public function getRandomizedFlightDuration() : int
	{
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	public function setFlightDuration(int $duration) : void
	{
		$tag = $this->getExplosionsTag();
		$tag->setByte("Flight", $duration);
		$this->setNamedTagEntry($tag);
	}

	protected function getExplosionsTag() : CompoundTag
	{
		return $this->getNamedTag()->getCompoundTag("Fireworks") ?? new CompoundTag("Fireworks");
	}

	public function addExplosion(int $type, string $color, string $fade = "", int $flicker = 0, int $trail = 0) : void
	{
		$explosion = new CompoundTag();
		$explosion->setByte("FireworkType", $type);
		$explosion->setByteArray("FireworkColor", $color);
		$explosion->setByteArray("FireworkFade", $fade);
		$explosion->setByte("FireworkFlicker", $flicker);
		$explosion->setByte("FireworkTrail", $trail);

		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag("Explosions") ?? new ListTag("Explosions");
		$explosions->push($explosion);
		$tag->setTag($explosions);
		$this->setNamedTagEntry($tag);
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), new Vector3(0.001, 0.05, 0.001), Utils::getRandomFloat() * 360, 90);

		$entity = Entity::createEntity("FireworksRocket", $player->getLevel(), $nbt, $this);

		if ($entity instanceof Entity) {
			$this->pop();
			$entity->spawnToAll();
			return true;
		}

		return false;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool
	{
		if ($player->isGliding()) {
			$motion = new Vector3((-sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * self::BOOST_POWER), (-sin($player->pitch / 180 * M_PI) * self::BOOST_POWER), (cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * self::BOOST_POWER));

			$nbt = Entity::createBaseNBT($player, $motion->subtract(0, 0.1, 0), Utils::getRandomFloat() * 360, 90);
			$entity = Entity::createEntity("FireworksRocket", $player->getLevel(), $nbt, $this);

			if ($entity instanceof Entity) {
				$this->pop();
				$entity->spawnToAll();
				$player->setMotion($motion);
				$player->getLevel()->addSound(new BlazeShootSound($player));
			}
		}

		return true;
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			return new TranslatedItemData(ItemIds::NETHER_STAR, $this->getDamage(), $this->getName());
		}

		return parent::getItemProtocol($playerProtocol);
	}
}

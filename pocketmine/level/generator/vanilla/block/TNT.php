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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\FlintSteel;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\sound\IgniteSound;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;

use function cos;
use function sin;

use const M_PI;

class TNT extends Solid
{
	protected $id = self::TNT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "TNT";
	}

	public function getHardness() : float
	{
		return 0;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($item instanceof FlintSteel || $item->hasEnchantment(Enchantment::FIRE_ASPECT)) {
			if ($item instanceof Durable) {
				$item->applyDamage(1);
			}
			$this->ignite();
			return true;
		}

		return false;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void
	{
		if ($projectile->isOnFire()) {
			$this->ignite();
		}
	}

	public function ignite(int $fuse = 80) : void
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);

		$mot = (2.0 * Utils::getRandomFloat() - 1.0) * 2 * M_PI;
		$nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5), new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
		$nbt->setShort("Fuse", $fuse);

		$tnt = Entity::createEntity("PrimedTNT", $this->getLevel(), $nbt);

		if ($tnt !== null) {
			$tnt->spawnToAll();

			$tnt->broadcastSound(new IgniteSound($tnt));
		}
	}

	public function getFlameEncouragement() : int
	{
		return 15;
	}

	public function getFlammability() : int
	{
		return 100;
	}

	public function onIncinerate() : void
	{
		$this->ignite();
	}
}

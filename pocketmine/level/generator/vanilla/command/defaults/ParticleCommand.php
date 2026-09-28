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

namespace pocketmine\level\generator\vanilla\command\defaults;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\particle\AngryVillagerParticle;
use pocketmine\level\generator\vanilla\level\particle\BlockForceFieldParticle;
use pocketmine\level\generator\vanilla\level\particle\BubbleParticle;
use pocketmine\level\generator\vanilla\level\particle\CriticalParticle;
use pocketmine\level\generator\vanilla\level\particle\DustParticle;
use pocketmine\level\generator\vanilla\level\particle\EnchantmentTableParticle;
use pocketmine\level\generator\vanilla\level\particle\EnchantParticle;
use pocketmine\level\generator\vanilla\level\particle\EntityFlameParticle;
use pocketmine\level\generator\vanilla\level\particle\ExplodeParticle;
use pocketmine\level\generator\vanilla\level\particle\FlameParticle;
use pocketmine\level\generator\vanilla\level\particle\HappyVillagerParticle;
use pocketmine\level\generator\vanilla\level\particle\HeartParticle;
use pocketmine\level\generator\vanilla\level\particle\HugeExplodeParticle;
use pocketmine\level\generator\vanilla\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\generator\vanilla\level\particle\InkParticle;
use pocketmine\level\generator\vanilla\level\particle\InstantEnchantParticle;
use pocketmine\level\generator\vanilla\level\particle\ItemBreakParticle;
use pocketmine\level\generator\vanilla\level\particle\LavaDripParticle;
use pocketmine\level\generator\vanilla\level\particle\LavaParticle;
use pocketmine\level\generator\vanilla\level\particle\Particle;
use pocketmine\level\generator\vanilla\level\particle\PortalParticle;
use pocketmine\level\generator\vanilla\level\particle\RainSplashParticle;
use pocketmine\level\generator\vanilla\level\particle\RedstoneParticle;
use pocketmine\level\generator\vanilla\level\particle\SmokeParticle;
use pocketmine\level\generator\vanilla\level\particle\SplashParticle;
use pocketmine\level\generator\vanilla\level\particle\SporeParticle;
use pocketmine\level\generator\vanilla\level\particle\TerrainParticle;
use pocketmine\level\generator\vanilla\level\particle\WaterDripParticle;
use pocketmine\level\generator\vanilla\level\particle\WaterParticle;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function explode;
use function max;
use function microtime;
use function mt_rand;
use function strpos;
use function strtolower;

class ParticleCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.particle.description",
			"%pocketmine.command.particle.usage"
		);
		$this->setPermission("pocketmine.command.particle");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 7) {
			throw new InvalidCommandSyntaxException();
		}

		if ($sender instanceof Player) {
			$level = $sender->getLevel();
			$pos = new Vector3(
				$this->getRelativeDouble($sender->getX(), $sender, $args[1]),
				$this->getRelativeDouble($sender->getY(), $sender, $args[2], Level::Y_MIN, Level::Y_MAX),
				$this->getRelativeDouble($sender->getZ(), $sender, $args[3])
			);
		} else {
			$level = $sender->getServer()->getDefaultLevel();
			$pos = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
		}

		$name = strtolower($args[0]);

		$xd = (float) $args[4];
		$yd = (float) $args[5];
		$zd = (float) $args[6];

		$count = isset($args[7]) ? max(1, (int) $args[7]) : 1;

		$data = isset($args[8]) ? (int) $args[8] : null;

		$particle = $this->getParticle($name, $pos, $xd, $yd, $zd, $data);

		if ($particle === null) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.particle.notFound", [$name]));
			return true;
		}

		$sender->sendMessage(new TranslationContainer("commands.particle.success", [$name, $count]));

		$random = new Random((int) (microtime(true) * 1000) + mt_rand());

		for ($i = 0; $i < $count; ++$i) {
			$particle->x = $pos->x + $random->nextSignedFloat() * $xd;
			$particle->y = $pos->y + $random->nextSignedFloat() * $yd;
			$particle->z = $pos->z + $random->nextSignedFloat() * $zd;
			$level->addParticle($particle);
		}

		return true;
	}

	/**
	 * @return Particle|null
	 */
	private function getParticle(string $name, Vector3 $pos, float $xd, float $yd, float $zd, ?int $data = null)
	{
		switch ($name) {
			case "explode":
				return new ExplodeParticle($pos);
			case "hugeexplosion":
				return new HugeExplodeParticle($pos);
			case "hugeexplosionseed":
				return new HugeExplodeSeedParticle($pos);
			case "bubble":
				return new BubbleParticle($pos);
			case "splash":
				return new SplashParticle($pos);
			case "wake":
			case "water":
				return new WaterParticle($pos);
			case "crit":
				return new CriticalParticle($pos);
			case "smoke":
				return new SmokeParticle($pos, $data ?? 0);
			case "spell":
				return new EnchantParticle($pos);
			case "instantspell":
				return new InstantEnchantParticle($pos);
			case "dripwater":
				return new WaterDripParticle($pos);
			case "driplava":
				return new LavaDripParticle($pos);
			case "townaura":
			case "spore":
				return new SporeParticle($pos);
			case "portal":
				return new PortalParticle($pos);
			case "flame":
				return new FlameParticle($pos);
			case "lava":
				return new LavaParticle($pos);
			case "reddust":
				return new RedstoneParticle($pos, $data ?? 1);
			case "snowballpoof":
				return new ItemBreakParticle($pos, ItemFactory::get(Item::SNOWBALL));
			case "slime":
				return new ItemBreakParticle($pos, ItemFactory::get(Item::SLIMEBALL));
			case "itembreak":
				if ($data !== null && $data !== 0) {
					return new ItemBreakParticle($pos, ItemFactory::get($data));
				}
				break;
			case "terrain":
				if ($data !== null && $data !== 0) {
					return new TerrainParticle($pos, BlockFactory::get($data));
				}
				break;
			case "heart":
				return new HeartParticle($pos, $data ?? 0);
			case "ink":
				return new InkParticle($pos, $data ?? 0);
			case "droplet":
				return new RainSplashParticle($pos);
			case "enchantmenttable":
				return new EnchantmentTableParticle($pos);
			case "happyvillager":
				return new HappyVillagerParticle($pos);
			case "angryvillager":
				return new AngryVillagerParticle($pos);
			case "forcefield":
				return new BlockForceFieldParticle($pos, $data ?? 0);
			case "mobflame":
				return new EntityFlameParticle($pos);
		}

		if (strpos($name, "iconcrack_") === 0) {
			$d = explode("_", $name);
			if (count($d) === 3) {
				return new ItemBreakParticle($pos, ItemFactory::get((int) $d[1], (int) $d[2]));
			}
		} elseif (strpos($name, "blockcrack_") === 0) {
			$d = explode("_", $name);
			if (count($d) === 2) {
				return new TerrainParticle($pos, BlockFactory::get(((int) $d[1]) & 0xff, ((int) $d[1]) >> 12));
			}
		} elseif (strpos($name, "blockdust_") === 0) {
			$d = explode("_", $name);
			if (count($d) >= 4) {
				return new DustParticle($pos, ((int) $d[1]) & 0xff, ((int) $d[2]) & 0xff, ((int) $d[3]) & 0xff, isset($d[4]) ? ((int) $d[4]) & 0xff : 255);
			}
		}

		return null;
	}
}

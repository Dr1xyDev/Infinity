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

use pocketmine\level\generator\vanilla\event\block\SignOpenEditEvent;
use pocketmine\level\generator\vanilla\event\block\SignTextColorChangeEvent;
use pocketmine\level\generator\vanilla\item\Dye;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\OpenSignPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Sign as TileSign;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\utils\Color;

use function floor;
use function is_null;

class SignPost extends Transparent
{
	public int $signWall;

	public function __construct(int $id, int $meta, string $name, int $itemId, int $signWall)
	{
		parent::__construct($id, $meta, $name, $itemId);
		$this->signWall = $signWall;
	}

	public function getHardness() : float
	{
		return 1;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function getName() : string
	{
		return "Sign Post";
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	protected function onOpenEditor(Player $player, Vector3 $vector3) : void
	{
		$ev = new SignOpenEditEvent($this, $player, true);
		$ev->call();

		if ($ev->isCancelled()) {
			return;
		}

		$pk = new OpenSignPacket();
		$pk->frontSide = true;
		$pk->x = $vector3->getX();
		$pk->y = $vector3->getY();
		$pk->z = $vector3->getZ();
		$player->sendDataPacket($pk);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face !== Facing::DOWN) {

			if ($face === Facing::UP) {
				$this->meta = $player !== null ? (floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f) : 0;
				$this->getLevel()->setBlock($blockReplace, $this, true);
			} else {
				$this->meta = $face;
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->signWall, $this->meta), true);
			}

			$sign = Tile::createTile(Tile::SIGN, $this->getLevel(), TileSign::createNBT($this, $face, $item, $player));
			if ($player !== null && $sign instanceof TileSign) {
				if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_582) {
					$this->onOpenEditor($player, $blockReplace);
				}

				$sign->setEditorEntityRuntimeId($player->getId());
			}

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if (!$player instanceof Player) {
			return false;
		}

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileSign) {
			$color = $item instanceof Dye ? $item->getColorFromMeta() : match ($item->getId()) {
				ItemIds::BONE => new Color(0xf0, 0xf0, 0xf0),
				BlockIds::LAPIS_ORE => new Color(0x3c, 0x44, 0xaa),
				BlockIds::COCOA => new Color(0x83, 0x54, 0x32),
				default => null
			};

			if (!is_null($color)) {
				$ev = new SignTextColorChangeEvent($this, $color);
				$ev->call();

				if ($ev->isCancelled()) {
					return false;
				}

				$tile->setTextColor($ev->getColor());
				$this->level->setBlock($this, $this, true);
				return true;
			} elseif ($item->getId() == ItemIds::GLOWSTONE_DUST) {
				$tile->setGlowing(true);
				$this->level->setBlock($this, $this, true);
				return true;
			}

			if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_582) {
				$signPos = new Vector3($this->getX(), $this->getFloorY(), $this->getZ());
				if ($player->distance($signPos) > 4) {
					return false;
				}

				if ($tile->getEditorEntityRuntimeId() !== null) {
					return false;
				}

				$tile->setEditorEntityRuntimeId($player->getId());

				$this->onOpenEditor($player, $signPos);
			}
		}

		return true;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() === self::AIR) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}

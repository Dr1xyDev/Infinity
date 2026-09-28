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

namespace pocketmine\level\generator\vanilla\level\generator\feature\treedecorators;

use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Utils;
use function count;

class AttachedToLogsDecorator extends TreeDecorator{

	/**
	 * @param int[] $directions
	 */
	public function __construct(
		private float $probability,
		private BlockStateProvider $blockProvider,
		private array $directions
	) {}

	protected function type() : TreeDecoratorType {
		return TreeDecoratorType::ATTACHED_TO_LOGS;
	}

	public function place(TreeDecoratorContext $context) : void {
		$random = $context->random();

		foreach (Utils::shuffledCopy($context->logs(), $random) as $logsPos) {
			/** @var Vector3 $logsPos */
			$direction = $this->directions[$random->nextRange(0, count($this->directions) - 1)];
			$placementPos = $logsPos->getSide($direction);
			if ($random->nextFloat() <= $this->probability && $context->isAir($placementPos)) {
				$context->setBlock($placementPos, $this->blockProvider->getState($random, $placementPos));
			}
		}
	}
}

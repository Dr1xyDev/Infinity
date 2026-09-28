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

namespace pocketmine\level\generator\vanilla\level\generator\feature\configurations;

use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\treedecorators\TreeDecorator;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;

class FallenTreeConfigurationBuilder {

	public BlockStateProvider $trunkProvider;
	private IntProvider $logLength;
	/** @var TreeDecorator[] */
	private array $stumpDecorators = [];
	/** @var TreeDecorator[] */
	private array $logDecorators = [];

	public function __construct(
		BlockStateProvider $trunkProvider,
		IntProvider $logLength
	){
		$this->trunkProvider = $trunkProvider;
		$this->logLength = $logLength;
	}

	/**
	 * @param TreeDecorator[] $stumpDecorators
	 */
	public function stumpDecorators(array $stumpDecorators) : FallenTreeConfigurationBuilder{
		$this->stumpDecorators = $stumpDecorators;
		return $this;
	}

	/**
	 * @param TreeDecorator[] $logDecorators
	 */
	public function logDecorators(array $logDecorators) : FallenTreeConfigurationBuilder{
		$this->logDecorators = $logDecorators;
		return $this;
	}

	public function build() : FallenTreeConfiguration{
		return new FallenTreeConfiguration(
			$this->trunkProvider,
			$this->logLength,
			$this->stumpDecorators,
			$this->logDecorators
		);
	}
}

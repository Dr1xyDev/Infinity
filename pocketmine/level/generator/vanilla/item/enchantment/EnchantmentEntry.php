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

namespace pocketmine\level\generator\vanilla\item\enchantment;

class EnchantmentEntry
{
	/** @var Enchantment[] */
	private $enchantments;
	/** @var int */
	private $cost;
	/** @var string */
	private $randomName;

	/**
	 * @param Enchantment[] $enchantments
	 */
	public function __construct(array $enchantments, int $cost, string $randomName)
	{
		$this->enchantments = $enchantments;
		$this->cost = $cost;
		$this->randomName = $randomName;
	}

	/**
	 * @return Enchantment[]
	 */
	public function getEnchantments() : array
	{
		return $this->enchantments;
	}

	public function getCost() : int
	{
		return $this->cost;
	}

	public function getRandomName() : string
	{
		return $this->randomName;
	}
}

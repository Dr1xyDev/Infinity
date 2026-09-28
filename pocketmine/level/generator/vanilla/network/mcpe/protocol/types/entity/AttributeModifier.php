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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class AttributeModifier
{
	/**
	 * @see AttributeModifierOperation
	 * @see AttributeModifierTargetOperand
	 */
	public function __construct(
		private string $id,
		private string $name,
		private float $amount,
		private int $operation,
		private int $operand,
		private bool $serializable //???
	) {
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getAmount() : float
	{
		return $this->amount;
	}

	public function getOperation() : int
	{
		return $this->operation;
	}

	public function getOperand() : int
	{
		return $this->operand;
	}

	public function isSerializable() : bool
	{
		return $this->serializable;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$id = $in->getString();
		$name = $in->getString();
		$amount = $in->getLFloat();
		$operation = $in->getLInt();
		$operand = $in->getLInt();
		$serializable = $in->getBool();

		return new self($id, $name, $amount, $operation, $operand, $serializable);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putString($this->id);
		$out->putString($this->name);
		$out->putLFloat($this->amount);
		$out->putLInt($this->operation);
		$out->putLInt($this->operand);
		$out->putBool($this->serializable);
	}
}

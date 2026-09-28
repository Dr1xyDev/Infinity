<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | | (_| | |__| |  __/
 * |_|   \_\_|\___|_|\___|_|\__|_| |_| |_|_| |_|\__,_|\____|_| |_|\___|
 *
 * Adaptadores concretos por dimension del generador vanilla.
 *
*/

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla;

class VanillaAdapterNether extends VanillaAdapter{

	public function __construct(array $settings = []){
		parent::__construct(["dimension" => "nether"]);
	}

	public function getName() : string{
		return "VanillaNether";
	}
}

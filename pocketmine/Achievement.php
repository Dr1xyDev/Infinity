<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine;

use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;

/**
 * Handles the achievement list and a bit more
 */

abstract class Achievement{
	/**
	 * @var array[]
	 */
	public static $list = [
		/*"openInventory" => array(
			"name" => "Проверить карманы",
			"requires" => [],
		),*/
		"mineWood" => [
			"name" => "Нарубить дров",
			"requires" => [ //"openInventory",
			],
		],
		"buildWorkBench" => [
			"name" => "Рабочий стол",
			"requires" => [
				"mineWood",
			],
		],
		"buildPickaxe" => [
			"name" => "Пора в шахту",
			"requires" => [
				"buildWorkBench",
			],
		],
		"buildFurnace" => [
			"name" => "Горячая штучка",
			"requires" => [
				"buildPickaxe",
			],
		],
		"acquireIron" => [
			"name" => "Куй железо",
			"requires" => [
				"buildFurnace",
			],
		],
		"buildHoe" => [
			"name" => "Время для фермы",
			"requires" => [
				"buildWorkBench",
			],
		],
		"makeBread" => [
			"name" => "Хлеб насущный",
			"requires" => [
				"buildHoe",
			],
		],
		"bakeCake" => [
			"name" => "Это ложь",
			"requires" => [
				"buildHoe",
			],
		],
		"buildBetterPickaxe" => [
			"name" => "Обновка",
			"requires" => [
				"buildPickaxe",
			],
		],
		"buildSword" => [
			"name" => "К бою готов",
			"requires" => [
				"buildWorkBench",
			],
		],
		"diamonds" => [
			"name" => "Алмазы",
			"requires" => [
				"acquireIron",
			],
		],

	];


	public static function broadcast(Player $player, $achievementId){
		if(isset(Achievement::$list[$achievementId])){
			$translation = new TranslationContainer("chat.type.achievement", [$player->getName(), TextFormat::GREEN . Achievement::$list[$achievementId]["name"]]);
			if(Server::getInstance()->getConfigString("announce-player-achievements", true) === true){
				Server::getInstance()->broadcastMessage($translation);
			}else{
				$player->sendMessage($translation);
			}

			return true;
		}

		return false;
	}

	public static function add($achievementId, $achievementName, array $requires = []){
		if(!isset(Achievement::$list[$achievementId])){
			Achievement::$list[$achievementId] = [
				"name" => $achievementName,
				"requires" => $requires,
			];

			return true;
		}

		return false;
	}


}

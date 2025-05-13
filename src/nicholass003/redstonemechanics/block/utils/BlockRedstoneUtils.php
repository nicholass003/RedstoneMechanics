<?php

/*
 * Copyright (c) 2024 - present nicholass003
 *        _      _           _                ___   ___ ____
 *       (_)    | |         | |              / _ \ / _ \___ \
 *  _ __  _  ___| |__   ___ | | __ _ ___ ___| | | | | | |__) |
 * | '_ \| |/ __| '_ \ / _ \| |/ _` / __/ __| | | | | | |__ <
 * | | | | | (__| | | | (_) | | (_| \__ \__ \ |_| | |_| |__) |
 * |_| |_|_|\___|_| |_|\___/|_|\__,_|___/___/\___/ \___/____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  nicholass003
 * @link    https://github.com/nicholass003/
 *
 *
 */

declare(strict_types=1);

namespace nicholass003\redstonemechanics\block\utils;

use nicholass003\redstonemechanics\block\power\BlockRedstonePowerHelper;
use pocketmine\block\Block;
use pocketmine\block\Button;
use pocketmine\block\Lever;
use pocketmine\block\Redstone;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\RedstoneWire;
use pocketmine\block\SimplePressurePlate;
use pocketmine\math\Facing;
use pocketmine\world\World;
use ReflectionClass;
use function in_array;

final class BlockRedstoneUtils{

	public static function isPoweredByRedstone(Block $block) : bool{
		$reflectionClass = new ReflectionClass($block);
		return in_array("pocketmine\block\utils\PoweredByRedstoneTrait", $reflectionClass->getTraitNames(), true);
	}

	public static function isPowerComponent(Block $block) : bool{
		if($block instanceof Button || $block instanceof Lever || $block instanceof Redstone || $block instanceof RedstoneTorch || $block instanceof SimplePressurePlate){
			//TODO: support more blocks ?
			return true;
		}
		return false;
	}

	public static function isTransmissionComponent(Block $block) : bool{
		if($block instanceof RedstoneWire || $block instanceof RedstoneRepeater){
			//TODO: support more blocks ?
			return true;
		}
		return false;
	}

	public static function hasPowerSourceNearby(Block $block, array &$visitedBlocks = []) : bool{
		$pos = $block->getPosition();
		$world = $pos->getWorld();

		$hash = World::blockHash($pos->x, $pos->y, $pos->z);
		if(isset($visitedBlocks[$hash])){
			return false;
		}

		$visitedBlocks[$hash] = true;

		foreach(Facing::ALL as $face){
			$_block = $block->getSide($face);
			if(self::isPowerComponent($_block)){
				BlockRedstonePowerHelper::power($_block);
				return true;
			}

			foreach([1, -1] as $yOffset){
				foreach([Facing::NORTH, Facing::SOUTH, Facing::EAST, Facing::WEST] as $horizontal){
					$neighborPos = $pos->getSide($horizontal)->add(0, $yOffset, 0);
					$diagonalBlock = $world->getBlock($neighborPos);
					if($diagonalBlock instanceof RedstoneWire){
						$neighbors[] = $diagonalBlock;
					}elseif(self::isPowerComponent($diagonalBlock)){
						BlockRedstonePowerHelper::power($diagonalBlock);
						return true;
					}
				}
			}

			foreach($neighbors as $neighbor){
				if(!isset($visitedBlocks[World::blockHash($neighbor->getPosition()->x, $neighbor->getPosition()->y, $neighbor->getPosition()->z)])){
					if(static::hasPowerSourceNearby($neighbor, $visitedBlocks)){
						return true;
					}
				}
			}
		}
		return false;
	}
}
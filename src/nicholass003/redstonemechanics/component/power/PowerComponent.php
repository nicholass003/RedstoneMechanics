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

namespace nicholass003\redstonemechanics\component\power;

use nicholass003\redstonemechanics\block\power\BlockRedstonePowerHelper;
use nicholass003\redstonemechanics\block\transmission\BlockRedstoneTransmissionHelper;
use nicholass003\redstonemechanics\component\RedstoneComponent;
use nicholass003\redstonemechanics\RedstoneMechanics;
use pocketmine\block\Block;
use pocketmine\block\Button;
use pocketmine\block\Lever;
use pocketmine\block\Redstone;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\RedstoneWire;
use pocketmine\block\SimplePressurePlate;
use pocketmine\math\Facing;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;

class PowerComponent implements RedstoneComponent{

	public function __construct(
		private Block $block,
		private bool $active = true
	){
		if($block instanceof Lever){
			$this->active = !$block->isActivated();
		}elseif($block instanceof RedstoneTorch){
			$this->active = $block->isLit();
		}
	}

	public function scheduleUpdate(int $delayTick = 1) : void{
		$scheduler = RedstoneMechanics::getInstance()->getScheduler();
		$block = $this->getBlock();
		if($block instanceof Button){
			$scheduler->scheduleDelayedTask(new ClosureTask(function() use($block) : void{
				$pos = $block->getPosition();
				$world = $pos->getWorld();
				$block->setPressed(false);
				$world->setBlock($pos, $block);
			}), $delayTick);
		}/*
		if($this->isActivated() && $this->getSignalPower() > 0){
			$scheduler->scheduleDelayedTask(new ClosureTask(function() : void{
				BlockRedstonePowerHelper::update($this->block);
			}), $delayTick);
		}*/
	}

	public function handleComponents(int $action) : void{
		$block = $this->block;
		$scheduler = RedstoneMechanics::getInstance()->getScheduler();
		$connectedRedstone = [];
		switch($action){
			case self::ACTION_BREAK:
				foreach(Facing::ALL as $face){
					$_block = $block->getSide($face);
					if($_block instanceof RedstoneWire){
						$connectedRedstone[] = $_block;
					}
				}
		
				$scheduler->scheduleDelayedTask(new ClosureTask(
					function() use($block, $connectedRedstone) : void{
						$highPowers = [];
						$power = $this->getSignalPower();
						foreach($connectedRedstone as $redstone){
							if($redstone->getOutputSignalStrength() < $power){
								continue;
							}
							$highPowers[] = $redstone;
						}
		
						$visitedBlocks = [];
						foreach($highPowers as $_redstone){
							$_pos = $_redstone->getPosition();
							$visitedBlocks[World::blockHash($_pos->x, $_pos->y, $_pos->z)] = true;
						}
		
						if($block instanceof RedstoneWire){
							$block->setOutputSignalStrength(0);
						}
						BlockRedstoneTransmissionHelper::transmite($block, 0, $visitedBlocks);
					}
				), 1);
				break;
			case self::ACTION_PLACE:
				break;
		}
	}

	public function getBlock() : Block{
		return $this->block;
	}

	public function isActivated() : bool{
		return $this->active;
	}

	public function getSignalPower() : int{
		$power = 0;
		$block = $this->getBlock();
		if($block instanceof Button || $block instanceof Redstone || $block instanceof SimplePressurePlate){
			$power = 15;
		}elseif($block instanceof RedstoneTorch){
			if($block->isLit()){
				$power = 15;
			}
		}
		return $power;
	}
}
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

namespace nicholass003\redstonemechanics;

use nicholass003\redstonemechanics\block\power\BlockRedstonePowerHelper;
use nicholass003\redstonemechanics\block\transmission\BlockRedstoneTransmissionHelper;
use pocketmine\block\Block;
use pocketmine\block\Lever;
use pocketmine\block\RedstoneWire;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Facing;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use function max;

class EventListener implements Listener{

	public function __construct(
		private RedstoneMechanics $plugin
	){}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();

		if($block instanceof RedstoneWire){
			$connectedRedstone = [];
			foreach(Facing::ALL as $face){
				$_block = $block->getSide($face);
				if($_block instanceof RedstoneWire){
					$connectedRedstone[] = $_block;
				}
			}

			$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
				function() use($connectedRedstone, $block) : void{
					$highPowers = [];
					$power = $block->getOutputSignalStrength();
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
					$block->setOutputSignalStrength(0);
					BlockRedstoneTransmissionHelper::transmite($block, 0, $visitedBlocks);
				}
			), 1);
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void{
		foreach($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
			/** @var Block $block */
			$connectedRedstone = [];
			foreach(Facing::ALL as $face){
				$rBlock = $block->getSide($face);
				if($block instanceof RedstoneWire){
					if($rBlock instanceof RedstoneWire){
						$pos = $rBlock->getPosition();
						$hash = World::blockHash($pos->x, $pos->y, $pos->z);
						if(isset($connectedRedstone[$hash])){
							continue;
						}
						$connectedRedstone[$hash] = $rBlock;
					}
				}
			}

			$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
				function() use($connectedRedstone, $block) : void{
					if(!$block instanceof RedstoneWire){
						return;
					}

					$highPowers = [];
					$power = 0;
					foreach($connectedRedstone as $redstone){
						if($redstone->getOutputSignalStrength() < $power || $redstone->getOutputSignalStrength() === 0){
							continue;
						}
						$power = max($power, $redstone->getOutputSignalStrength());
						$highPowers[] = $redstone;
					}

					$visitedBlocks = [];
					foreach($highPowers as $_redstone){
						$_pos = $_redstone->getPosition();
						$visitedBlocks[World::blockHash($_pos->x, $_pos->y, $_pos->z)] = $_redstone->getOutputSignalStrength();
					}

					$exactPower = max(0, $power - 1);
					BlockRedstoneTransmissionHelper::transmite($block, $exactPower, $visitedBlocks);
				}
			), 1);
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$block = $event->getBlock();
			if($block instanceof Lever){
				BlockRedstonePowerHelper::update($block);
			}
			//TODO: support more blocks
		}
	}
}
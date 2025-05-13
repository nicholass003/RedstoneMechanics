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

namespace nicholass003\redstonemechanics\component\transmission;

use nicholass003\redstonemechanics\component\RedstoneComponent;
use nicholass003\redstonemechanics\RedstoneMechanics;
use pocketmine\block\Block;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneWire;

class TransmissionComponent implements RedstoneComponent{

	public function __construct(
		private Block $block,
		private bool $active = true
	){}

	public function scheduleUpdate(int $delayTick = 1) : void{
		//currently nothing to do
	}

	public function handleComponents(int $action) : void{
		$block = $this->block;
		$scheduler = RedstoneMechanics::getInstance()->getScheduler();
		$connectedRedstone = [];
		switch($action){
			case self::ACTION_BREAK:
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
		if($this->block instanceof RedstoneWire){
			$power = $this->block->getOutputSignalStrength();
		}elseif($this->block instanceof RedstoneRepeater){
			$power = 15;
		}
		//TODO: RedstoneComparator
		return $power;
	}
}
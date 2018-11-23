<?php

namespace DaPigGuy\PiggyCustomEnchantsShop\Economy;

use pocketmine\Player;

/**
 * Class BasicEconomy
 * @package DaPigGuy\PiggyCustomEnchantsShop\Economy
 */
interface BasicEconomy
{
    /**
     * @param Player $player
     * @param int    $amount
     * @return mixed
     */
    public function takeMoney(Player $player, int $amount);

    /**
     * @param Player $player
     * @return mixed
     */
    public function getMoney(Player $player);

    public function getMonetaryUnit();

}
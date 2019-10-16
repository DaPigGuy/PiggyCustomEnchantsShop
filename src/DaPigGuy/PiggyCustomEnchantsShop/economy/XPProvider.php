<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\economy;

use pocketmine\Player;

/**
 * Class XPProvider
 * @package DaPigGuy\PiggyCustomEnchantsShop\economy
 */
class XPProvider implements EconomyProvider
{
    /**
     * @param Player $player
     * @return int
     */
    public function getMoney(Player $player): int
    {
        return $player->getXpLevel();
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function giveMoney(Player $player, int $amount): void
    {
        $player->addXpLevels($amount, false);
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function takeMoney(Player $player, int $amount): void
    {
        $player->subtractXpLevels($amount);
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function setMoney(Player $player, int $amount): void
    {
        $player->setXpLevel($amount);
    }
}
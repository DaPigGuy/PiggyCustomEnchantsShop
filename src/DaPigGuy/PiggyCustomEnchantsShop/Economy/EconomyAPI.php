<?php

namespace DaPigGuy\PiggyCustomEnchantsShop\Economy;

use DaPigGuy\PiggyCustomEnchantsShop\Main;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

/**
 * Class EconomyAPI
 * @package DaPigGuy\PiggyCustomEnchantsShop\Economy
 */
class EconomyAPI implements BasicEconomy
{
    /** @var Main */
    private $plugin;
    /** @var \onebone\economyapi\EconomyAPI */
    private $economy;

    /**
     * EconomyAPI constructor.
     * @param Main   $plugin
     * @param Plugin $economy
     */
    public function __construct(Main $plugin, Plugin $economy)
    {
        $this->plugin = $plugin;
        $this->economy = $economy;
    }

    /**
     * @param Player $player
     * @param int    $amount
     * @return mixed
     */
    public function takeMoney(Player $player, int $amount)
    {
        return $this->economy->reduceMoney($player, $amount, true);
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function getMoney(Player $player)
    {
        return $this->economy->myMoney($player);
    }

    /**
     * @return mixed
     */
    public function getMonetaryUnit()
    {
        return $this->economy->getMonetaryUnit();
    }
}
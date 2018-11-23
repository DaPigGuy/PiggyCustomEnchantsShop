<?php

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\PiggyCustomEnchantsShop\Commands\CustomEnchantShopCommand;
use DaPigGuy\PiggyCustomEnchantsShop\Economy\BasicEconomy;
use DaPigGuy\PiggyCustomEnchantsShop\Economy\EconomyAPI;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\Shop;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\SignShopsManager;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\UIShopsManager;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

/**
 * Class Main
 * @package DaPigGuy\PiggyCustomEnchantsShop
 */
class Main extends PluginBase
{
    /** @var \PiggyCustomEnchants\Main */
    private $ce;
    /** @var \onebone\economyapi\EconomyAPI */
    private $economy;

    /** @var BasicEconomy */
    private $economymanager;
    /** @var SignShopsManager|UIShopsManager */
    private $shopmanager;

    public function onEnable()
    {
        if ($this->checkDependents()) {
            $this->saveDefaultConfig();
            switch ($this->getEconomy()->getName()) {
                case "EconomyAPI":
                    $this->economymanager = new EconomyAPI($this, $this->economy);
                    break;
            }
            switch ($this->getConfig()->getNested("shop-type")) {
                case "ui":
                    $this->shopmanager = new UIShopsManager($this);
                    $this->getServer()->getCommandMap()->register("piggycustomenchantsshop", new CustomEnchantShopCommand("customenchantshop", $this));
                    break;
                case "sign":
                default:
                    $this->shopmanager = new SignShopsManager($this);
                    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
                    break;
            }
            $this->shopmanager->initShops();
        }
    }

    /**
     * @return bool
     */
    public function checkDependents()
    {
        $this->ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if (is_null($this->ce)) {
            $this->getLogger()->critical("PiggyCustomEnchants is required.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (is_null($this->economy)) {
            $this->getLogger()->critical("EconomyAPI is required.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        return true;
    }

    /**
     * @return \onebone\economyapi\EconomyAPI
     */
    public function getEconomy()
    {
        return $this->economy;
    }

    /**
     * @return BasicEconomy
     */
    public function getEconomyManager()
    {
        return $this->economymanager;
    }

    /**
     * @return \PiggyCustomEnchants\Main
     */
    public function getCustomEnchants()
    {
        return $this->ce;
    }

    /**
     * @return SignShopsManager|UIShopsManager
     */
    public function getShopManager()
    {
        return $this->shopmanager;
    }

    /**
     * @param Player $player
     * @param Shop   $shop
     */
    public function buyItem(Player $player, Shop $shop)
    {
        if ($this->getCustomEnchants()->canBeEnchanted($player->getInventory()->getItemInHand(), CustomEnchants::getEnchantmentByName($shop->getEnchantment()), $shop->getEnchantLevel()) === true) {
            $this->getEconomyManager()->takeMoney($player, $shop->getPrice());
        }
        $player->getInventory()->setItemInHand($this->getCustomEnchants()->addEnchantment($player->getInventory()->getItemInHand(), $shop->getEnchantment(), $shop->getEnchantLevel(), true, $player)); //Still do it anyway to send the issue to player
    }
}
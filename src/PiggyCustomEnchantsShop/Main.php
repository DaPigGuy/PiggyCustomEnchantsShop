<?php

namespace PiggyCustomEnchantsShop;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchantsShop\Commands\CustomEnchantShopCommand;
use PiggyCustomEnchantsShop\Economy\BasicEconomy;
use PiggyCustomEnchantsShop\Shops\Shop;
use PiggyCustomEnchantsShop\Shops\SignShopsManager;
use PiggyCustomEnchantsShop\Shops\UIShopsManager;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package PiggyCustomEnchantsShop
 */
class Main extends PluginBase
{
    /** @var \PiggyCustomEnchants\Main */
    private $ce;
    /** @var \onebone\economyapi\EconomyAPI */
    private $economy;
    /** @var \jojoe77777\FormAPI\FormAPI|null */
    private $formsAPI = null;

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
                    $this->economymanager = new \PiggyCustomEnchantsShop\Economy\EconomyAPI($this, $this->economy);
                    break;
            }
            switch ($this->getConfig()->getNested("shop-type")) {
                case "ui":
                    if (($formsAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI")) !== null) {
                        $this->shopmanager = new UIShopsManager($this);
                        $this->formsAPI = $formsAPI;
                        $this->getServer()->getCommandMap()->register("customenchantshop", new CustomEnchantShopCommand("customenchantshop", $this));
                        break;
                    }
                    $this->getLogger()->error("UI Shops require FormAPI by Jojoe77777. Using SignShops instead.");
                case "sign":
                default:
                    $this->shopmanager = new SignShopsManager($this);
                    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
                    break;
            }
            $this->shopmanager->initShops();
            $this->getLogger()->info(TextFormat::GREEN . "Enabled.");
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
     * @return \jojoe77777\FormAPI\FormAPI|null
     */
    public function getFormsAPI()
    {
        return $this->formsAPI;
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
     * @param Shop $shop
     */
    public function buyItem(Player $player, Shop $shop)
    {
        if ($this->getCustomEnchants()->canBeEnchanted($player->getInventory()->getItemInHand(), CustomEnchants::getEnchantmentByName($shop->getEnchantment()), $shop->getEnchantLevel()) === true) {
            $this->getEconomyManager()->takeMoney($player, $shop->getPrice());
        }
        $player->getInventory()->setItemInHand($this->getCustomEnchants()->addEnchantment($player->getInventory()->getItemInHand(), $shop->getEnchantment(), $shop->getEnchantLevel(), true, $player)); //Still do it anyway to send the issue to player
    }
}
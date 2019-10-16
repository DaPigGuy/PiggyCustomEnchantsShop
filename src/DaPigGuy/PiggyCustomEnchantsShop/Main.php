<?php

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use DaPigGuy\PiggyCustomEnchantsShop\Commands\CustomEnchantShopCommand;
use DaPigGuy\PiggyCustomEnchantsShop\Economy\BasicEconomy;
use DaPigGuy\PiggyCustomEnchantsShop\Economy\EconomyAPI;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\Shop;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\SignShopsManager;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\UIShopsManager;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package DaPigGuy\PiggyCustomEnchantsShop
 */
class Main extends PluginBase
{
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
        if (is_null($this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants"))) {
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
        if (Utils::canBeEnchanted($player->getInventory()->getItemInHand(), CustomEnchantManager::getEnchantmentByName($shop->getEnchantment()), $shop->getEnchantLevel())) {
            $this->getEconomyManager()->takeMoney($player, $shop->getPrice());
            $item = $player->getInventory()->getItemInHand();
            $item->addEnchantment(new EnchantmentInstance(CustomEnchantManager::getEnchantmentByName($shop->getEnchantment()), $shop->getEnchantLevel()));
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage(TextFormat::GREEN . "Item has successfully been enchanted.");
            return;
        }
        $player->sendMessage(TextFormat::RED . "Enchantment could not be applied to item.");
    }
}
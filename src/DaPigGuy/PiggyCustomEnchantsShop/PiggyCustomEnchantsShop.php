<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\PiggyCustomEnchantsShop\commands\CustomEnchantShopCommand;
use DaPigGuy\PiggyCustomEnchantsShop\economy\EconomyProvider;
use DaPigGuy\PiggyCustomEnchantsShop\economy\EconomySProvider;
use DaPigGuy\PiggyCustomEnchantsShop\economy\XPProvider;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShopsManager;
use DaPigGuy\PiggyCustomEnchantsShop\tiles\ShopSignTile;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use ReflectionException;

/**
 * Class PiggyCustomEnchantsShop
 * @package DaPigGuy\PiggyCustomEnchantsShop
 */
class PiggyCustomEnchantsShop extends PluginBase
{
    /** @var EconomyProvider */
    public $economyProvider;

    /** @var UIShopsManager */
    public $uiShopManager = null;

    /**
     * @throws ReflectionException
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        if (!$this->checkDependencies()) {
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if ($this->getConfig()->getNested("shop-types.ui")) {
            $this->uiShopManager = new UIShopsManager($this);
            $this->uiShopManager->initShops();
            $this->getServer()->getCommandMap()->register("piggycustomenchantsshop", new CustomEnchantShopCommand($this, "customenchantshop", "Buy Custom Enchants", ["ceshop"]));
        }
        Tile::registerTile(ShopSignTile::class);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    /**
     * @return bool
     */
    public function checkDependencies(): bool
    {
        if ($this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants") === null) {
            $this->getLogger()->error("PiggyCustomEnchants is required.");
            return false;
        }
        switch ($this->getConfig()->getNested("economy.provider")) {
            case "xp":
                $this->economyProvider = new XPProvider();
                break;
            default:
            case "EconomyS":
                if ($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null) {
                    $this->getLogger()->error("EconomyAPI is required for your selected economy provider.");
                    return false;
                }
                $this->economyProvider = new EconomySProvider();
        }
        return true;
    }

    /**
     * @return EconomyProvider
     */
    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }

    /**
     * @return UIShopsManager|null
     */
    public function getUIShopManager(): ?UIShopsManager
    {
        return $this->uiShopManager;
    }
}
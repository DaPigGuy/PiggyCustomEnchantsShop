<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyCustomEnchantsShop\tasks\CheckUpdatesTask;
use DaPigGuy\PiggyCustomEnchantsShop\commands\CustomEnchantShopCommand;
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
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));

        if ($this->getConfig()->getNested("shop-types.ui")) {
            $this->uiShopManager = new UIShopsManager($this);
            $this->uiShopManager->initShops();
            $this->getServer()->getCommandMap()->register("piggycustomenchantsshop", new CustomEnchantShopCommand($this, "customenchantshop", "Buy Custom Enchants", ["ceshop"]));
        }
        Tile::registerTile(ShopSignTile::class);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask($this->getDescription()->getVersion(), $this->getDescription()->getCompatibleApis()[0]));
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
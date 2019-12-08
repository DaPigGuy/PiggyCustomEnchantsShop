<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use CortexPE\Commando\BaseCommand;
use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyCustomEnchantsShop\commands\CustomEnchantShopCommand;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShopsManager;
use DaPigGuy\PiggyCustomEnchantsShop\tasks\CheckUpdatesTask;
use DaPigGuy\PiggyCustomEnchantsShop\tiles\ShopSignTile;
use jojoe77777\FormAPI\Form;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use ReflectionClass;
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

    /** @var array */
    public static $vanillaEnchantmentNames = [];

    /**
     * @throws ReflectionException
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    public function onEnable(): void
    {
        if (!class_exists(BaseCommand::class)) {
            $this->getLogger()->error("Commando virion not found. Please download PiggyCustomEnchantsShop from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!class_exists(Form::class)) {
            $this->getLogger()->error("libformapi virion not found. Please download PiggyCustomEnchantsShop from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!class_exists(libPiggyEconomy::class)) {
            $this->getLogger()->error("libPiggyEconomy virion not found. Please download PiggyCustomEnchantsShop from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $reflection = new ReflectionClass(Enchantment::class);
        $lastEnchantmentId = -1;
        foreach ($reflection->getConstants() as $name => $id) {
            $lastEnchantmentId++;
            if ($id !== $lastEnchantmentId) break;
            $enchantment = Enchantment::getEnchantment($id);
            if ($enchantment instanceof Enchantment) {
                self::$vanillaEnchantmentNames[$enchantment->getName()] = ucwords(strtolower(str_replace("_", " ", $name)));
            }
        }

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
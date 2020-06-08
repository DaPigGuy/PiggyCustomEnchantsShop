<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
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
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        foreach (
            [
                "libPiggyEconomy" => libPiggyEconomy::class,
                "Commando" => BaseCommand::class,
                "libformapi" => Form::class
            ] as $virion => $class
        ) {
            if (!class_exists($class)) {
                $this->getLogger()->error($virion . " virion not found. Please download PiggyCustomEnchantsShop from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
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
            if (!PacketHooker::isRegistered()) PacketHooker::register($this);
            $this->getServer()->getCommandMap()->register("piggycustomenchantsshop", new CustomEnchantShopCommand($this, "customenchantshop", "Opens enchantment shop menu", ["ceshop"]));
        }
        Tile::registerTile(ShopSignTile::class);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask());
    }

    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }

    public function getUIShopManager(): ?UIShopsManager
    {
        return $this->uiShopManager;
    }
}
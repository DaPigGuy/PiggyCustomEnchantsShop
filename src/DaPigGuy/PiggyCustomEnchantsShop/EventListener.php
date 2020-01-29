<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchantsShop\tiles\ShopSignTile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

/**
 * Class EventListener
 * @package DaPigGuy\PiggyCustomEnchantsShop
 */
class EventListener implements Listener
{
    /** @var PiggyCustomEnchantsShop */
    private $plugin;

    /** @var array */
    private $lastTap;

    /**
     * EventListener constructor.
     * @param PiggyCustomEnchantsShop $plugin
     */
    public function __construct(PiggyCustomEnchantsShop $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        /** @var Level $level */
        $level = $block->getLevel();
        if ($level->getTile($block) instanceof ShopSignTile) {
            if (!$player->hasPermission("piggycustomenchantsshop.sign.break")) {
                $player->sendMessage(TextFormat::RED . "You are not allowed to do this.");
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        /** @var Level $level */
        $level = $block->getLevel();
        $tile = $level->getTile($block);
        if ($tile instanceof ShopSignTile) {
            if ($player->hasPermission("piggycustomenchantsshop.sign.use")) return;
            if (($enchant = $tile->getEnchantment()) === null) {
                $player->sendMessage(TextFormat::RED . "Shop sign using invalid enchantment.");
                return;
            }
            if ($this->plugin->getEconomyProvider()->getMoney($player) < $tile->getPrice()) {
                $player->sendMessage(TextFormat::RED . "Not enough money. Need " . str_replace("{amount}", (string)($tile->getPrice() - $this->plugin->getEconomyProvider()->getMoney($player)), $this->plugin->getConfig()->getNested("economy.currency-format")) . " more.");
                return;
            }
            if (!$this->plugin->getConfig()->getNested("shop-types.sign.double-tap")) {
                $tile->purchaseItem($this->plugin, $player);
                return;
            }
            if (!isset($this->lastTap[$player->getName()]) || (isset($this->lastTap[$player->getName()]) && $this->lastTap[$player->getName()] < time())) {
                $this->lastTap[$player->getName()] = time() + 10;
                $player->sendMessage(TextFormat::YELLOW . "Tap again to buy " . $enchant->getName() . " for " . str_replace("{amount}", (string)$tile->getPrice(), $this->plugin->getConfig()->getNested("economy.currency-format")) . ".");
                return;
            }
            unset($this->lastTap[$player->getName()]);
            $tile->purchaseItem($this->plugin, $player);

        } elseif ($tile instanceof Sign) {
            $lines = $tile->getText();
            /**
             * Converts signs from pre 1.3.0
             */
            if (file_exists($this->plugin->getDataFolder() . "signs/shops.yml")) {
                $oldSignShops = new Config($this->plugin->getDataFolder() . "signs/shops.yml");
                if ($oldSignShops->exists($tile->x . "," . $tile->y . "," . $tile->z . "," . $tile->getLevel()->getName())) {
                    $enchantment = CustomEnchantManager::getEnchantmentByName($lines[1]);
                    $level = (int)str_replace("Level: ", "", $lines[2]);
                    $price = (int)str_replace("Price: ", "", $lines[3]);

                    $nbt = $tile->getSpawnCompound();
                    $nbt->setInt("Enchantment", $enchantment->getId());
                    $nbt->setInt("EnchantmentLevel", $level);
                    $nbt->setInt("Price", $price);

                    /** @var ShopSignTile $newTile */
                    $newTile = Tile::createTile("ShopSignTile", $event->getBlock()->getLevel(), $nbt);
                    $newTile->setLine(0, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $level, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-one")));
                    $newTile->setLine(1, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $level, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-two")));
                    $newTile->setLine(2, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $level, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-three")));
                    $newTile->setLine(3, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $level, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-four")));
                    $tile->close();
                }
            }
        }
    }

    /**
     * @param SignChangeEvent $event
     */
    public function onSignChange(SignChangeEvent $event): void
    {
        $player = $event->getPlayer();
        /** @var Sign $tile */
        $tile = $event->getBlock()->getLevel()->getTile($event->getBlock());
        $lines = $event->getLines();
        if ($this->plugin->getConfig()->getNested("shop-types.sign.enabled")) {
            if ($lines[0] === "ce" || $lines[0] === "[CE]") {
                if (!$player->hasPermission("piggycustomenchantsshop.sign.create")) {
                    $event->setLines([TextFormat::RED . "You are not", TextFormat::RED . "allowed to do", TextFormat::RED . "this.", ""]);
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                if (($enchantment = CustomEnchantManager::getEnchantmentByName($lines[1])) === null && ($enchantment = Enchantment::getEnchantmentByName($lines[1])) === null) {
                    if (is_numeric($lines[1]) && (($enchantment = CustomEnchantManager::getEnchantment((int)$lines[1])) !== null || ($enchantment = Enchantment::getEnchantment((int)$lines[1])) !== null)) {
                        $event->setLine(1, $enchantment->getName());
                    } else {
                        $event->setLine(1, TextFormat::RED . "Invalid enchantment.");
                        if ($tile instanceof ShopSignTile) $event->setCancelled();
                        return;
                    }
                }
                if (!is_numeric($lines[2])) {
                    $event->setLine(2, TextFormat::RED . "Invalid value.");
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                if (!is_numeric($lines[3])) {
                    $event->setLine(3, TextFormat::RED . "Invalid value.");
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                $event->setCancelled();

                $newTile = $tile;
                if ($tile instanceof Sign) {
                    $nbt = $tile->getSpawnCompound();
                    $nbt->setInt("Enchantment", $enchantment->getId());
                    $nbt->setInt("EnchantmentLevel", (int)$lines[2]);
                    $nbt->setInt("Price", (int)$lines[3]);

                    /** @var ShopSignTile $newTile */
                    $newTile = Tile::createTile("ShopSignTile", $event->getBlock()->getLevel(), $nbt);
                    $tile->close();
                }
                $enchantmentName = PiggyCustomEnchantsShop::$vanillaEnchantmentNames[$enchantment->getName()] ?? $enchantment->getName();
                $newTile->setLine(0, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-one")));
                $newTile->setLine(1, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-two")));
                $newTile->setLine(2, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-three")));
                $newTile->setLine(3, str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-four")));

                $newTile->setEnchantment($enchantment);
                $newTile->setEnchantmentLevel((int)$lines[2]);
                $newTile->setPrice((int)$lines[3]);
            }
        }
    }
}
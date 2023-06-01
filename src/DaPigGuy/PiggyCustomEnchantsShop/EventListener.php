<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchantsShop\enchants\PlaceholderEnchant;
use DaPigGuy\PiggyCustomEnchantsShop\tiles\ShopSignTile;
use pocketmine\block\tile\Sign;
use pocketmine\block\utils\SignText;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
    /** @var int[] */
    private array $lastTap;

    public function __construct(private PiggyCustomEnchantsShop $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $world = $block->getPos()->getWorld();
        if ($world->getTile($block->getPos()) instanceof ShopSignTile) {
            if (!$player->hasPermission("piggycustomenchantsshop.sign.break")) {
                $player->sendMessage(TextFormat::RED . "You are not allowed to do this.");
                $event->setCancelled();
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $world = $block->getPos()->getWorld();
        $tile = $world->getTile($block->getPos());
        if ($tile instanceof ShopSignTile) {
            if (!$player->hasPermission("piggycustomenchantsshop.sign.use")) return;
            if (($enchant = $tile->getEnchantment()) instanceof PlaceholderEnchant) {
                $player->sendMessage(TextFormat::RED . "Shop sign using invalid or unregistered enchantment.");
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
            $lines = $tile->getText()->getLines();
            /**
             * Converts signs from pre 1.3.0
             */
            if (file_exists($this->plugin->getDataFolder() . "signs/shops.yml")) {
                $oldSignShops = new Config($this->plugin->getDataFolder() . "signs/shops.yml");
                if ($oldSignShops->exists($tile->getPos()->x . "," . $tile->getPos()->y . "," . $tile->getPos()->z . "," . $world->getFolderName())) {
                    $enchantment = CustomEnchantManager::getEnchantmentByName($lines[1]);
                    if ($enchantment === null) {
                        $player->sendMessage(TextFormat::RED . "Could not convert legacy sign; enchantment not found.");
                        return;
                    }
                    $enchantmentLevel = (int)str_replace("Level: ", "", $lines[2]);
                    $price = (int)str_replace("Price: ", "", $lines[3]);

                    $newTile = new ShopSignTile($world, $block->getPos());
                    $newTile->setEnchantment($enchantment);
                    $newTile->setEnchantmentLevel($enchantmentLevel);
                    $newTile->setPrice($price);
                    $newTile->setText(new SignText([
                        str_replace(["&", "{enchantment}", "{world}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $enchantmentLevel, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-one")),
                        str_replace(["&", "{enchantment}", "{world}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $enchantmentLevel, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-two")),
                        str_replace(["&", "{enchantment}", "{world}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $enchantmentLevel, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-three")),
                        str_replace(["&", "{enchantment}", "{world}", "{price}"], [TextFormat::ESCAPE, ucfirst($enchantment->getName()), $enchantmentLevel, $price], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-four"))
                    ]));
                    $world->addTile($newTile);
                    $tile->close();
                }
            }
        }
    }

    public function onSignChange(SignChangeEvent $event): void
    {
        $player = $event->getPlayer();
        $level = $event->getBlock()->getPos()->getWorld();
        /** @var Sign $tile */
        $tile = $level->getTile($event->getBlock()->getPos());
        $lines = $event->getNewText()->getLines();
        if ($this->plugin->getConfig()->getNested("shop-types.sign.enabled")) {
            $lines[0] = strtolower($lines[0]);
            if ($lines[0] === "ce" || $lines[0] === "[ce]") {
                if (!$player->hasPermission("piggycustomenchantsshop.sign.create")) {
                    $event->setNewText(new SignText([TextFormat::RED . "You are not", TextFormat::RED . "allowed to do", TextFormat::RED . "this.", ""]));
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                if (($enchantment = CustomEnchantManager::getEnchantmentByName($lines[1])) === null && ($enchantment = Enchantment::fromString($lines[1])) === null) {
                    if (!is_numeric($lines[1]) || (($enchantment = CustomEnchantManager::getEnchantment((int)$lines[1])) === null && ($enchantment = Enchantment::get((int)$lines[1])) === null)) {
                        $event->setNewText(new SignText([$lines[0], TextFormat::RED . "Invalid enchantment.", $lines[2], $lines[3]]));
                        if ($tile instanceof ShopSignTile) $event->setCancelled();
                        return;
                    }
                }
                if (!is_numeric($lines[2])) {
                    $event->setNewText(new SignText([$lines[0], $lines[1], TextFormat::RED . "Invalid value.", $lines[3]]));
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                if (!is_numeric($lines[3])) {
                    $event->setNewText(new SignText([$lines[0], $lines[1], $lines[2], TextFormat::RED . "Invalid value."]));
                    if ($tile instanceof ShopSignTile) $event->setCancelled();
                    return;
                }
                $event->setCancelled();

                $newTile = $tile;
                if ($tile instanceof Sign) {
                    $newTile = new ShopSignTile($tile->getPos()->getWorld(), $tile->getPos());
                    $tile->getPos()->getWorld()->addTile($newTile);
                    $tile->close();
                }
                $enchantmentName = PiggyCustomEnchantsShop::$vanillaEnchantmentNames[$enchantment->getName()] ?? $enchantment->getName();
                $newTile->setText(new SignText([
                    str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-one")),
                    str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-two")),
                    str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-three")),
                    str_replace(["&", "{enchantment}", "{level}", "{price}"], [TextFormat::ESCAPE, $enchantmentName, $lines[2], $lines[3]], $this->plugin->getConfig()->getNested("shop-types.sign.format.line-four"))
                ]));

                $newTile->setEnchantment($enchantment);
                $newTile->setEnchantmentLevel((int)$lines[2]);
                $newTile->setPrice((int)$lines[3]);
            }
        }
    }
}
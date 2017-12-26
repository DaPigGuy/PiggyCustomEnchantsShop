<?php

namespace PiggyCustomEnchantsShop;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\block\SignPost;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class EventListener
 * @package PiggyCustomEnchantsShop
 */
class EventListener implements Listener
{
    private $plugin;

    private $tap;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param BlockBreakEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if ($block instanceof SignPost) {
            if (!is_null($shop = $this->plugin->getProvider()->getShop($block->x, $block->y, $block->z))) {
                if (!$player->hasPermission("piggycustomenchantsshop.breaksign")) {
                    $player->sendMessage(TextFormat::RED . "You are not allowed to do this.");
                    $event->setCancelled();
                } else {
                    $this->plugin->getProvider()->removeShop($shop);
                }
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (!is_null($shop = $this->plugin->getProvider()->getShop($block->x, $block->y, $block->z))) {
            if ($player->hasPermission("piggycustomenchantsshop.usesign")) {
                if ($this->plugin->getEconomyManager()->getMoney($player) >= $shop->getPrice()) {
                    if (!$this->plugin->getConfig()->getNested("double-tap")) {
                        $this->buyItem($player, $shop);
                    } else {
                        if (!isset($this->tap[$player->getLowerCaseName()]) || (isset($this->tap[$player->getLowerCaseName()]) && $this->tap[$player->getLowerCaseName()] <= time())) {
                            $this->tap[$player->getLowerCaseName()] = time() + 10;
                            $player->sendMessage(TextFormat::YELLOW . "Tap again to buy " . $shop->getEnchantment() . " for " . $this->plugin->getEconomyManager()->getMonetaryUnit() . $shop->getPrice() . ".");
                        } else {
                            $this->buyItem($player, $shop);
                        }
                    }
                } else {
                    $player->sendMessage(TextFormat::RED . "Not enough money. Need " . $this->plugin->getEconomyManager()->getMonetaryUnit() . ($shop->getPrice() - $this->plugin->getEconomyManager()->getMoney($player)) . " more.");
                }
            }
        }
    }

    /**
     * @param SignChangeEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     * @return bool
     */
    public function onSignChange(SignChangeEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if ($block instanceof SignPost) {
            $text = $event->getLines();
            switch ($text[0]) {
                case "[CE]":
                case "ce":
                    if (!$player->hasPermission("piggycustomenchantsshop.makesign")) {
                        $event->setLines([TextFormat::RED . "You are not allowed", "to do this.", "", ""]);
                        return false;
                    }
                    if (is_null($enchantment = \PiggyCustomEnchants\CustomEnchants\CustomEnchants::getEnchantmentByName($text[1]))) {
                        if (is_numeric($text[1]) && is_null($enchantment = \PiggyCustomEnchants\CustomEnchants\CustomEnchants::getEnchantment($text[1])) !== true) {
                            $event->setLine(1, $enchantment->getName());
                        } else {
                            $event->setLine(1, TextFormat::RED . "Invalid enchantment.");
                            return false;
                        }
                    }
                    if (!is_numeric($text[2])) {
                        $event->setLine(2, TextFormat::RED . "Missing/Invalid value.");
                        return false;
                    }
                    if (!is_numeric($text[3])) {
                        $event->setLine(3, TextFormat::RED . "Missing/Invalid value.");
                        return false;
                    }
                    $event->setLine(0, "[" . TextFormat::GREEN . "CE" . TextFormat::RESET . "]");
                    $event->setLine(1, ucfirst($text[1]));
                    $event->setLine(2, "Level: " . $text[2]);
                    $event->setLine(3, "Price: " . $text[3]);
                    $this->plugin->getProvider()->addShop(new Shop($block->x, $block->y, $block->z, $enchantment->getName(), $text[2], $text[3]));
                    break;
            }
        }
        return true;
    }

    /**
     * @param Player $player
     * @param Shop $shop
     */
    public function buyItem(Player $player, Shop $shop)
    {
        if ($this->plugin->ce->canBeEnchanted($player->getInventory()->getItemInHand(), CustomEnchants::getEnchantmentByName($shop->getEnchantment()), $shop->getLevel()) === true) {
            $this->plugin->getEconomyManager()->takeMoney($player, $shop->getPrice());
        }
        $player->getInventory()->setItemInHand($this->plugin->ce->addEnchantment($player->getInventory()->getItemInHand(), $shop->getEnchantment(), $shop->getLevel(), true, $player)); //Still do it anyway to send the issue to player
        if (isset($this->tap[$player->getLowerCaseName()])) {
            unset($this->tap[$player->getLowerCaseName()]);
        }
    }
}

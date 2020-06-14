<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\commands;

use CortexPE\Commando\BaseCommand;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use DaPigGuy\PiggyCustomEnchantsShop\commands\subcommands\AddSubCommand;
use DaPigGuy\PiggyCustomEnchantsShop\enchants\PlaceholderEnchant;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShop;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShopsManager;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;

class CustomEnchantShopCommand extends BaseCommand
{
    /** @var PiggyCustomEnchantsShop */
    protected $plugin;

    /**
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("command.use-in-game"));
            return;
        }
        $this->sendEnchantsForm($sender);
    }

    public function sendEnchantsForm(Player $player): void
    {
        /** @var UIShopsManager $shopManager */
        $shopManager = $this->plugin->getUIShopManager();
        $shops = array_filter($shopManager->getShops(), function (UIShop $shop) {
            return !$shop->getEnchantment() instanceof PlaceholderEnchant;
        });
        if (count($shops) === 0) {
            $player->sendMessage($this->plugin->getMessage("menu.no-shop-entries"));
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($shops) {
            if ($data !== null) {
                $selectedShop = $shops[array_keys($shops)[$data]];
                $form = new ModalForm(function (Player $player, ?bool $data) use ($selectedShop) {
                    if ($data !== null) {
                        if ($data) {
                            if (($limit = $this->plugin->getConfig()->get("enchant-limit", -1)) !== -1 && count($player->getInventory()->getItemInHand()->getEnchantments()) >= $limit) {
                                $player->sendMessage($this->plugin->getMessage("menu.item.enchantment-limit", ["{LIMIT}" => $limit]));
                                return;
                            }
                            if (!Utils::canBeEnchanted($player->getInventory()->getItemInHand(), $selectedShop->getEnchantment(), $selectedShop->getEnchantmentLevel())) {
                                $player->sendMessage($this->plugin->getMessage("menu.item.cant-be-enchanted"));
                                return;
                            }
                            if ($this->plugin->getEconomyProvider()->getMoney($player) < $selectedShop->getPrice()) {
                                $player->sendMessage($this->plugin->getMessage("menu.item.not-enough-money", ["{AMOUNT}" => str_replace("{amount}", (string)($selectedShop->getPrice() - $this->plugin->getEconomyProvider()->getMoney($player)), $this->plugin->getConfig()->getNested("economy.currency-format"))]));
                                return;
                            }
                            $this->plugin->getEconomyProvider()->takeMoney($player, $selectedShop->getPrice());
                            $item = $player->getInventory()->getItemInHand();
                            $item->addEnchantment(new EnchantmentInstance($selectedShop->getEnchantment(), $selectedShop->getEnchantmentLevel()));
                            $player->getInventory()->setItemInHand($item);
                            $player->sendMessage($this->plugin->getMessage("menu.item.success"));
                        } else {
                            $this->sendEnchantsForm($player);
                        }
                    }
                });
                $form->setTitle($this->plugin->getMessage("menu.confirmation.title"));
                $form->setContent($this->plugin->getMessage("menu.confirmation.content", ["{ENCHANTMENT}" => $selectedShop->getEnchantment()->getName(), "{LEVEL}" => Utils::getRomanNumeral($selectedShop->getEnchantmentLevel()), "{AMOUNT}" => str_replace("{amount}", (string)$selectedShop->getPrice(), $this->plugin->getConfig()->getNested("economy.currency-format"))]));
                $form->setButton1("Yes");
                $form->setButton2("No");
                $player->sendForm($form);
            }
        });
        $form->setTitle($this->plugin->getMessage("menu.title"));
        foreach ($shops as $shop) {
            $form->addButton($this->plugin->getMessage("menu.button", ["{ENCHANTMENT}" => $shop->getEnchantment()->getName(), "{LEVEL}" => Utils::getRomanNumeral($shop->getEnchantmentLevel())]));
        }
        $player->sendForm($form);
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchantsshop.command.ceshop.use");
        $this->registerSubCommand(new AddSubCommand($this->plugin, "add", "Opens enchantment shop configuration menu"));
    }
}
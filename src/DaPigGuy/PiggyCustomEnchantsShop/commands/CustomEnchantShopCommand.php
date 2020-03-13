<?php
declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\SubCommandCollision;
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
use pocketmine\utils\TextFormat;

class CustomEnchantShopCommand extends BaseCommand
{
    /** @var PiggyCustomEnchantsShop */
    private $plugin;

    public function __construct(PiggyCustomEnchantsShop $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
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
            $player->sendMessage(TextFormat::RED . "There are no existing shop entries.");
            return;
        }
        $form = new SimpleForm(function (Player $player, ?int $data) use ($shops) {
            if ($data !== null) {
                $selectedShop = $shops[array_keys($shops)[$data]];
                $form = new ModalForm(function (Player $player, ?bool $data) use ($selectedShop) {
                    if ($data !== null) {
                        if ($data) {
                            if (Utils::canBeEnchanted($player->getInventory()->getItemInHand(), $selectedShop->getEnchantment(), $selectedShop->getEnchantmentLevel())) {
                                if ($this->plugin->getEconomyProvider()->getMoney($player) < $selectedShop->getPrice()) {
                                    $player->sendMessage(TextFormat::RED . "Not enough money. Need " . str_replace("{amount}", (string)($selectedShop->getPrice() - $this->plugin->getEconomyProvider()->getMoney($player)), $this->plugin->getConfig()->getNested("economy.currency-format")) . " more.");
                                    return;
                                }
                                $this->plugin->getEconomyProvider()->takeMoney($player, $selectedShop->getPrice());
                                $item = $player->getInventory()->getItemInHand();
                                $item->addEnchantment(new EnchantmentInstance($selectedShop->getEnchantment(), $selectedShop->getEnchantmentLevel()));
                                $player->getInventory()->setItemInHand($item);
                                $player->sendMessage(TextFormat::GREEN . "Item has successfully been enchanted.");
                                return;
                            }
                            $player->sendMessage(TextFormat::RED . "Enchantment could not be applied to item.");
                        } else {
                            $this->sendEnchantsForm($player);
                        }
                    }
                });
                $form->setTitle(TextFormat::GREEN . "Purchase Confirmation");
                $form->setContent("Are you sure you would like to buy the enchantment " . $selectedShop->getEnchantment()->getName() . " " . Utils::getRomanNumeral($selectedShop->getEnchantmentLevel()) . " for " . str_replace("{amount}", (string)$selectedShop->getPrice(), $this->plugin->getConfig()->getNested("economy.currency-format")) . "?");
                $form->setButton1("Yes");
                $form->setButton2("No");
                $player->sendForm($form);
            }
        });
        $form->setTitle(TextFormat::GREEN . "Custom Enchant Shop");
        foreach ($shops as $shop) {
            $form->addButton($shop->getEnchantment()->getName() . " " . Utils::getRomanNumeral($shop->getEnchantmentLevel()));
        }
        $player->sendForm($form);
    }

    /**
     * @throws SubCommandCollision
     */
    public function prepare(): void
    {
        $this->setPermission("piggycustomenchantsshop.command.ceshop.use");
        $this->registerSubCommand(new AddSubCommand($this->plugin, "add", "Add a shop entry to PiggyCustomEnchantsShop"));
    }
}
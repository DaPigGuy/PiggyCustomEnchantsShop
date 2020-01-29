<?php
declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\SubCommandCollision;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use DaPigGuy\PiggyCustomEnchantsShop\commands\subcommands\AddSubCommand;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CustomEnchantShopCommand
 * @package DaPigGuy\PiggyCustomEnchantsShop\commands
 */
class CustomEnchantShopCommand extends BaseCommand
{
    /** @var PiggyCustomEnchantsShop */
    private $plugin;

    /**
     * CustomEnchantShopCommand constructor.
     * @param PiggyCustomEnchantsShop $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(PiggyCustomEnchantsShop $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
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

    /**
     * @param Player $player
     */
    public function sendEnchantsForm(Player $player): void
    {
        $shops = $this->plugin->getUIShopManager()->getShops();
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
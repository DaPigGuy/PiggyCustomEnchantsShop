<?php

namespace DaPigGuy\PiggyCustomEnchantsShop\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShop;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class AddSubCommand
 * @package DaPigGuy\PiggyCustomEnchantsShop\commands\subcommands
 */
class AddSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchantsShop */
    private $plugin;

    /**
     * AddSubCommand constructor.
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
        if (count($args) >= 3) {
            if (($enchantment = CustomEnchantManager::getEnchantmentByName($args["enchantment"])) === null && ($enchantment = CustomEnchantManager::getEnchantment((int)$args["enchantment"])) === null) {
                $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
                return;
            }
            if (!is_numeric($args["level"])) {
                $sender->sendMessage(TextFormat::RED . "Level must be numerical.");
                return;
            }
            if (!is_numeric($args["price"])) {
                $sender->sendMessage(TextFormat::RED . "Price must be numerical.");
                return;
            }
            $this->plugin->getUIShopManager()->addShop(new UIShop($this->plugin->getUIShopManager()->getNextId(), CustomEnchantManager::getEnchantmentByName($args["enchantment"]) ?? CustomEnchantManager::getEnchantment((int)$args["enchantment"]), (int)$args["level"], (int)$args["price"]));
            $sender->sendMessage(TextFormat::GREEN . "Shop entry has been created.");
        } else {
            if ($sender instanceof Player) {
                $form = new CustomForm(function (Player $player, ?array $data): void {
                    if ($data !== null) {
                        if (($enchantment = CustomEnchantManager::getEnchantmentByName($data[0])) === null && ($enchantment = CustomEnchantManager::getEnchantment((int)$data[0])) === null) {
                            $player->sendMessage(TextFormat::RED . "Invalid enchantment.");
                            return;
                        }
                        if (!is_numeric($data[1])) {
                            $player->sendMessage(TextFormat::RED . "Level must be numerical.");
                            return;
                        }
                        if (!is_numeric($data[2])) {
                            $player->sendMessage(TextFormat::RED . "Price must be numerical.");
                            return;
                        }
                        $this->plugin->getUIShopManager()->addShop(new UIShop($this->plugin->getUIShopManager()->getNextId(), CustomEnchantManager::getEnchantmentByName($data[0]) ?? CustomEnchantManager::getEnchantment((int)$data[0]), (int)$data[1], (int)$data[2]));
                        $player->sendMessage(TextFormat::GREEN . "Shop entry has been created.");

                    }
                });
                $form->setTitle(TextFormat::GREEN . "Add Shop Entry");
                $form->addInput("Enchantment", "", empty($args["enchantment"]) ? null : $args["enchantment"]);
                $form->addSlider("Level", 1, 5, 1, empty($args["level"]) ? 1 : $args["level"]);
                $form->addInput("Price", "", empty($args["price"]) ? null : $args["price"]);
                $sender->sendForm($form);
                return;
            }
            $sender->sendMessage("Usage: /ceshop add <enchantment> <level> <price>");
        }
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("piggycustomenchantsshop.command.ceshop.add");
        $this->registerArgument(0, new RawStringArgument("enchantment", true));
        $this->registerArgument(1, new RawStringArgument("level", true));
        $this->registerArgument(2, new RawStringArgument("price", true));
    }
}
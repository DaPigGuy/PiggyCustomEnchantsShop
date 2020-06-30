<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShop;
use DaPigGuy\PiggyCustomEnchantsShop\shops\UIShopsManager;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\player\Player;

class AddSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchantsShop */
    protected $plugin;

    /**
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var UIShopsManager $shopManager */
        $shopManager = $this->plugin->getUIShopManager();
        if (count($args) >= 3) {
            if (
                ($enchantment = CustomEnchantManager::getEnchantmentByName($args["enchantment"])) === null &&
                ($enchantment = Enchantment::fromString($args["enchantment"])) === null &&
                (!is_numeric($args["enchantment"]) || (($enchantment = CustomEnchantManager::getEnchantment((int)$args["enchantment"])) === null && ($enchantment = Enchantment::getEnchantment((int)$args["enchantment"])) === null))
            ) {
                $sender->sendMessage($this->plugin->getMessage("menu.add.invalid-enchantment"));
                return;
            }
            if (!is_numeric($args["level"])) {
                $sender->sendMessage($this->plugin->getMessage("menu.add.level-not-numerical"));
                return;
            }
            if (!is_numeric($args["price"])) {
                $sender->sendMessage($this->plugin->getMessage("menu.add.price-not-numerical"));
                return;
            }
            $shopManager->addShop(new UIShop($shopManager->getNextId(), $enchantment, (int)$args["level"], (int)$args["price"]));
            $sender->sendMessage($this->plugin->getMessage("menu.add.entry-created"));
        } else {
            if ($sender instanceof Player) {
                $form = new CustomForm(function (Player $player, ?array $data) use ($shopManager): void {
                    if ($data !== null) {
                        if (
                            ($enchantment = CustomEnchantManager::getEnchantmentByName($data[0])) === null &&
                            ($enchantment = Enchantment::fromString($data[0])) === null &&
                            (!is_numeric($data[0]) || (($enchantment = CustomEnchantManager::getEnchantment((int)$data[0])) === null && ($enchantment = Enchantment::get((int)$data[0])) === null))
                        ) {
                            $player->sendMessage($this->plugin->getMessage("menu.add.invalid-enchantment"));
                            return;
                        }
                        if (!is_numeric($data[1])) {
                            $player->sendMessage($this->plugin->getMessage("menu.add.level-not-numerical"));
                            return;
                        }
                        if (!is_numeric($data[2])) {
                            $player->sendMessage($this->plugin->getMessage("menu.add.price-not-numerical"));
                            return;
                        }
                        $shopManager->addShop(new UIShop($shopManager->getNextId(), $enchantment, (int)$data[1], (int)$data[2]));
                        $player->sendMessage($this->plugin->getMessage("menu.add.entry-created"));
                    }
                });
                $form->setTitle($this->plugin->getMessage("menu.add.title"));
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
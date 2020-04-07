# PiggyCustomEnchantsShop [![Poggit-CI](https://poggit.pmmp.io/shield.dl/PiggyCustomEnchantsShop)](https://poggit.pmmp.io/p/PiggyCustomEnchantsShop) [![Discord](https://img.shields.io/discord/330850307607363585?logo=discord)](https://discord.gg/qmnDsSD)

PiggyCustomEnchantsShop is an add-on the the PiggyCustomEnchants plugin, adding shops.

## Prerequisites
* Basic knowledge on how to install plugins from Poggit Releases and/or Poggit CI
* PMMP 3.2.0 or greater
* Economy type supported by libPiggyEconomy:
  * [EconomyAPI](https://github.com/onebone/EconomyS/tree/3.x/EconomyAPI) by onebone
  * [MultiEconomy](https://github.com/TwistedAsylumMC/MultiEconomy) by TwistedAsylumMC
  * PMMP Player EXP

## Installation & Setup
1. Install the plugin from Poggit.
2. (Optional) Setup your economy provider. If using EconomyAPI, this step can be skipped. Otherwise, change `economy.provider` to the name of the economy plugin being used, or `xp` for PMMP Player EXP. If using MultiEconomy, create `economy.multieconomy-currency` with the value being your preferred currency type.
3. (Optional) Configure formatting of your sign shops under `shop-types.sign.format`
4. Start your server.
5. **Sign Shops**: Place a sign down & create a sign shop with the following format:
    ![SignShopFormat](https://piggydocs.aericio.net/assets/img/piggycustomenchantsshop/pces-ss_visual.png)

   **UI Shops**: Use `/ceshop add` and enter requested information.
6. You're done!

## Commands
| Command | Description | Permissions | Aliases |
| --- | --- | --- | --- |
| `/customenchantshop` | Opens enchantment shop menu | `piggycustomenchantsshop.command.ceshop.use` |  `/ceshop` |
| `/customenchantshop add` | Opens enchantment shop configuration menu | `piggycustomenchantsshop.command.ceshop.add` | `/ceshop add` |

## Permissions
| Permissions | Description | Default |
| --- | --- | --- |
| `piggycustomenchantsshop` | Allows usage of all PiggyCustomEnchantsShop features | `op` |
| `piggycustomenchantsshop.command` | Allow usage of all PiggyCustomEnchantsShop commands | `op` |
| `piggycustomenchantsshop.command.ceshop` | Allow usage of both /ceshop & /ceshop add| `op` |
| `piggycustomenchantsshop.command.ceshop.add` | Allow usage of /ceshop add| `op` |
| `piggycustomenchantsshop.command.ceshop.use` | Allow usage of /ceshop| `true` |
| `piggycustomenchantsshop.sign` | Allows usage of all sign shop features | `op` |
| `piggycustomenchantsshop.sign.break` | Allows breaking of sign shops | `op` |
| `piggycustomenchantsshop.sign.create` | Allows creation of sign shops | `op` |
| `piggycustomenchantsshop.sign.use` | Allows usage of sign shops | `true` |

## Issue Reporting
* If you experience an unexpected non-crash behavior with PiggyCustomEnchantsShop, click [here](https://github.com/DaPigGuy/PiggyCustomEnchantsShop/issues/new?assignees=DaPigGuy&labels=bug&template=bug_report.md&title=).
* If you experience a crash in PiggyCustomEnchantsShop, click [here](https://github.com/DaPigGuy/PiggyCustomEnchantsShop/issues/new?assignees=DaPigGuy&labels=bug&template=crash.md&title=).
* If you would like to suggest a feature to be added to PiggyCustomEnchantsShop, click [here](https://github.com/DaPigGuy/PiggyCustomEnchantsShop/issues/new?assignees=DaPigGuy&labels=suggestion&template=suggestion.md&title=).
* If you require support, please join our discord server [here](https://discord.gg/qmnDsSD).
* Do not file any issues related to outdated API version; we will resolve such issues as soon as possible.
* We do not support any spoons of PocketMine-MP. Anything to do with spoons (Issues or PRs) will be ignored.
  * This includes plugins that modify PocketMine-MP's behavior directly, such as TeaSpoon.

## Additional Information
* We do not support any spoons. Anything to do with spoons (Issues or PRs) will be ignored.
* We are using [Commando](https://github.com/CortexPE/Commando), [libFormAPI](https://github.com/jojoe77777/FormAPI), [libPiggyEconomy](https://github.com/DaPigGuy/libPiggyEconomy) virions.
    * **Unless you know what you are doing, use the pre-compiled phar from [Poggit-CI](https://poggit.pmmp.io/ci/DaPigGuy/PiggyCustomEnchantsShop/~) and not GitHub.**
    * If you wish to run it via source, check out [DEVirion](https://github.com/poggit/devirion).
* Detailed Installation Guide available at [PiggyDocs.](https://piggydocs.aericio.net/PiggyCustomEnchantsShop.html)
* Check out our [Discord Server](https://discord.gg/qmnDsSD) for additional plugin support.
=== Invelity GLS online connect ===
Author: Invelity s.r.o.
Author URI: https://www.invelity.com
Tags: GLS, shipping, WooCommerce
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=38W6PN4WHLK32
Requires at least: 5.8.1
Tested up to: 6.0.3
Stable tag: 5.9.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin Invelity GLS online connect je vytvorený pre obchodníkov na platforme Woocommerce ktorý potrebuju automaticky exportovat údaje o objednávkach do systému GLS online za ú?elom vytla?enia doru?ovacích lístkov.

== Description ==
Plugin Vám umožnuje jednoduchý prenos údajov o objednávkach z Wordpress adminu priamo do systému GLS online bez exportovania/importovania akýchkolvek súborov pomocou API volaní.
Po exportovaní údajov sa prihlásite do služby GLS online po výbere možnosti importovania údajov budete vidiet pripravené štítky z Vami exportovaných objednávok

== Installation ==

Táto sekcia popisuje inštaláciu pluginu.

1. Stiahnite plugin a nahrajte ho priamo cez FTP (`/wp-content/plugins/invelity-gls-online-connect) alebo plugin stiahnite priamo z Wordpress repozitára.
2. Aktivujte plugin cez 'Plugins' obrazovku vo WordPress.
3. V hlavnom menu (ľavý sidebar) uvidíte položku "Invelity plugins" a jej pod-položku "Invelity GLS online connect".
4. Vpíšte všetky potrebné údaje vrátane údajov ktoré ste dostali priamo od služby GLS.
5. Po správnom nastavení pluginu môžete pristúpiť k exportovaniu údajov o objednávkach do GLS.
6. Vo výpise objednávok zaškrtnite objednávky ktoré chcete exportovať do GLS. Z drop-down zvoľte možnosť "Export GLS online connect"
7. V systéme gls online zvoľte import štítkov a pokračujte ďalšími procesmi v GLS online (vygenerovanie, úprava, tlač ...)

== Frequently Asked Questions ==

= Potrebujem ešte niečo pre správnu funkcionalitu tohto pluginu? =

Áno, potrebujete mať dohodnutú spoluprácu s GLS a pristupové údaje na https://online.gls-slovakia.sk/index.php

= Je tento plugin zdarma? =

Áno. Plugin ponúkame úplne zdarma v plnej verzii bez akýchkoľvek obmedzení, avšak bez akejkoľvek garancie podpory alebo funkcionality.
Podporu nad rámec hlavnej funkcionality pluginu ako jeho úpravy, nastavenia alebo inštálácie poskytujeme za poplatok po dohode.
V prípade záujmu nás kontaktujte na https://www.invelity.com/ alebo priamo na mike@invelity.com

== Screenshots ==

1. Konfigurácia pluginu
/assets/screenshot-1.png
2. Používanie pluginu
/assets/screenshot-2.png
3. Používanie GLS online

== Change log ==

= 1.0.0 =
* Plugin Release

= 1.0.0 - 1.1.3 =
* Various fixes and tweaking

= 1.1.4 =
* Licence removal and plugin code refactoring

= 1.1.6 =
* Added suppoert for GLS CZ

= 1.1.7 - 1.1.8 =
* Fixes for GLS CZ

= 1.1.9 =
* Pcount fix

= 1.2 =
* Added backwards compatibility for $order->get_payment_method();

= 1.2.1 =
*Fixed remote data call on servers that block it

= 1.2.2 =
*Added option to select GLS services

= 1.2.3 =
*Services name change

= 1.2.4 =
*Compatibility with INVELITY GLS PARCELSHOP plugin

{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style>
    .bold_text{
        font-size: 18px;
        font-weight: bold;
    }
    .panel{
        font-size: 15px;
    }
    .chosen-container-multi .chosen-choices li.search-choice .search-choice-close {
        position: absolute;
        top: 4px;
        right: 3px;
        display: block;
        width: 12px;
        height: 12px;
        background: url('../modules/productionshipping/views/img/chosen-sprite.png') -42px 1px no-repeat;
        font-size: 1px;
    }
    form#module_form .chosen-container-multi{
        min-width: 500px;
    }
    select#PRODUCTION_ORDER_STATES {
        min-width: 400px;
        min-height: 400px;
    }
</style>
<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Production & Shipping' mod='productionshipping'}</h3>
    <div><span class="bold_text">{l s='If you want to receive a copy of a sent "Manufacturer Report" you should setup  Admin Email.' mod='productionshipping'}</span></div>
    <div><span class="bold_text">{l s='To setup an auto sending of a "Manufacturer Report" you can add script' mod='productionshipping'}</span> <i>"send_manuf_report.php"</i> <span class="bold_text">{l s='into your cron jobs.' mod='productionshipping'}</span></div>
    <div><span class="bold_text">{l s='If you want to receive cron.log, also, you should setup Developer Email and add' mod='productionshipping'}</span> <i>"send_reports.sh"</i> <span class="bold_text">{l s='in your cron jobs, instead of' mod='productionshipping'}</span> <i>"send_manuf_report.php"</i>.</div>
    <div>&nbsp;</div>
    <div>For example:</div>
    <div> "* 6 * * 1 php /path_to_module_folder/send_manuf_report.php" - will send "Manufacturer Report" every Monday at 6AM"</div>
    <div> "* 6 * * 1 sh /path_to_module_folder/send_reports.sh" - will send "Manufacturer Report" and "cron.log" every Monday at 6AM"</div>
</div>


<table style="width: 100%;">
    <tr>
        <td style="width: 100%">
            {if $logo_path|escape:'html':'UTF-8'}<img src="{$logo_path|escape:'html':'UTF-8'}" style="width:{$width_logo|escape:'html':'UTF-8'}px; height:{$height_logo|escape:'html':'UTF-8'}px;" />{/if}
            <br>
        </td>
    </tr>
    <tr>
        <td style="width: 50%;text-align: left;">{$shop_name|escape:'html':'UTF-8'}{$shop_address|escape:'html':'UTF-8'}</td>
    </tr>
</table>
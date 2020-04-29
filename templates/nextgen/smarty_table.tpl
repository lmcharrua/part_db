{locale path="nextgen/locale" domain="partdb"}
{*
    This file is used to create tables for Part or DevicePart objects, or for check the import data (tools_import.php).
    Maybe later we can use it also for other objects, if this is required.
    The table columns are very flexible, you have to declare them in config.php.

    Quite important methods for creating the loop arrays are:
    - Part::build_template_table_row_array()
    - Part::build_template_table_array()
    - DevicePart::build_template_table_row_array()
    - DevicePart::build_template_table_array()
    - lib.import.php::data_array_to_template_loop()

    Please Note: There are no empty lines between the {TMPL_IF}{/if} groups, because they would produce extremely large HTML output files (because of the loops)!
*}
<div class="table-responsive">
    <table class="table table-striped table-condensed table-compact table-hover table-export table-no-margin
    table-sortable {if isset($table_selectable) && $table_selectable}table-selectable{/if}" cellspacing="0" width="100%">
        <thead class="thead-default">
        {foreach $table as $t}
            {if isset($t.print_header) && $t.print_header}
                <tr class="trcat">
                    {foreach $t.columns as $col}
                        {if $col.caption=="row"}<th>{t}Nr.{/t}</th>{/if}
                        {if $col.caption=="hover_picture"}<th class="no-sort no-export"></th>{/if}
                        {if $col.caption=="id"}<th class="idclass">{t}ID{/t}</th>{/if}
                        {if $col.caption=="row_index"}<th class="idclass">{t}Nr.{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="name"}<th class="order-default">{t}Name{/t}</th>{/if}
                        {if $col.caption=="name_edit"}<th>{t}Name{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="description"}<th>{t}Beschreibung{/t}</th>{/if}
                        {if $col.caption=="description_edit"}<th>{t}Beschreibung{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="comment"}<th>{t}Kommentar{/t}</th>{/if}
                        {if $col.caption=="comment_edit"}<th>{t}Kommentar{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="name_description"}<th>{t}Name / Beschreibung{/t}</th>{/if}
                        {if $col.caption=="instock"}<th>{t}Bestand{/t}</th>{/if}
                        {if $col.caption=="instock_edit"}<th class="no-sort">{t}Bestand{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="instock_edit_buttons"}<th>{t}Bestand ändern{/t}</th>{/if}
                        {if $col.caption=="order_quantity_edit"}<th class="no-sort">{t escape=false}Bestell-<br>menge</th>{/t}{/if} {*  only for order parts  *}
                        {if $col.caption=="mininstock"}<th>{t escape=no}Mindest-{/t}<br>{t}bestand{/t}</th>{/if}
                        {if $col.caption=="mininstock_edit"}<th>{t}Mindest-{/t}<br>{t}bestand{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="instock_mininstock"}<th>{t escape=no}Vorh./<br>Min.Best{/t}</th>{/if}
                        {if $col.caption=="category"}<th>{t}Kategorie{/t}</th>{/if}
                        {if $col.caption=="category_edit"}<th>{t}Kategorie{/t}</th>{/if} {*  only for import parts  *}
                        {if !$disable_footprints}
                            {if $col.caption=="footprint"}<th>{t}Footprint{/t}</th>{/if}
                            {if $col.caption=="footprint_edit"}<th>{t}Footprint{/t}</th>{/if} {*  only for import parts  *}
                        {/if}
                        {if !$disable_manufacturers}
                            {if $col.caption=="manufacturer"}<th>{t}Hersteller{/t}</th>{/if}
                            {if $col.caption=="manufacturer_edit"}<th>{t}Hersteller{/t}</th>{/if} {*  only for import parts  *}
                        {/if}
                        {if $col.caption=="storelocation"}<th>{t}Lagerort{/t}</th>{/if}
                        {if $col.caption=="storelocation_edit"}<th>{t}Lagerort{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="suppliers"}<th>{t}Lieferanten{/t}</th>{/if}
                        {if $col.caption=="supplier_edit"}<th>{t}Lieferant{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="suppliers_radiobuttons"}<th>{t}Lieferanten{/t}</th>{/if} {*  only for order parts  *}
                        {if $col.caption=="datasheets"}{if !$disable_auto_datasheets}<th class="no-sort no-export">{t}Datenblätter{/t}</th>{/if}{/if}
                        {if $col.caption=="button_decrement"}<th class="text-center no-sort no-export">-</th>{/if}
                        {if $col.caption=="button_increment"}<th class="text-center no-sort no-export">+</th>{/if}
                        {if $col.caption=="button_edit"}<th class="text-center no-sort"></th>{/if}
                        {if $col.caption=="order_options"}<th class="no-sort no-export">{t}Optionen{/t}</th>{/if} {*  only for order parts  *}
                        {if $col.caption=="quantity_edit"}<th class="no-sort">{t}Anzahl{/t}</th>{/if} {*  only for device parts  *}
                        {if $col.caption=="mountnames_edit"}<th class="no-sort">{t escape=no}Bestückungs-<br>daten{/t}</th>{/if} {*  only for device parts  *}
                        {if $col.caption=="price_edit"}<th>{t}Preis{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="average_single_price"}<th>{t escape=no}Einzel-<br>preis Ø{/t}</th>{/if}
                        {if $col.caption=="single_prices"}<th>{t escape=no}Einzel-<br>preise{/t}</th>{/if}
                        {if $col.caption=="total_prices"}<th>{t escape=no}Gesamt-<br>preise{/t}</th>{/if} {*  only for device parts  *}
                        {if $col.caption=="supplier_partnrs"}<th>{t escape=no}Bestell-<br>nummern{/t}</th>{/if}
                        {if $col.caption=="supplier_partnr_edit"}<th class="no-sort">{t escape=no}Bestell-<br>nummer{/t}</th>{/if} {*  only for import parts  *}
                        {if $col.caption=="attachements"}<th class="no-export">{t}Dateianhänge{/t}</th>{/if}
                        {if $col.caption=="last_modified"}<th>{t}Zuletzt bearbeitet{/t}</th>{/if}
                        {if $col.caption=="created"}<th>{t}Erstellt{/t}</th>{/if}
                        {if $col.caption=="systemupdate_from_version"}<th class="no-sort">{t}Von Version{/t}</th>{/if}
                        {if $col.caption=="systemupdate_to_version" }<th class="no-sort">{t}Auf Version{/t}</th>{/if}
                        {if $col.caption=="systemupdate_release_date"}<th class="no-sort">{t}Veröffentlichung{/t}</th>{/if}
                        {if $col.caption=="systemupdate_changelog"}<th class="no-sort">{t}Changelog{/t}</th>{/if}
                    {/foreach}
                </tr>
            {/if}
        {/foreach}
        </thead>

        <tbody>
        {foreach $table as $t}
            {if !isset($t.print_header) || !$t.print_header}

                {* the alternating background colors are created here *}
                <tr {if isset($t.favorite) && $t.favorite}class="success"{/if}
                        {if $t.instock_warning_full_row}class="danger"{/if}>
                    {if isset($t.id)}
                        <input type="hidden" name="id_{$t.row_index}" value="{$t.id}">
                    {/if}
                    {foreach $t.row_fields as $row}

                        {if $row.caption =="row"}
                            {* row number *}
                            <td class="tdrow1 table-center">{$row.row}</td>
                        {/if}
                        {if $row.caption =="hover_picture"}
                            {* Pictures *}
                            <td class="tdrow0">
                                {if $row.caption =="hover_picture"}
                                    <p>
                                        <img class="img-fluid hoverpic" rel="popover" src="{$row.small_picture}" alt="{$row.picture_name}">
                                    </p>
                                {else}
                                    {if $row.small_picture}
                                        <img class="img-fluid hoverpic" rel="popover" src="{$row.small_picture}" alt="">
                                    {/if}
                                {/if}
                            </td>
                        {/if}
                        {if $row.caption =="id"}
                            {* id (note: "part_not_found" is used in lib.import.php::build_deviceparts_import_template_loop() )*}
                            <td class="tdrow4 idclass{if $row.part_not_found} bg-danger{/if}">{if isset($row.id)}{$row.id}{/if}</td>
                        {/if}
                        {if $row.caption == "row_index"}
                            {* row index *}
                            <td class="tdrow4 idclass">{$row.row_index}</td>
                        {/if}
                        {if $row.caption == "name"}
                            {* name/comment with link *}
                            <td class="tdrow1{if $row.caption == "obsolete"} bg-danger{/if}">
                                <a  data-toggle="tooltip" title="{if $row.caption == "obsolete"}(nicht mehr erhätlich) {/if}{if isset($row.comment) && !empty($row.comment)}{t}Kommentar:{/t} {$row.comment nofilter}{/if}"
                                    href="show_part_info.php?pid={$row.id}">
                                    {$row.name}
                                </a>
                            </td>
                        {/if}
                        {if $row.caption == "name_edit"}
                            {* name edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:150px;" name="name_{$row.row_index}" value="{$row.name}"></td>
                        {/if}
                        {if $row.caption == "description"}
                            {* description *}
                            <td class="tdrow1{if $row.obsolete} not-enough-instock{/if}">{$row.description nofilter}</td>
                        {/if}
                        {if $row.caption == "description_edit"}
                            {* description edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:150px;" name="description_{$row.row_index}" value="{$row.description nofilter}"></td>
                        {/if}
                        {if $row.caption == "comment"}
                            {* comment *}
                            <td class="tdrow1">{$row.comment nofilter}</td>
                        {/if}
                        {if $row.caption == "comment_edit"}
                            {* comment edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:150px;" name="comment_{$row.row_index}" value="{$row.comment nofilter}"></td>
                        {/if}
                        {if $row.caption == "name_description"}
                            {* name/comment/description *}
                            <td class="tdrow1{if $row.obsolete} not-enough-instock{/if}">
                                <a data-toggle="tooltip" title="{if $row.obsolete}(nicht mehr erhätlich) {/if}{if isset($row.comment) && !empty($row.comment)}{t}Kommentar:{/t} {$row.comment nofilter}{/if}"
                                   href="show_part_info.php?pid={$row.id}">
                                    {$row.name}{if isset($row.description)}&nbsp;{$row.description}{/if}
                                </a>
                            </td>
                        {/if}
                        {if $row.caption == "instock"}
                            {* instock *}
                            <td class="tdrow2 {if $row.not_enough_instock} not-enough-instock{/if}">
                                <div data-toggle="tooltip" title="min. Bestand: {$row.mininstock}">{$row.instock}</div>
                            </td>
                        {/if}
                        {if $row.caption == "instock_edit"}
                            {* instock edit *}
                            <td class="tdrow1"><input type="number" class="form-control input-sm" style="max-width: 75px;" name="instock_{$row.row_index}" value="{$row.instock}"></td>
                        {/if}
                        {if $row.caption == "order_quantity_edit"}
                            {* order quantity edit (only for order parts)  *}
                            <td class="tdrow1">
                                <input type="number" min="0" max="99999" class="form-control input-sm" name="order_quantity_{$row.row_index}" value="{$row.order_quantity}">
                                <span class="export-helper" data-target=""></span>
                                <p class="help-block">(mind. {$row.min_order_quantity})</p>
                            </td>
                        {/if}
                        {if $row.caption == "mininstock"}
                            {* mininstock *}
                            <td class="tdrow2">
                                {$row.mininstock}
                            </td>
                        {/if}
                        {if $row.caption == "mininstock_edit"}
                            {* instock edit *}
                            <td class="tdrow1"><input type="number" min="0" class="form-control input-sm" style="max-width: 75px;" name="mininstock_{$row.row_index}" value="{$row.mininstock}"></td>
                        {/if}
                        {if $row.caption == "instock_mininstock"}
                            {* instock/mininstock *}
                            <td class="tdrow2 {if $row.not_enough_instock} not-enough-instock{/if}">
                                {$row.instock}/{$row.mininstock}
                            </td>
                        {/if}
                        {if $row.caption == "category"}
                            {* category *}
                            <td class="tdrow1">
                                {if $t.show_full_paths == true}
                                    {include "./smarty_structural_link.tpl" link=$row.category_loop}
                                {else}
                                    <a href="show_category_parts.php?cid={$row.category_id}" title="{$row.category_path}">{$row.category_name}</a>
                                {/if}
                            </td>
                        {/if}
                        {if $row.caption == "category_edit"}
                            {* category edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:100px;" name="category_{$row.row_index}" value="{$row.category_name}"></td>
                        {/if}
                        {if !$disable_footprints}
                            {if $row.caption == "footprint"}
                                {* footprint *}
                                <td class="tdrow1">
                                    {if isset($row.footprint_path)}
                                        {if $t.show_full_paths == true}
                                            {include "./smarty_structural_link.tpl" link=$row.footprint_loop}
                                        {else}
                                            <a href="show_footprint_parts.php?fid={$row.footprint_id}&subfoot=0" title="{$row.footprint_path}">{$row.footprint_name}</a>
                                        {/if}
                                    {/if}
                                </td>
                            {/if}
                            {if $row.caption == "footprint_edit"}
                                {* footprint edit (only for import parts) *}
                                <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:100px;" name="footprint_{$row.row_index}" value="{$row.footprint_name}"></td>
                            {/if}
                        {/if}
                        {if !isset($disable_manufacturers) || !$disable_manufacturers }
                            {if $row.caption == "manufacturer"}
                                {* manufacturer *}
                                <td class="tdrow1">
                                    {if isset($row.manufacturer_id)}
                                        {if $t.show_full_paths == true}
                                            {include "./smarty_structural_link.tpl" link=$row.manufacturer_loop}
                                        {else}
                                            <a href="show_manufacturer_parts.php?mid={$row.manufacturer_id}&subman=0" title="{$row.manufacturer_path}">{$row.manufacturer_name}</a>
                                        {/if}
                                    {/if}
                                </td>
                            {/if}
                            {if $row.caption == "manufacturer_edit"}
                                {* manufacturer edit *}
                                <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:100px;" name="manufacturer_{$row.row_index}" value="{$row.manufacturer_name}"></td>
                            {/if}
                        {/if}
                        {if $row.caption == "storelocation"}
                            {* storelocation *}
                            <td class="tdrow1">
                                {if isset($row.storelocation_path)}
                                    {if $t.show_full_paths == true}
                                        {include "./smarty_structural_link.tpl" link=$row.storelocation_loop}
                                    {else}
                                        <a href="show_location_parts.php?lid={$row.storelocation_id}&subloc=0" title="{$row.storelocation_path}">{$row.storelocation_name}</a>
                                    {/if}
                                {/if}

                            </td>
                        {/if}
                        {if $row.caption == "storelocation_edit"}
                            {* storelocation edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:100px;" name="storelocation_{$row.row_index}" value="{$row.storelocation_name}"></td>
                        {/if}
                        {if $row.caption == "datasheets"}
                            {if !$disable_auto_datasheets}
                                {* datasheet links with icons *}
                                <td class="tdrow5" nowrap>
                                    {foreach $row.datasheets as $sheet }
                                        <a class="link-datasheet datasheet" title="{$sheet.name}" href="{$sheet.url}" target="_blank"><img class="companypic" src="{$relative_path}{$sheet.image}" alt="{$sheet.name}"></a>
                                    {/foreach}
                                </td>
                            {/if}
                        {/if}
                        {if $row.caption == "button_decrement"}
                            {* build the "-" button, only if more than 0 parts on stock *}
                            <td class="tdrow6 no-select">
                                <button type="submit" class="btn btn-xs btn-outline-dark" name="decrement_{$row.row_index}"
                                        {if $row.decrement_disabled}disabled="disabled"{/if}
                                ><i class="fa fa-minus" aria-hidden="true"></i></span></button>
                            </td>
                        {/if}
                        {if $row.caption == "button_increment"}
                            {* build the "+" button *}
                            <td class="tdrow7 no-select">
                                <button type="submit" class="btn btn-xs btn-outline-dark" name="increment_{$row.row_index}"
                                        {if $row.increment_disabled}disabled="disabled"{/if}
                                ><i class="fa fa-plus" aria-hidden="true"></i></span></button>
                            </td>
                        {/if}
                        {if $row.caption == "button_edit"}
                            <td class="tdrow7 no-select">
                                {if !$row.edit_disabled} <a class="btn btn-xs btn-outline-dark" href="{$relative_path}edit_part_info.php?pid={$row.id}"
                                                            {if $row.edit_disabled}disabled="disabled" {/if}><i class="fa fa-pencil-alt" aria-hidden="true"></i></span></a> {/if}
                            </td>
                        {/if}
                        {if $row.caption == "order_options"}
                            {* build the order options (e.g. the "to stock" checkbox) (only for order parts) *}
                            <td class="tdrow1" class="form-control" nowrap>
                                <div class="form-check abc-checkbox form-check-dropdown">
                                    <input type="checkbox" class="form-check-input" id name="tostock_{$row.row_index}">
                                    <label class="form-check-label" for>{t}Einbuchen{/t}</label>
                                </div>
                                {if $row.enable_remove}
                                    <div class="form-check abc-checkbox form-check-dropdown">
                                        <input class="form-check-input" type="checkbox" name="remove_{$row.row_index}">
                                        <label class="form-check-label">{t}Aus Liste löschen{/t}</label>
                                    </div>
                                {/if}
                            </td>
                        {/if}
                        {if $row.caption == "quantity_edit"}
                            {* quantity for DevicePart elements *}
                            <td class="tdrow1" nowrap style="width: 5rem;">
                                <div class="input-group">
                                    <input type="text" class="form-control input-sm" style="width:45px;" name="quantity_{$row.row_index}"
                                           value="{if isset($row.quantity)}{$row.quantity}{else}0{/if}"
                                           {if isset($can_part_edit) && isset($can_part_delete) && !$can_part_edit && !$can_part_delete}disabled{/if}>
                                    <span class="export-helper" data-target=""></span>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary btn-sm" type="button" onClick="elements['quantity_{$row.row_index}'].value=0"
                                                {if isset($can_part_delete) && !$can_part_delete}disabled{/if}>
                                            <i class="fa fa-times" aria-hidden="true"></i></button>
                                    </div>
                                </div>

                            </td>
                        {/if}
                        {if $row.caption == "mountnames_edit"}
                            {* mountnames for DevicePart elements *}
                            <td class="tdrow1">
                                <input type="text" size="8" class="form-control input-sm" name="mountnames_{$row.row_index}"
                                       value="{if isset($row.mountnames)}{$row.mountnames}{/if}"
                                       {if isset($can_part_edit) && !$can_part_edit}disabled{/if}>
                                <span class="export-helper" data-target=""></span>
                            </td>
                        {/if}
                        {if $row.caption == "suppliers"}
                            {* suppliers *}
                            <td class="tdrow4" nowrap valign="top">
                                {foreach $row.suppliers as $sup}
                                    <div style="display:inline-block; height:1.7em; line-height:1.7em;">
                                        <a href="{$relative_path}show_supplier_parts.php?sid={$sup.supplier_id}&subsup=0">{$sup.supplier_name}</a>
                                    </div><br>
                                {/foreach}
                            </td>
                        {/if}
                        {if $row.caption == "supplier_edit"}
                            {* supplier edit (only for import parts) *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:100px;" name="supplier_{$row.row_index}" value="{$row.supplier_name}"></td>
                        {/if}
                        {if $row.caption == "suppliers_radiobuttons"}
                            {* supplier-radiobuttons (only for order parts) *}
                            <td class="tdrow1" nowrap valign="top">
                                {foreach $row.suppliers_radiobuttons as $radio}
                                    <div class="form-check abc-radio form-check-dropdown">
                                        <input type="radio" class="form-check-input" name="orderdetails_{$radio.row_index}" value="{$radio.orderdetails_id}" {if $radio.selected}checked{/if}>
                                        <label class="form-check-label">{$radio.supplier_name}</label>
                                    </div>
                                {/foreach}
                            </td>
                        {/if}
                        {if $row.caption == "price_edit"}
                            {* price edit *}
                            <td class="tdrow1"><input type="number" class="form-control input-sm" style="width:60px;" min="0" step="any" name="price_{$row.row_index}" value="{$row.price}"></td>
                        {/if}
                        {if $row.caption == "average_single_price"}
                            {* average single price for one piece *}
                            <td class="tdrow4" nowrap>
                                {$row.average_single_price}
                            </td>
                        {/if}
                        {if $row.caption == "single_prices"}
                            {* single prices *}
                            <td class="tdrow4" nowrap valign="top">
                                {foreach $row.single_prices as $price}
                                    <div style="display:inline-block; height:1.7em; line-height:1.7em;">
                                        {$price.single_price}
                                    </div><br>
                                {/foreach}
                            </td>
                        {/if}
                        {if $row.caption == "total_prices"}
                            {* total prices *}
                            <td class="tdrow4" nowrap valign="top">
                                {foreach $row.total_prices as $price}
                                    <div style="display:inline-block; height:1.7em; line-height:1.7em;">
                                        {$price.total_price}
                                    </div><br>
                                {/foreach}
                            </td>
                        {/if}
                        {if $row.caption == "supplier_partnrs"}
                            {* supplier part-nrs *}
                            <td class="tdrow1" nowrap valign="top">
                                {foreach $row.supplier_partnrs as $sup}
                                    <div style="display:inline-block; height:1.7em; line-height:1.7em;">
                                        {if isset($sup.supplier_product_url) && !empty($sup.supplier_product_url)}
                                            <a class="link-external" target="_blank" rel="noopener" title="{$sup.supplier_product_url}" href="{$sup.supplier_product_url}">{$sup.supplier_partnr}</a>
                                        {else}
                                            {$sup.supplier_partnr}
                                        {/if}
                                    </div><br>
                                {/foreach}
                            </td>
                        {/if}
                        {if $row.caption == "supplier_partnr_edit"}
                            {* supplier part-nr edit *}
                            <td class="tdrow1"><input type="text" class="form-control input-sm" style="width:120px;" name="supplier_partnr_{$row.row_index}" value="{$row.supplier_partnr}"></td>
                        {/if}
                        {if $row.caption == "attachements"}
                            {* attachements (names with hyperlinks) *}
                            <td class="tdrow5" id="attach">
                                {foreach $row.attachements as $attach}
                                    {if $t.use_attachements_names}
                                        <a class="link-external" title="{$attach.type}" href="{$attach.filename}" rel="noopener" target="_blank">{$attach.name}</a><br>
                                    {else}
                                        <a class="link-datasheet" title="{$attach.type}: {$attach.name}" href="{$attach.filename}" rel="noopener" target="_blank">{$attach.icon nofilter}</a>
                                    {/if}

                                {/foreach}
                                <div></div>
                            </td>
                        {/if}
                        {if $row.caption == "last_modified"}
                            {* description *}
                            <td class="tdrow1">{$row.last_modified}</td>
                        {/if}
                        {if $row.caption == "created"}
                            {* description *}
                            <td class="tdrow1">{$row.created}</td>
                        {/if}
                        {if $row.caption == "systemupdate_from_version"}
                            {* only for systemupdates *}
                            <td class="tdrow1{if  $row.stable} backgreen{/if}" style="min-width:100px;">
                                {$from_version}
                            </td>
                        {/if}
                        {if $row.caption == "systemupdate_to_version"}
                            {* only for systemupdates *}
                            <td class="tdrow1{if $row.stable} backgreen{/if}" style="min-width:100px;">
                                <b>{$to_version}</b>
                            </td>
                        {/if}
                        {if $row.caption == "systemupdate_release_date"}
                            {* only for systemupdates *}
                            <td class="tdrow1{if $row.stable} backgreen{/if}" style="min-width:100px;">
                                {$release_date}
                            </td>
                        {/if}
                        {if $row.caption == "systemupdate_changelog"}
                            {* only for systemupdates *}
                            <td class="tdrow1{if $row.stable} backgreen{/if}" style="min-width:100px;">
                                {foreach $changelog as $change}
                                    {if isset($change.log_item)}&nbsp;&bull;&nbsp;{$change.log_item}<br>{/if}
                                {/foreach}
                            </td>
                        {/if}
                    {/foreach}
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>
</div>
{locale path="nextgen/locale" domain="partdb"}

<span id="export-title">{t}Bauteile mit Footprint{/t}: {$footprint_name}</span>
<span id="export-messageTop">{t}Vollständiger Pfad{/t}: {$footprint_fullpath}</span>

{include "../smarty_breadcrumb.tpl"}

{if $other_panel_position == "top" || $other_panel_position == "both"}
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <a data-toggle="collapse" class="link-collapse text-white" href="#panel-other">
                {t}Sonstiges{/t}
            </a>
        </div>
        <div class="card-body card-collapse collapse {if !$other_panel_collapse}show{/if}" id="panel-other">
            <form action="" method="post" class="form-horizontal no-progbar">
                <input type="hidden" name="fid" value="{$fid}">
                <input type="hidden" name="subfoot" value="{if $with_subfoot}0{else}1{/if}">

                <div class="form-group row">
                    <div class="col-md-10">
                        <button type="submit" class="btn btn-outline-secondary {if $with_subfoot}active{/if}" name="subfoot_button" >{t}Unterfootprints einblenden{/t}</button>
                    </div>

                    {if $can_create}
                        <div class="form-inline col-md-7 col-lg-8 mt-2">
                            <div class="form-group">
                                <div class="col-md-12"></div>
                                <a class="btn btn-primary" href="edit_part_info.php?footprint_id={$fid}">
                                    {t}Neues Teil mit diesem Footprint{/t}
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </form>
        </div>
    </div>
{/if}

<form method="post">
    <input type="hidden" name="fid" value="{$fid}">
    <input type="hidden" name="subfoot" value="{$with_subfoot}">
    <input type="hidden" name="page" value="1">

    {include "../smarty_pagination.tpl"}
</form>

<div class="card">
    <div class="card-header">
        <i class="fa fa-cube" aria-hidden="true"></i>&nbsp;
        <b>{$table_rowcount}</b> {t}Teile mit Footprint{/t} "<b>{$footprint_name}</b>"
    </div>
    <form method="post" action="" class="no-progbar">
        <input type="hidden" name="lid" value="{$fid}">
        <input type="hidden" name="subloc" value="{if $with_subfoot}1{else}0{/if}">
        <input type="hidden" name="table_rowcount" value="{$table_rowcount}">
        <input type="hidden" name="limit" value="{$limit}">
        <input type="hidden" name="page" value="{$page}">
        {include file='../smarty_table.tpl' table_selectable=true}
    </form>
</div>

<form method="post">
    <input type="hidden" name="fid" value="{$fid}">
    <input type="hidden" name="subcat" value="{$with_subfoot}">
    <input type="hidden" name="page" value="1">

    {include "../smarty_pagination.tpl"}
</form>

{if $other_panel_position == "bottom" || $other_panel_position == "both"}
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <a data-toggle="collapse" class="link-collapse text-white" href="#panel-other2">
                {t}Sonstiges{/t}
            </a>
        </div>
        <div class="card-body card-collapse collapse {if !$other_panel_collapse}show{/if}" id="panel-other2">
            <form action="" method="post" class="form-horizontal no-progbar">
                <input type="hidden" name="fid" value="{$fid}">
                <input type="hidden" name="subfoot" value="{if $with_subfoot}0{else}1{/if}">

                <div class="form-group row">
                    <div class="col-md-10">
                        <button type="submit" class="btn btn-outline-secondary {if $with_subfoot}active{/if}" name="subfoot_button" >{t}Unterfootprints einblenden{/t}</button>
                    </div>

                    {if $can_create}
                        <div class="form-inline col-md-7 col-lg-8 mt-2">
                            <div class="form-group">
                                <div class="col-md-12"></div>
                                <a class="btn btn-primary" href="edit_part_info.php?footprint_id={$fid}">
                                    {t}Neues Teil mit diesem Footprint{/t}
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </form>
        </div>
    </div>
{/if}
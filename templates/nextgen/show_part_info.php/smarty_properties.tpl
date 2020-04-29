{locale path="nextgen/locale" domain="partdb"}

{if !empty($properties_loop)}
    <div class="card mt-3">
        <div class="card-header">
            <a data-toggle="collapse" class="link-collapse text-default" href="#panel-properties"><i class="fa fa-table fa-fw" aria-hidden="true"></i>
            {t}Bauteileeigenschaften{/t}
            </a>
        </div>
        <div class="table-responsive card-collape collapse show" id="panel-properties">
            <table class="table table-striped table-hover table-sortable">
                <thead>
                <tr>
                    <th class="no-sort no-export"></th>
                    <th>{t}Eigenschaft{/t}</th>
                    <th>{t}Wert{/t}</th>

                </tr>
                </thead>
                <tbody>
                {foreach $properties_loop as $property}
                    <tr>
                        <td></td>
                        <td><strong>{$property['name']}</strong></td>
                        <td>{$property['value']}</td>

                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
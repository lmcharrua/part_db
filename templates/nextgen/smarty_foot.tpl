{if isset($messages)}
    <!--suppress ALL -->

    <div class="card">
        <form action="" method="post" class="card-body">
            {foreach $messages as $msg}
                {if isset($msg.text)}
                    {if isset($msg.strong)}<strong>{/if}
                {if isset($msg.color)}<span style="color: {$msg.color}; ">{/if}
                    {$msg.text nofilter}
                {if isset($msg.color)}</span>{/if}
                    {if isset($msg.strong)}</strong>{/if}
                {/if}

                {if isset($msg.html)}
                    {$msg.html nofilter}
                {/if}

                {if !isset($msg.no_linebreak)}<br>{/if}
            {/foreach}
        </form>
    </div>
{/if}


<!-- Modal -->
<div class="modal border-primary" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{t}Lade{/t}
                    <small class="text-muted">{t}Dies kann einen Moment dauern...{/t}</small></h5>
            </div>
            <div class="modal-body">
                <div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0"
                     aria-valuemax="100" style="width: 100%;">
                    <span>{t}Lade{/t}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="basepath" value="{$relative_path}">
<input type="hidden" id="autorefresh" value="{$autorefresh}">
<input type="hidden" id="redirect_url" value="{$redirect_url}">
<input type="hidden" id="auto_sort" value="{$auto_sort}">


</div> <!-- content-data -->
</div> <!-- .container-float -->
</div> <!-- page-content-wrapper -->

</main>

</div>   <!-- Wrapper -->

<!-- PHP Debugbar -->
{if isset($debugbar_body)}{$debugbar_body nofilter}{/if}


{if !isset($ajax_request) || !ajax_request}
    <!-- Back to top button -->
    <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button"
       title="Zum Seitenbeginn" data-toggle="tooltip" data-placement="left">
        <i class="fas fa-angle-up fa-fw"></i>
    </a>

    <!-- Datatables -->
    <link rel="stylesheet" type="text/css" href="{$relative_path}datatables/datatables.min.css"/>
    <script type="text/javascript" src="{$relative_path}datatables/datatables.min.js"></script>
    <!-- Datatables plugin for natural sorting -->
    <script type="text/javascript" src="{$relative_path}datatables/natural.min.js"></script>

    {* Cookie Consent dialog*}
    {if $cookie_consent_active}
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js"></script>
    <script>
        window.addEventListener("load", function(){
            window.cookieconsent.initialise({
                "palette": {
                    "popup": {
                        "background": "#383b75"
                    },
                    "button": {
                        "background": "#f1d600"
                    }
                },
                "content": {
                    "message": "{$cookie_consent_config.message}",
                    "dismiss": "{$cookie_consent_config.button_text}",
                    "link": "{$cookie_consent_config.link_text}",
                    "href": "{$cookie_consent_config.link_href}"
                }
            })});
    </script>
    {/if}


    <!-- Treeview -->
    <script src="{$relative_path}js/bootstrap-treeview.min.js" async></script>


    <!-- Moment JS -->
    <script src="{$relative_path}js/tempusdominus/moment-with-locales.min.js"></script>

    <!-- Chart JS -->
    <script src="{$relative_path}vendor/nnnick/chartjs/dist/Chart.min.js"></script>

    <!-- FileInput -->
    <script src="{$relative_path}vendor/kartik-v/bootstrap-fileinput/js/fileinput.js" async></script>
    {* <script src="{$relative_path}vendor/kartik-v/bootstrap-fileinput/themes/fas/theme.min.js"></script> *}

    <!-- JQuery Highlight -->
    <script src="{$relative_path}js/jquery.highlight.min.js" async></script>


    <script src="{$relative_path}js/tempusdominus/tempusdominus-bootstrap-4.min.js"></script>
    <link rel="stylesheet" href="js/tempusdominus/tempusdominus-bootstrap-4.min.css"/>

    <!-- Functions -->
    {if $debugging_activated}
        <script src="{$relative_path}templates/nextgen/js/functions.js" async></script>
        <script src="{$relative_path}templates/nextgen/js/ajax_ui.js" async></script>
    {else} {* Use minified scripts *}
        <script src="{$relative_path}templates/nextgen/js/functions.min.js" async></script>
        <script src="{$relative_path}templates/nextgen/js/ajax_ui.min.js" async></script>
    {/if}

    <!-- Calculator scripts -->
    <script type="text/javascript" src="{$relative_path}javascript/calculator.min.js"></script>

    <!-- jQuery Form lib -->
    <script src="{$relative_path}vendor/jquery-form/form/dist/jquery.form.min.js"></script>

    <!-- Bootstrap-select -->
    <script src="{$relative_path}vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="{$relative_path}vendor/snapappointments/bootstrap-select/dist/js/i18n/defaults-de_DE.js"></script>

    <!-- Bootstrap typeahead -->
    <script src="{$relative_path}js/bootstrap3-typeahead.min.js"></script>

    {if !empty($tracking_code)}{$tracking_code nofilter}{/if}

    </body>

    </html>
{/if}
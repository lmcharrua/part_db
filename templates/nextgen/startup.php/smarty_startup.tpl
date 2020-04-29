{locale path="nextgen/locale" domain="partdb"}

{if isset($must_change_pw) && $must_change_pw}
    <div class="alert alert-danger mb-3">
        <div class="row vertical-align">
            <div class="col-md-1">
                <i class="fas fa-exclamation fa-5x " style="text-align: center; width: 1em;"></i>
            </div>
            <div class="col-md-11">
                    <h4>{t}Password Änderung erforderlich!{/t}</h4>
                    <strong>{t}Aus Sicherheitsgründen müssen sie ihr Password ändern.{/t}</strong>
                    <p>{t escape=false}Besuchen sie hierzu in die <a href="user_settings.php">Benutzeinstellungen</a>.{/t}</p>
            </div>
        </div>
    </div>
{/if}

{if isset($database_update) && $database_update}
    {if $database_update}
        <div class="card border-danger mb-3">
            <div class="card-header bg-danger text-white">
                <h3>
                    <i class="fa fa-database" aria-hidden="true"></i>
                    {t}Datenbankupdate{/t}
                </h3>
            </div>
            <div class="card-body">
                <b>{t 1=$db_version_current 2=$db_version_latest}Datenbank-Version %1 benötigt ein Update auf Version %2.{/t}</b><br><br>
                {if isset($disabled_autoupdate)}
                    {if isset($auto_disabled_autoupdate)}
                        <p>{t}Automatische Datenbankupdates wurden vorübergehend automatisch deaktiviert,
                                da es sich um ein sehr umfangreiches Update handelt.{/t}</p>
                    {else}
                        <p>{t}Automatische Datenbankupdates sind deaktiviert.{/t}</p>
                    {/if}
                    {t}Updates bitte manuell durchführen:{/t} <a href="system_database.php">{t}System -> Datenbank{/t}</a>
                {else}
                    {$database_update_log}
                {/if}
            </div>
        </div>
    {/if}
{/if}


<div class="jumbotron">
    <h1 class="display-3">{if !empty($partdb_title)}{$partdb_title}{else}Part-DB{/if}</h1>
    {if isset($system_version_full)}
        <h4>{t}Version:{/t} {$system_version_full}{if !empty($git_branch)}, Git: {$git_branch}{if isset($git_commit)}/{$git_commit}{/if}{/if}</h4>
    {/if}
    <h5><i>"NextGen"</i></h5>

    {if !empty($banner)}
        <hr>
        <div>
            <h5>{$banner nofilter}</h5>
        </div>
    {/if}
</div>



{if isset($display_warning) && $display_warning}
    <div class="card border-danger mt-3">
        <div class="card-header bg-danger text-white">
            {t}Achtung!{/t}
        </div>
        <div class="card-body">
            {t escape=false 1=$missing_category 2=$missing_storeloc 3=$missing_footprint 4=$missing_supplier}Bitte beachten Sie, dass vor der Verwendung der Datenbank mindestens<br>
                <blockquote>%1 eine <a href="edit_categories.php">Kategorie</a> </blockquote>hinzufügt werden muss.<br><br>
                Um das Potential der Suchfunktion zu nutzen, wird empfohlen
                <blockquote>%2 einen <a href="edit_storelocations.php">Lagerort</a></blockquote>
                <blockquote>%3 einen <a href="edit_footprints.php">{t}Footprint{/t}</a> </blockquote>
                <blockquote>%4 und einen <a href="edit_suppliers.php">{t}Lieferanten{/t}</a> </blockquote>
                anzugeben.{/t}

            <br>
            {t escape=false}Diese Meldung kann in den <a href="system_config.php">Einstellungen</a> deaktiviert werden.{/t}
        </div>
    </div>
{/if}

{if isset($broken_filename_footprints) && $broken_filename_footprints}
    <div class="card border-danger mt-3">
        <div class="card-header bg-danger text-white">
            <h2 class="red">{t}Achtung!{/t}</h2>
        </div>
        <div class="card-body">
        <span style="color: red; ">{t}In Ihrer Datenbank gibt es Footprints, die einen fehlerhaften Dateinamen hinterlegt haben.
                Dies kann durch ein Datenbankupdate, ein Update von Part-DB, oder durch nicht mehr existierende Dateien ausgelöst worden sein.{/t}
            <br>
            {t escape=none}Sie können dies unter <a href="edit_footprints.php">Bearbeiten/Footprints</a> (ganz unten, "Fehlerhafte Dateinamen") korrigieren.{/t}
        </span>
        </div>
    </div>
{/if}



<div class="card border-primary mt-3">
    <div class="card-header bg-primary text-white">
        <h4><i class="fa fa-book fa-fw" aria-hidden="true"></i>&nbsp{t}Lizenz{/t}</h4>
    </div>
    <div class="card-body">
        <p>Part-DB, Copyright &copy; 2005 of <strong>Christoph Lechner</strong>. <br> Part-DB is published under the <strong>GPL</strong>, so it comes with <strong>ABSOLUTELY NO WARRANTY</strong>,
            click <a href="{$relative_path}readme/gpl.txt" class="link-external" rel="noopener" target="_blank">here</a> for details.
            This is free software, and you are welcome to redistribute it under certain conditions.
            Click <a href="{$relative_path}readme/gpl.txt" class="link-external" rel="noopener" target="_blank">here</a> for details.<br>
        </p>
        <strong><i class="fab fa-github fa-fw"></i> {t}Projektseite:{/t}</strong> {t escape=false link="https://github.com/Part-DB/Part-DB"}Downloads, Bugreports, ToDo-Liste usw. gibts auf der <a class="link-external" target="_blank" href="%1">GitHub Projektseite</a>{/t}<br>
        <strong><i class="fas fa-question fa-fw"></i> {t}Hilfe{/t}</strong> {t escape=false link="https://github.com/Part-DB/Part-DB/wiki"}Hilfe und Tipps finden sie im <a class="link-external" href="%1" target="_blank">Wiki</a> der GitHub Seite.{/t} <br>
        <strong><i class="fas fa-comments fa-fw"></i> Forum:</strong> {t escape=false link="https://www.mikrocontroller.net/topic/461256"}Für Fragen rund um die Part-DB gibt es einen Thread auf <a class="link-external" target="_blank" href="%1">mikrocontroller.net</a>{/t}<br>
        <strong><i class="fas fa-info fa-fw"></i> Wiki:</strong> {t escape=false link="https://www.mikrocontroller.net/articles/Part-DB_RW_-_Lagerverwaltung"}Weitere Informationen gibt es im <a class="link-external" target="_blank" href="%1">mikrocontroller.net Artikel</a>{/t}<br>
        <br>
        {t}Initiator:{/t} <strong>Christoph Lechner</strong> - <a class="link-external" rel="noopener" target="_blank" href="http://www.cl-projects.de/">http://www.cl-projects.de/</a><br>
        {t}Autor seit 2009:{/t} <strong>K. Jacobs</strong> - <a class="link-external" rel="noopener" target="_blank" href="http://www.grautier.com/">http://grautier.com</a><br>
        {t}Autor seit 2016:{/t} <strong>Jan Böhmer</strong> - <a class="link-external" rel="noopener" target="_blank" href="https://github.com/jbtronics">Github</a><br>
    </div>

    <table class="table table-sm">
        <thead>
        <tr>
            <th>{t}Weitere Autoren:{/t}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $authors as $author}
            <tr><td><strong>{$author.name}</strong></td><td>{$author.role}</td></tr>
        {/foreach}
        </tbody>
    </table>
</div>

{if !empty($rss_feed_loop)}
    <div class="card boder-dark mt-3">
        <div class="card-header bg-light">
            <h4><i class="fa fa-rss fa-fw" aria-hidden="true"></i>&nbsp{t}Updates{/t}</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>{t}Version{/t}</th>
                    <th>{t}Veröffentlichungsdatum{/t}</th>
                    <th>{t}Link{/t}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $rss_feed_loop as $rss}
                    <tr>
                        <td>{$rss.title}</td>
                        <td>{$rss.datetime}</td>
                        <td><a href="{$rss.link}" class="link-external" rel="noopener" target="_blank">{$rss.link}</a></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}

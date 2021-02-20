{if $catrgoryMsg !== ''}
<div class="alert alert-danger" role="alert">
    {$catrgoryMsg}
    <br>
    <div style="float: auto">
        <a class="btn btn-primary" target='_blank' href='{$link->getAdminLink('', true, array('route' => 'admin_metas_index'))}'>
            {l s='Traffic & SEO' mod='advancedNavigation'}
        </a>
    </div>
</div>
{/if}

<ul class="nav nav-tabs" id="lang" role="tablist">
    {foreach item=categoryGroup key=lang from=$categories name="tabs"}
        {assign var=avLang value=Language::getLanguage($lang)}
        <li class="nav-item {if $smarty.foreach.tabs.index === 0}active{/if}">
            <a class="nav-link {if $smarty.foreach.tabs.index === 0}active{/if}" id="lang-{$lang}-tab" data-toggle="tab" href="#lang-{$lang}" role="tab" aria-controls="lang-tab-{$lang}" aria-selected="{if $smarty.foreach.tabs.index === 0}true{else}false{/if}">
                {$avLang['iso_code']}
            </a>
        </li>
    {/foreach}
</ul>

<div class="tab-content" id="langContent">
{foreach item=categoryGroup key=lang from=$categories name="list"}
    <div class="tab-pane fade active {if $smarty.foreach.list.index===0}in{/if}" id="lang-{$lang}" role="tabpanel" aria-labelledby="lang-{$lang}-tab">
    {foreach item=group key=gi from=$categoryGroup}
    <table class="table" >
        <tr>
            <td scope="row" style="border-bottom: 1px solid #181818; width: 10%">
                {l s='Public Link' mod='advancedNavigation'}:
            </td>
            <td style="border-bottom: 1px solid #181818">
                {$link->getCategoryLink($group[0]['category']->id_category, null, $lang)}
            </td>
        </tr>
        <tr>
            <table style='width: 80%' class="table">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">{l s='Category Id' mod='advancedNavigation'}</th>
                    <th scope="col">{l s='Category Name' mod='advancedNavigation'}</th>
                    <th scope="col">{l s='Parent' mod='advancedNavigation'}</th>
                    <th scope="col">{l s='Rewirite URL' mod='advancedNavigation'}</th>
                    <th scope="col">{l s='Language' mod='advancedNavigation'}</th>
                    <th scope="col">{l s='Actions ' mod='advancedNavigation'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=cat from=$group}
                    <tr>
                        <td scope="row">{$cat['category']->id_category}</td>
                        <td>{$cat['category']->name[$cat['lang']['id_lang']]}</td>
                        <td>{$cat['parent']->name[$cat['lang']['id_lang']]}</td>
                        <td>{$cat['category']->link_rewrite[$cat['lang']['id_lang']]}</td>
                        <td>{$cat['lang']['name']}</td>
                        <td style="text-align: center; vertical-align: middle;">
                            <a class="btn btn-primary" target='_blank' href='{$link->getAdminLink('', true, array('route' => 'admin_categories_edit', 'categoryId'=> $cat['category']->id_category))}'>
                                {l s='edit'}
                            </a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </tr>
    </table>
    <div style='height: 30px; background: grey'></div>
{/foreach}
    </div>
{foreachelse}
    <h1>{l s='No duplicated items were found in the search' mod='advancedNavigation'}</h1>
{/foreach}
</div>
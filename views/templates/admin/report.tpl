<div class="panel">
    <h2>{l s='Duplicated Urls'}</h2>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item active">
            <a class="nav-link active" id="category-tab" data-toggle="tab" href="#category" role="tab" aria-controls="category" aria-selected="true">
                {l s='Categories' mod='advancedNavigation'}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="product-tab" data-toggle="tab" href="#product" role="tab" aria-controls="product" aria-selected="false">
                {l s='Products' mod='advancedNavigation'}
            </a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade active in" id="category" role="tabpanel" aria-labelledby="category-tab">.
            {include file='./category.report.tpl'}
        </div>
        <div class="tab-pane fade active" id="product" role="tabpanel" aria-labelledby="product-tab">
            {include file='./product.report.tpl'}
        </div>
    </div>
</div>
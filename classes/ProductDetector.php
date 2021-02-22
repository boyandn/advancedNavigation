<?php


class ProductDetector
{
    const DB_TABLE_PRODUCT_LANG = "`" . _DB_PREFIX_ . "product_lang`";

    protected $map = [
        'id_product' => 'pl.id_product',
        'product_rewrite' => 'pl.link_rewrite',
        'reference' => 'p.reference',
        'encoded_id_product' => 'pl.id_product',
    ];

    protected $dispacher;
    protected $shopId;
    protected $link;
    protected $isMultiLang;

    public function __construct()
    {
        $this->dispacher = Dispatcher::getInstance();
        $this->shopId = Shop::getContextShopID();
        $this->link = new Link();
        $this->isMultiLang = Language::isMultiLanguageActivated($this->shopId);
    }

    public function findDublicatedProductLinks($langId)
    {
        $route = $this->getRoute($langId);
        $short_link = $this->getShortLink($langId);
        $regexp = $this->getRegexFromRoute($route);
        $dbColumns = $this->mapKeywords($regexp, $short_link);
        $ids = $this->findDublicatedProductIdsByColums($dbColumns, $langId);
        return $this->findProductsByIds($ids, $langId);
    }

    public function getUsedKeywords($langId)
    {
        $route = $this->getRoute($langId);
        $regexp = $this->getRegexFromRoute($route);
        $short_link = $this->getShortLink($langId);
        return $this->findUsedKeywordsInRoute($regexp, $short_link);
    }

    protected function findUsedKeywordsInRoute($regexp, $short_link)
    {
        preg_match($regexp, $short_link, $kw);
        $k = [];
        foreach ($kw as $keyword => $conf) {
            if (is_string($keyword)) {
                $k[] = $keyword;
            }
        }
        $k = array_unique($k);
        return $k;
    }

    protected function findDublicatedProductIdsByColums($dbColumns, $langId)
    {
        $sql ='
            SELECT 
                   group_concat(pl.id_product) as ids  
            FROM '. _DB_PREFIX_ . 'product_lang as pl
            JOIN '. _DB_PREFIX_ . 'product as p 
                on pl.id_product = p.id_product and pl.id_lang = '.$langId.' and  pl.id_shop = '.$this->shopId.'
            GROUP BY '. implode(',', $dbColumns).'
            HAVING count(pl.id_product) > 1';

        $res = Db::getInstance()->ExecuteS($sql);
        return $res;
    }

    protected function findProductsByIds($ids, $langId)
    {
        $res = [];
        foreach ($ids as $v) {
            $sql = 'SELECT id_product, id_lang FROM ' ._DB_PREFIX_ . 'product_lang
            WHERE `id_product` IN ('.$v["ids"].')';

            $sql .= ' AND id_lang = ' . $langId;

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql .= ' AND `id_shop` = ' . $this->shopId;
            }

            $res[] = Db::getInstance()->ExecuteS($sql);
        }
        return $res;
    }

    protected function mapKeywords($regexp, $short_link)
    {
        $keyword = $this->findUsedKeywordsInRoute($regexp, $short_link);

        $k = [];
        foreach ($keyword as $word) {
            $k[] = $this->map[$word];
        }
        return $k;
    }

    protected function getProductId()
    {
        $sql = 'SELECT id_product FROM ' ._DB_PREFIX_ . 'product_lang';
        return Db::getInstance()->getValue($sql);;
    }

    protected function getShortLink($langId = null )
    {
        $productId = $this->getProductId();
        $link = $this->link->getProductLink($productId, null, $langId, null, $this->shopId);
        $short_link = ltrim(parse_url($link, PHP_URL_PATH), '/');
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        if($this->isMultiLang)
        {
            $short_link = preg_replace('#^\/[a-zA-Z]{1,2}#i', '', $short_link);
        }

        return $short_link;
    }

    protected function getRoute($langId)
    {
         return $this->dispacher->getRoutes()[$this->shopId][$langId]['product_rule'];
    }

    protected function getRegexFromRoute($route)
    {
        return preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
    }


}
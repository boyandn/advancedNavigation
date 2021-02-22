<?php


class CategoryDetector
{
    protected $map = [
        'id_category' => 'cl.id_category',
        'encode_id' => 'cl.id_category',
        'category_rewrite' => 'cl.link_rewrite',
        'categories' => 'cpl.link_rewrite',
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

        $sql ="
            SELECT 
                group_concat(cl.id_category) as ids
            FROM " . _DB_PREFIX_ . "category_lang cl 
            JOIN " . _DB_PREFIX_ . "category c ON c.id_category=cl.id_category and cl.id_lang=".$langId." and cl.id_shop = " . $this->shopId . "
            JOIN " . _DB_PREFIX_ . "category cp ON c.id_parent=cp.id_category
            JOIN " . _DB_PREFIX_ . "category_lang cpl ON cpl.id_category=cp.id_category and cpl.id_lang=".$langId." and cpl.id_shop = " . $this->shopId . "
            GROUP BY ".implode(',', $dbColumns)."
            HAVING count(cl.id_category) > 1
       ";

        $res = Db::getInstance()->ExecuteS($sql);
        return $res;
    }

    protected function findProductsByIds($ids, $langId)
    {
        $res = [];
        foreach ($ids as $v) {
            $sql = 'SELECT id_category, id_lang FROM '. _DB_PREFIX_ . 'category_lang
            WHERE id_category IN ('.$v["ids"].')';

            $sql .= ' AND id_lang = ' . $langId;

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql .= ' AND id_shop = ' . $this->shopId;
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
        $sql = 'SELECT id_category FROM ' . _DB_PREFIX_ . 'category';
        return Db::getInstance()->getValue($sql);;
    }

    protected function getShortLink($langId = null )
    {
        $productId = $this->getProductId();
        $link = $this->link->getCategoryLink($productId, null, $langId, null, $this->shopId);
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
         return $this->dispacher->getRoutes()[$this->shopId][$langId]['category_rule'];
    }

    protected function getRegexFromRoute($route)
    {
        return preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
    }


}
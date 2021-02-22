<?php

include_once 'classes/ProductDetector.php';
include_once 'classes/CategoryDetector.php';

if (!defined('_PS_VERSION_')) {
    return;
}

class advancedNavigation extends Module
{
    public function __construct()
    {
        $this->name = 'advancedNavigation';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Boyan Naydenov';
        $this->need_instance = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Advanced Navigation');
        $this->description = $this->l('This module allows you to remove ID\'s from URLs. 
            It add possibility use encoded id\'s if needed. 
            It will report if detect duplicated urls for categories and products');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall "Advanced Navigation Module" module?');
    }

    public function getContent()
    {

        $categories = [];
        $products= [];
        $categoryCheck = $this->checkCategory();
        $categoryMsg = "";
        $productMsg = "";

        $categoryKeywords = (new CategoryDetector())->getUsedKeywords($this->context->language->id);



        if (in_array('id_category',$categoryKeywords)) {
            $categoryMsg .= $this->l("You can change {id} with {encode_id} for better security")."<br>";
        }

        if (!empty($categoryCheck)) {

            if(count($categoryKeywords) === 1) {
                $categoryMsg .= $this->l("Try to use additional keyword for the url schema, to prevent duplications, 
                        or edit category to change Friendly URL parameter")."<br>";
                $categoryMsg .= $this->l("This keywords {meta_keywords} and {meta_title} can't help to identify category accurately.
                                Since they can be empty")."<br>";

                if (!in_array('categories',$categoryKeywords)) {
                    $categoryMsg .= $this->l("You can add {categories} to prevent url duplications")."<br>";
                }

                if (!in_array('encode_id',$categoryKeywords)) {
                    $categoryMsg .= $this->l("You can add {encode_id} to prevent url duplications")."<br>";
                }
            }

            foreach ($categoryCheck as $langId => $groups) {
                foreach ($groups as $k => $group) {
                    foreach ($group as $gi => $p) {
                        $category = new Category($p['id_category']);
                        $categories[$langId][$k][$gi]['lang'] = $this->context->language->getLanguage($p['id_lang']);
                        $categories[$langId][$k][$gi]['category'] = $category;
                        $categories[$langId][$k][$gi]['parent'] = new Category($category->id_parent);
                    }
                }
            }
        }

        $productKeywords = (new ProductDetector())->getUsedKeywords($this->context->language->id);

        if (in_array('id_product',$productKeywords)) {
            $productMsg .= $this->l("You can change {id} with {encode_id} for better security")."<br>";
        }
        if ($res = $this->checkProducts()) {
            if(count($productKeywords) === 1) {
                $productMsg .= $this->l("Try to use additional keyword for the url schema, to prevent duplications, 
                        or edit product to change Friendly URL parameter")."<br>";

                if (!in_array('id_product',$productKeywords)) {
                    $productMsg .= $this->l("You can add {id} to prevent url duplications, but we recommend {encode_id}")."<br>";
                }

                if (!in_array('reference',$productKeywords)) {
                    $productMsg .= $this->l("You can add {reference} to prevent url duplications. But make sure all products have unique reference.")."<br>";
                }

                if (!in_array('encode_id_product',$productKeywords)) {
                    $productMsg .= $this->l("You can add {encode_id} to prevent url duplications")."<br>";
                }
            }


            foreach ($res as $langId => $groups) {
                foreach ($groups as $k => $group) {
                    foreach ($group as $gi => $p) {
                        $product = new Product($p['id_product']);
                        $products[$langId][$k][$gi]['lang'] = $this->context->language->getLanguage($langId);
                        $products[$langId][$k][$gi]['product'] = $product;
                    }
                }
            }
        }

        $this->context->smarty->assign('link', new Link());
        $this->context->smarty->assign('categories', $categories);
        $this->context->smarty->assign('catrgoryMsg', $categoryMsg);
        $this->context->smarty->assign('products', $products);
        $this->context->smarty->assign('productsKeywords', '');
        $this->context->smarty->assign('productMsg', $productMsg);


        $content = $this->context->smarty->fetch($this->local_path.'views/templates/admin/report.tpl');
        return $content;
    }

    private function checkCategory(){
        $cd = new CategoryDetector();
        $lang = Language::getLanguages();

        $res = [];
        foreach ($lang as $l)
        {
            $dublicated = $cd->findDublicatedProductLinks($l['id_lang']);
            if (!empty($dublicated)){
                $res[$l['id_lang']] = $dublicated;
            }

        }
        return $res;

    }

    private function checkProducts(){
        $pd = new ProductDetector();
        $lang = Language::getLanguages();

        $res = [];
        foreach ($lang as $l)
        {
            $dublicated = $pd->findDublicatedProductLinks($l['id_lang']);
            if (!empty($dublicated)){
                $res[$l['id_lang']] = $dublicated;
            }
        }
       return $res;
    }

    public function install()
    {
        // add link_rewrite as index to improve search
        foreach (array('category_lang', 'cms_category_lang', 'cms_lang', 'product_lang') as $tab) {
            if (!Db::getInstance()->ExecuteS('SHOW INDEX FROM `'._DB_PREFIX_.$tab.'` WHERE Key_name = \'link_rewrite\'')) {
                Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$tab.'` ADD INDEX ( `link_rewrite` )');
            }
        }

        if (
            parent::install()
            && $this->registerHook('dashboardZoneOne')
        ) {
            return true;
        }

        return false;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function hookDashboardZoneOne($params)
    {
        $catCount = 0;
        $prodCount = 0;

        $cat = $this->checkCategory();
        if (is_array($cat)) {
            foreach ($cat as $catLangs) {
                $catCount += count($catLangs);
            }
        }

        $prods = $this->checkProducts();
        if (is_array($prods)) {
            foreach ($prods as $prodLangs) {
                $prodCount += count($prodLangs);
            }
        }

        $this->context->smarty->assign('categories',$catCount);
        $this->context->smarty->assign('products',$prodCount);
        return $this->display(__FILE__, 'dashboard_zone_one.tpl');
    }

}

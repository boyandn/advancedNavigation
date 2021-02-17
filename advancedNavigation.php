<?php


if (!defined('_PS_VERSION_')) {
    return;
}

// Set true to enable debugging
define('FKV_DEBUG', true);

if (version_compare(phpversion(), '5.3.0', '>=')) { // Namespaces support is required
    include_once __DIR__.'/tools/debug.php';
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
        $this->description = $this->l('This override-module allows you to remove URL ID\'s.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall "Advanced Navigation Module" module?');
    }

    public function getContent()
    {
        $link = new LinkCore();

        $output = '<p class="info">'
            .$this->l('On some versions you could have to disable Cache, save, open your shop home page, than go back and enable it:').'<br><br>'
            .sprintf('%s -> %s -> %s', $this->l('Advanced Parameters'), $this->l('Performance'), $this->l('Clear Smarty cache')).'<br>'
            .sprintf('%s -> %s -> %s -> %s', $this->l('Preferences'), $this->l('SEO and URLs'), $this->l('Set user-friendly URL off'), $this->l('Save')).'<br>'
            .sprintf('%s -> %s -> %s -> %s', $this->l('Preferences'), $this->l('SEO and URLs'), $this->l('Set user-friendly URL on'), $this->l('Save')).'<br>'
            .'</p>';

//        var_dump(new Category(15));
        if ($res = $this->checkCategory()) {
            $err = $this->l('You need to fix duplicate URL entries for categories:').'<br><br><br>';
            $err .= "<table>";
            foreach ($res as $row) {
                $err .= "<table border='1'>";
                $err .= "<tr>";
                $err .= "<td colspan='10'>".(new Link())->getCategoryLink($row[0]['id_category'])."</td>";
                $err .= "</tr>";
                $err .= "</table>";
                $err .= "<table border='1' style='width: 100%'>";

                $err .= "<tr>";
                $err .= "<th>Category Id</th>";
                $err .= "<th>Category Name</th>";
                $err .= "<th>Parent</th>";
                $err .= "<th>Rewirite URL</th>";
                $err .= "<th>Language</th>";
                $err .= "<th>Actions</th>";
                $err .= "</tr>";
                foreach ($row as $p) {
                    $lang = $this->context->language->getLanguage($p['id_lang']);
                    $category = new Category($p['id_category']);
                    $err .= "<tr>";
                    $err .= "<td>".$category->id."</td>";
                    $err .= "<td>".$category->name[$lang['id_lang']]."</td>";
                    $err .= "<td>".(new Category($category->id_parent))->name[$lang['id_lang']]."</td>";
                    $err .= "<td>".$category->link_rewrite[$lang['id_lang']]."</td>";
                    $err .= "<td>".$lang['name']."</td>";
                    $err .= "<td><a target='_blank' href='".$link->getAdminLink('', true, array('route' => 'admin_categories_edit', 'categoryId'=> $category->id))."'>EDIT</a></td>";
                    $err .= "</tr>";
                }
                $err .= "</table>";
                $err .= "<tr><div style='height: 20px;'></div></tr>";
            }
            $err.="</table>";
            $output .= $this->displayWarning($err);
        } else {
            $output .= $this->displayConfirmation($this->l('Nice. You have no duplicate URL entry for products.'));
        }

        if ($res = $this->checkProducts()) {
            $err = $this->l('You need to fix duplicate URL entries for products:').'<br><br><br>';
            $err .= "<table>";
            foreach ($res as $row) {
                $err .= "<table border='1'>";
                $err .= "<tr>";
                $err .= "<td colspan='10'>".(new Link())->getProductLink($row[0]['id_product'])."</td>";
                $err .= "</tr>";
                $err .= "</table>";
                $err .= "<table border='1' style='width: 100%'>";

                $err .= "<tr>";
                $err .= "<th>Product Id</th>";
                $err .= "<th>Product Name</th>";
                $err .= "<th>Rewirite URL</th>";
                $err .= "<th>Language</th>";
                $err .= "<th>Actions</th>";
                $err .= "</tr>";
                foreach ($row as $p) {
                    $lang = $this->context->language->getLanguage($p['id_lang']);
                    $product = new Product($p['id_product']);
                    $err .= "<tr>";
                    $err .= "<td>".$product->id."</td>";
                    $err .= "<td>".$product->name[$lang['id_lang']]."</td>";
                    $err .= "<td>".$product->link_rewrite[$lang['id_lang']]."</td>";
                    $err .= "<td>".$lang['name']."</td>";
                    $err .= "<td><a target='_blank' href='".$link->getAdminLink('AdminProducts', true, array('route' => 'admin_product_form', 'id'=> $product->id))."#tab-step5'>EDIT</a></td>";
                    $err .= "</tr>";
                }
                $err .= "</table>";
                $err .= "<tr><div style='height: 20px;'></div></tr>";
            }
            $err.="</table>";
            $output .= $this->displayWarning($err);
        } else {
            $output .= $this->displayConfirmation($this->l('Nice. You have no duplicate URL entry for products.'));
        }


        return '<div class="panel">'.$output.'</div>';
    }

    private function checkCategory(){
        $dispacher = Dispatcher::getInstance();
        $lang = Language::getLanguages(false);
        $shopId = Shop::getContextShopID();
        $rule = $dispacher->getRoutes()[$shopId][$lang[0]['id_lang']]['category_rule'];

        $short_link = ltrim(parse_url((new Link())->getCategoryLink(34), PHP_URL_PATH), '/');
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $rule['regexp']);
        preg_match($regexp, $short_link, $kw);

        $map = [
            'id_category' => '`cl`.`id_category`',
            'encode_id' => '`cl`.`id_category`',
            'category_rewrite' => '`cl`.`link_rewrite`',
            'categories' => '`clp`.`link_rewrite`',
        ];

        $k = [];
        foreach ($kw as $keyword => $conf) {
            if (is_string($keyword)) {
                $k[] = $map[$keyword];
            }
        }
        $k = array_unique($k);

        $subSql ="
            SELECT 
                group_concat(cl.`id_category`) as ids
            FROM `" . _DB_PREFIX_ . "category_lang` cl 
            JOIN `" . _DB_PREFIX_ . "category` c ON c.id_category=cl.id_category 
            JOIN `" . _DB_PREFIX_ . "category` cp ON c.id_parent=cp.id_category 
            JOIN `" . _DB_PREFIX_ . "category_lang` clp ON cp.id_category=clp.id_category 
            GROUP BY cl.id_lang, ".implode(',', $k)."
            HAVING count(`cl`.`id_category`) > 1
       ";
        $subRes = Db::getInstance()->ExecuteS($subSql);

        foreach ($subRes as $v){
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'category_lang`
            WHERE `id_category`
            IN ('.$v["ids"].')';

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql .= ' AND `id_shop` = '.(int) Shop::getContextShopID();
            }
            $res[] = Db::getInstance()->ExecuteS($sql);
        }

        return $res;
    }

    private function checkProducts(){
        $dispacher = Dispatcher::getInstance();
        $lang = Language::getLanguages(false);
        $shopId = Shop::getContextShopID();
        $rule = $dispacher->getRoutes()[$shopId][$lang[0]['id_lang']]['product_rule'];
        $short_link = ltrim(parse_url((new Link())->getProductLink(81), PHP_URL_PATH), '/');
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $rule['regexp']);
        preg_match($regexp, $short_link, $kw);

        $map = [
            'id' => '`pl`.`id_product`',
            'product_rewrite' => '`pl`.`link_rewrite`',
            'reference' => '`p`.`reference`',
            'encoded_id_product' => '`pl`.`id_product`',
        ];

        $k = [];
        foreach ($kw as $keyword => $conf) {
            if (is_string($keyword)) {
                $k[] = $map[$keyword];
            }
        }
        $k = array_unique($k);

        $subSql ='SELECT group_concat(`pl`.`id_product`) as ids  FROM `'._DB_PREFIX_.'product_lang` as pl
            JOIN `'._DB_PREFIX_.'product` as p on `pl`.`id_product` = `p`.`id_product`
            GROUP BY `pl`.`id_lang`, '. implode(',', $k).'
            HAVING count(`pl`.`id_product`) > 1';

        $subRes = Db::getInstance()->ExecuteS($subSql);

        foreach ($subRes as $v) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product_lang`
            WHERE `id_product`
            IN ('.$v["ids"].')';

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql .= ' AND `id_shop` = ' . (int)Shop::getContextShopID();
            }

            $res[] = Db::getInstance()->ExecuteS($sql);
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

        $languages = Language::getLanguages(false);
        foreach ($languages AS $lang) {
            $sql = "
                INSERT INTO `" . _DB_PREFIX_ . "translation` 
                    (`id_lang`, `key`, `translation`, `domain`, `theme`) 
                VALUES (
                    ".$lang['id_lang'].", 
                    'There are several available keywords for each route listed below; note that keywords with * are required!', 
                    'There are several available keywords for each route listed below; note that keywords with * can be used to identify items. They can be combained for accuracy for examle:
                    2 products with same name can be distinguished by using {rewirite}{-:id}, {rewirite}{::encoded_id}, {rewirite}{::reference}, {rewirite}{-:id}{::reference} 
                    At least one keyword with * must be used. Keywords that conatin encoded will be encoded', 
                    'AdminShopparametersNotification', 
                    NULL
            )";
            Db::getInstance()->Execute($sql);
        }

        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."translation`  WHERE `key` = 'There are several available keywords for each route listed below; note that keywords with * are required!'";
        Db::getInstance()->Execute($sql);

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }
}

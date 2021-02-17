<?php

class Link extends LinkCore
{

    public function getProductLink(
        $product,
        $alias = null,
        $category = null,
        $ean13 = null,
        $idLang = null,
        $idShop = null,
        $ipa = null,
        $force_routes = false,
        $relativeProtocol = false,
        $addAnchor = false,
        $extraParams = []
    ) {
        $dispatcher = Dispatcher::getInstance();
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);
        $params = [];
        if (!is_object($product)) {
            if (is_array($product) && isset($product['id_product'])) {
                $params['id'] = $product['id_product'];
            } elseif ((int) $product) {
                $params['id'] = $product;
            } else {
                throw new PrestaShopException('Invalid product vars');
            }
        } else {
            $params['id'] = $product->id;
        }
        if (empty($ipa)) {
            $ipa = null;
        }
        $product = $this->getProductObject($product, $idLang, $idShop);

        $params['id_product_attribute'] = $ipa;
        $params['rewrite'] = (!$alias) ? $product->getFieldByLang('link_rewrite') : $alias;
        $params['ean13'] = (!$ean13) ? $product->ean13 : $ean13;

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($product->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($product->getFieldByLang('meta_title'));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'manufacturer', $idShop)) {
            $params['manufacturer'] = Tools::str2url($product->isFullyLoaded ? $product->manufacturer_name : Manufacturer::getNameById($product->id_manufacturer));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'supplier', $idShop)) {
            $params['supplier'] = Tools::str2url($product->isFullyLoaded ? $product->supplier_name : Supplier::getNameById($product->id_supplier));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'price', $idShop)) {
            $params['price'] = $product->isFullyLoaded ? $product->price : Product::getPriceStatic($product->id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specificPrice);
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'tags', $idShop)) {
            $params['tags'] = Tools::str2url($product->getTags($idLang));
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'category', $idShop)) {
            $params['category'] = (!$category) ? $product->category : $category;
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'reference', $idShop)) {
            $params['reference'] = $product->reference;
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'encoded_id_product', $idShop)) {
            $params['encoded_id_product'] = base64_encode($product->id);
        }
        if ($dispatcher->hasKeyword('product_rule', $idLang, 'categories', $idShop)) {
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = [];
            foreach ($product->getParentCategories($idLang) as $cat) {
                if (!in_array($cat['id_category'], Link::$category_disable_rewrite)) {
                    $cats[] = $cat['link_rewrite'];
                }
            }
            $params['categories'] = implode('/', $cats);
        }
        $anchor = $ipa ? $product->getAnchor((int) $ipa, (bool) $addAnchor) : '';

        return $url . $dispatcher->createUrl('product_rule', $idLang, array_merge($params, $extraParams), $force_routes, $anchor, $idShop);
    }


    public function getCategoryLink($category, $alias = null, $id_lang = null, $selected_filters = null, $id_shop = null, $relative_protocol = false)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        $url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);
        if (!is_object($category)) {
            $category = new Category($category, $id_lang);
        }
        $params = array();
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;
        if (empty($selected_filters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }
        $dispatcher = Dispatcher::getInstance();
        if ($dispatcher->hasKeyword('category_rule', $id_lang, 'categories')) {
            $p_cats = array();
            foreach ($category->getParentsCategories($id_lang) as $p_cat) {
                if (!in_array($p_cat['id_category'], array_merge(self::$category_disable_rewrite, array($category->id)))) {
                    $p_cats[] = $p_cat['link_rewrite'];
                }
            }
            $params['categories'] = implode('/', array_reverse($p_cats));
        }
        return $url.$dispatcher->createUrl($rule, $id_lang, $params, $this->allow, '', $id_shop);
    }

    public function getPaginationLink($type, $id_object, $nb = false, $sort = false, $pagination = false, $array = false)
    {
        if (!$type && !$id_object) {
            $method_name = 'get'.Dispatcher::getInstance()->getController().'Link';
            if (method_exists($this, $method_name) && isset($_GET['id_'.Dispatcher::getInstance()->getController()])) {
                $type = Dispatcher::getInstance()->getController();
                $id_object = $_GET['id_'.$type];
            }
        }
        if ($type && $id_object) {
            $url = $this->{'get'.$type.'Link'}($id_object, null);
        } else {
            if (isset(Context::getContext()->controller->php_self)) {
                $name = Context::getContext()->controller->php_self;
            } else {
                $name = Dispatcher::getInstance()->getController();
            }
            $url = $this->getPageLink($name);
        }
        $vars = array();
        $vars_nb = array('n', 'search_query');
        $vars_sort = array('orderby', 'orderway');
        $vars_pagination = array('p');
        foreach ($_GET as $k => $value) {
            if ($k != 'id_'.$type && $k != 'controller' && $k != $type.'_rewrite' ) {
                if (Configuration::get('PS_REWRITING_SETTINGS') && ($k == 'isolang' || $k == 'id_lang')) {
                    continue;
                }
                $if_nb = (!$nb || ($nb && !in_array($k, $vars_nb)));
                $if_sort = (!$sort || ($sort && !in_array($k, $vars_sort)));
                $if_pagination = (!$pagination || ($pagination && !in_array($k, $vars_pagination)));
                if ($if_nb && $if_sort && $if_pagination) {
                    if (!is_array($value)) {
                        $vars[urlencode($k)] = $value;
                    } else {
                        foreach (explode('&', http_build_query(array($k => $value), '', '&')) as $key => $val) {
                            $data = explode('=', $val);
                            $vars[urldecode($data[0])] = $data[1];
                        }
                    }
                }
            }
        }
        if (!$array) {
            if (count($vars)) {
                return $url.(!strstr($url, '?') && ($this->allow == 1 || $url == $this->url) ? '?' : '&').http_build_query($vars, '', '&');
            } else {
                return $url;
            }
        }
        $vars['requestUrl'] = $url;
        if ($type && $id_object) {
            $vars['id_'.$type] = (is_object($id_object) ? (int) $id_object->id : (int) $id_object);
        }
        if (!$this->allow == 1) {
            $vars['controller'] = Dispatcher::getInstance()->getController();
        }
        return $vars;
    }
}

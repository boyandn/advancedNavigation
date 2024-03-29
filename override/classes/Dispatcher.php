<?php

class Dispatcher extends DispatcherCore
{
    protected function loadRoutes($id_shop = null)
    {
        $this->default_routes = array(
            'category_rule' => array(
                'controller' => 'category',
                'rule' => '{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_category'),
                    'categories' => array('regexp' => '[/_a-zA-Z0-9\pL\pS-]*', 'param' => 'categories'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'category_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'encode_id' => array('regexp' => '[-A-Za-z0-9+/]*={0,3}', 'param' => 'encode_id'),
                ),
            ),
            'supplier_rule' => array(
                'controller' => 'supplier',
                'rule' => 'supplier/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_supplier'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'supplier_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                ),
            ),
            'manufacturer_rule' => array(
                'controller' => 'manufacturer',
                'rule' => 'manufacturer/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_manufacturer'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'manufacturer_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                ),
            ),
            'cms_rule' => array(
                'controller' => 'cms',
                'rule' => 'info/{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_cms'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'cms_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                ),
            ),
            'cms_category_rule' => array(
                'controller' => 'cms',
                'rule' => 'info/{rewrite}/',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_cms_category'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'cms_category_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                ),
            ),
            'module' => array(
                'controller' => null,
                'rule' => 'module/{module}/{controller}',
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9-]+', 'param' => 'module', 'require' => true),
                    'controller' => array('regexp' => '[_a-zA-Z0-9-]+', 'param' => 'controller', 'require' => true),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'product_rule' => array(
                'controller' => 'product',
                'rule' => '{category:/}{rewrite}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_product'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'product_rewrite', 'require' => true),
                    'ean13' => array('regexp' => '[0-9]{8,17}'),
                    'category' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'categories' => array('regexp' => '[/_a-zA-Z0-9\pL-]*'),
                    'reference' => array('regexp' => '[_a-zA-Z0-9\pL-]*','param' => 'reference'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'manufacturer' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'supplier' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'price' => array('regexp' => '[0-9\.,]*'),
                    'tags' => array('regexp' => '[a-zA-Z0-9\pL-]*'),
                    'encoded_id_product' => array('regexp' => '[-A-Za-z0-9+/]*={0,3}', 'param' => 'encoded_id_product'),
                ),
            ),
            'layered_rule' => array(
                'controller' => 'category',
                'rule' => '{rewrite}/f/{selected_filters}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+','param' => 'id_category'),
                    'selected_filters' => array('regexp' => '.*', 'param' => 'selected_filters', 'require' => true),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL-]*', 'param' => 'category_rewrite', 'require' => true),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9\pL-]*'),
                ),
            ),
        );

        parent::loadRoutes($id_shop);
    }

    public function validateRoute($route_id, $rule, &$errors = [])
    {
        $errors = [];
        if (!isset($this->default_routes[$route_id])) {
            return false;
        }

        foreach ($this->default_routes[$route_id]['keywords'] as $keyword => $data) {
            if (isset($data['require']) && !preg_match('#\{([^{}]*:)?' . $keyword . '(:[^{}]*)?\}#', $rule)) {
                $errors[] = $keyword;
            }
        }

        return (count($errors)) ? false : true;
    }

    public function addRoute($route_id, $rule, $controller, $id_lang = null, array $keywords = array(), array $params = array(), $id_shop = null)
    {
        if (isset(Context::getContext()->language) && $id_lang === null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if (isset(Context::getContext()->shop) && $id_shop === null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        $regexp = preg_quote($rule, '#');
        if ($keywords) {
            $transform_keywords = array();
            preg_match_all('#\\\{(([^{}]*)\\\:)?('.implode('|', array_keys($keywords)).')(\\\:([^{}]*))?\\\}#', $regexp, $m);
            for ($i = 0, $total = count($m[0]); $i < $total; ++$i) {
                $prepend = $m[2][$i];
                $keyword = $m[3][$i];
                $append = $m[5][$i];
                $transform_keywords[$keyword] = array(
                    'required' => isset($keywords[$keyword]['required']),
                    'prepend' => stripslashes($prepend),
                    'append' => stripslashes($append),
                );
                $prepend_regexp = $append_regexp = '';
                if ($prepend || $append) {
                    $prepend_regexp = '('.$prepend;
                    $append_regexp = $append.')??'; // fix greediness (step 1)
                }
                if (isset($keywords[$keyword]['param'])) {
                    $regexp = str_replace($m[0][$i], $prepend_regexp.'(?P<'.$keywords[$keyword]['param'].'>'.$keywords[$keyword]['regexp'].')'.$append_regexp, $regexp);
                } else {
                    $regexp = str_replace($m[0][$i], $prepend_regexp.'('.$keywords[$keyword]['regexp'].')'.$append_regexp, $regexp);
                }
            }
            $keywords = $transform_keywords;
        }
        $regexp = '#^/'.$regexp.'$#uU'; // fix greediness (step 2)
        if (!isset($this->routes[$id_shop])) {
            $this->routes[$id_shop] = array();
        }
        if (!isset($this->routes[$id_shop][$id_lang])) {
            $this->routes[$id_shop][$id_lang] = array();
        }

        $this->routes[$id_shop][$id_lang][$route_id] = array(
            'rule' => $rule,
            'regexp' => $regexp,
            'controller' => $controller,
            'keywords' => $keywords,
            'params' => $params,
        );
    }

    public function getController($id_shop = null)
    {

        if (defined('_PS_ADMIN_DIR_')) {
            $_GET['controllerUri'] = Tools::getvalue('controller');
        }

        if ($this->controller) {
            $_GET['controller'] = $this->controller;
            return $this->controller;
        }

        if (null === $id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        $controller = Tools::getValue('controller');
        $curr_lang_id = Context::getContext()->language->id;


        if (isset($controller) && is_string($controller) && preg_match('/^([0-9a-z_-]+)\?(.*)=(.*)$/Ui', $controller, $m)) {
            $controller = $m[1];
            if (isset($_GET['controller'])) {
                $_GET[$m[2]] = $m[3];
            } elseif (isset($_POST['controller'])) {
                $_POST[$m[2]] = $m[3];
            }
        }
        if (!Validate::isControllerName($controller)) {
            $controller = false;
        }

        if ($this->use_routes && !$controller && !defined('_PS_ADMIN_DIR_')) {
            if (!$this->request_uri) {
                return strtolower($this->controller_not_found);
            }
            $controller = $this->controller_not_found;
            if (!preg_match('/\.(gif|jpe?g|png|css|js|ico)$/i', $this->request_uri)) {
                if ($this->empty_route) {
                    $this->addRoute($this->empty_route['routeID'], $this->empty_route['rule'], $this->empty_route['controller'], $curr_lang_id, array(), array(), $id_shop);
                }
                list($uri) = explode('?', $this->request_uri);
                if (isset($this->routes[$id_shop][$curr_lang_id])) {

                    $route = array();
                    foreach ($this->routes[$id_shop][$curr_lang_id] as $k => $r) {
                        if (preg_match($r['regexp'], $uri, $m)) {
                            $isTemplate = false;
                            $module = isset($r['params']['module']) ? $r['params']['module'] : '';
                            switch ($r['controller'].$module) { // Avoid name collision between core and modules' controllers
                                case 'supplier':
                                case 'manufacturer':
                                    if (false !== strpos($r['rule'], '{')) {
                                        $isTemplate = true;
                                    }
                                    break;
                                case 'cms':
                                case 'product':
                                    $isTemplate = true;
                                    break;
                                case 'category':
                                    if (false === strpos($r['rule'], 'selected_filters')) {
                                        $isTemplate = true;
                                    }
                                    break;
                            }
                            if (!$isTemplate) {
                                $route = $r;
                                break;
                            }
                        }
                    }
                    if (empty($route)) {
                        $short_link = ltrim(parse_url($uri, PHP_URL_PATH), '/');
                        $route = $this->checkRoute('product_rule', $short_link, $id_shop, $curr_lang_id);

                        if (!empty($route['controller'])) {
                            $controller = $route['controller'];
                        }else{
                            $controller = $this->controller_not_found;
                        }
                    }

                    if (!empty($route)) {
                        if (preg_match($route['regexp'], $uri, $m)) {
                            foreach ($m as $k => $v) {
                                if (!is_numeric($k)) {
                                    $_GET[$k] = $v;
                                }
                            }
                            $controller = $route['controller'] ? $route['controller'] : $_GET['controller'];
                            if (!empty($route['params'])) {
                                foreach ($route['params'] as $k => $v) {
                                    $_GET[$k] = $v;
                                }
                            }
                            if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9]+)$#i', $controller, $m)) {
                                $_GET['module'] = $m[1];
                                $_GET['fc'] = 'module';
                                $controller = $m[2];
                            }
                            if (isset($_GET['fc']) && $_GET['fc'] == 'module') {
                                $this->front_controller = self::FC_MODULE;
                            }
                        }
                    }
                }
            }
            if ($controller == 'index' || $this->request_uri == '/index.php') {
                $controller = $this->default_controller;
            }
            $this->controller = $controller;
        } else { // Default mode, take controller from url
            $this->controller = $controller;
        }

        $this->controller = str_replace('-', '', $this->controller);
        $_GET['controller'] = $this->controller;
        return $this->controller;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    private function checkRoute($routeName, $short_link, $id_shop, $curr_lang_id)
    {
        $matched = false;
        $next = false;

        switch ($routeName)
        {
            case 'product_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['product_rule'];
                $matched = self::isProductLink($short_link, $route);
                $next = 'category_rule';
                break;
            case 'category_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['category_rule'];
                $matched = self::isCategoryLink($short_link, $route);
                $next = 'cms_rule';
                break;
            case 'cms_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['cms_rule'];
                $matched = self::isCmsLink($short_link, $route);
                $next = 'cms_category_rule';
                break;
            case 'cms_category_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['cms_category_rule'];
                $matched = self::isCmsCategoryLink($short_link, $route);
                $next = 'manufacturer_rule';
                break;
            case 'manufacturer_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['manufacturer_rule'];
                $matched = self::isManufacturerLink($short_link, $route);
                $next = 'supplier_rule';
                break;
            case 'supplier_rule':
                $route = $this->routes[$id_shop][$curr_lang_id]['supplier_rule'];
                $matched = self::isSupplierLink($short_link, $route);
                $next = false;
                break;
            default:
                $matched=true;
                $route = [];
                break;
        }

        return ($matched)?$route:$this->checkRoute($next, $short_link, $id_shop, $curr_lang_id);
    }

    private static function isProductLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);
        $id_product = 0;
        if (!empty($kw['id_product'])) {
            $id_product = $kw['id_product'];
        }
        if (!empty($kw['encoded_id_product'])) {
            $id_product = base64_decode($kw['encoded_id_product']);
        }

        if (!empty($kw['product_rewrite'])) {
            $lang_id = (int) Context::getContext()->language->id;
            $sql = 'SELECT `pl`.`id_product` 
            FROM `' . _DB_PREFIX_ . 'product_lang` as pl';
            if (!empty($kw['reference'])){
                $sql .= ' JOIN `' . _DB_PREFIX_ . 'product` as p on `pl`.`id_product` = `p`.`id_product`';
            }
            $sql .= ' WHERE `pl`.`link_rewrite` = \'' . pSQL(str_replace('.html', '', $kw['product_rewrite'])) . '\' 
            AND `pl`.`id_lang` = ' . $lang_id;

            if (!empty($kw['reference'])) {
                $sql .= ' AND `p`.`reference` = \'' . $kw['reference'] . '\'';
            }

            if ($id_product !== 0) {
                $sql .= ' AND `pl`.`id_product` = ' . $id_product;
            }

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql .= ' AND `id_shop` = '.(int) Shop::getContextShopID();
            }
            $id = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

            if ( $id > 0) {
                $_GET['id_product'] = $id;
                return true;
            }
        }

        return false;
    }

    private static function isCategoryLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);
        $id_lang_cat = 0;
        $id_category = 0;
        if (!empty($kw['id_category'])) {
            $id_category = $kw['id_category'];
        }
        if (!empty($kw['encode_id'])) {
            $id_category = base64_decode($kw['encode_id']);
        }
        if (empty($kw['category_rewrite'])) {
            return false;
        }
        if (empty($kw['categories'])) {
            $sql = 'SELECT `id_category`
            FROM `' . _DB_PREFIX_ . 'category_lang` cl
            WHERE `cl`.`link_rewrite` = \'' . pSQL($kw['category_rewrite']) . '\' AND `cl`.`id_lang` = ' . (int)Context::getContext()->language->id;
        }else{
            $parent = explode("/", $kw['categories']);
            $sql = "
            SELECT 
                cl.`id_category` 
            FROM `" . _DB_PREFIX_ . "category_lang` cl 
            JOIN `" . _DB_PREFIX_ . "category` c ON c.id_category=cl.id_category 
            JOIN `" . _DB_PREFIX_ . "category` cp ON c.id_parent=cp.id_category 
            JOIN `" . _DB_PREFIX_ . "category_lang` clp ON cp.id_category=clp.id_category 
            WHERE cl.`link_rewrite` = '" . pSQL($kw['category_rewrite']) . "' 
                and clp.`link_rewrite` = '" . pSQL(end($parent)) . "'
                AND `cl`.`id_lang` = " . (int)Context::getContext()->language->id;
        }

        if ($id_category !== 0) {
            $sql .= ' AND `cl`.`id_category` = ' . $id_category;
        }

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND `cl`.`id_shop` = '.(int) Shop::getContextShopID();
        }
        $id_category = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($id_category !== 0 )
        {
            $_GET['id_category'] = $id_category;
            return true;
        }

        return false;
    }

    private static function isCmsLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);
        $id_cms = 0;
        if (!empty($kw['id_cms'])) {
            $id_cms = $kw['id_cms'];
        }

        if (empty($kw['cms_rewrite'])) {
            return false;
        }
        $sql = 'SELECT l.`id_cms`
            FROM `'._DB_PREFIX_.'cms_lang` l
            LEFT JOIN `'._DB_PREFIX_.'cms_shop` s ON (l.`id_cms` = s.`id_cms`)
            WHERE l.`link_rewrite` = \''.pSQL($kw['cms_rewrite']).'\'';

        if ($id_cms !== 0) {
            $sql .= ' AND `l`.`$id_cms` = ' . $id_cms;
        }

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND s.`id_shop` = '.(int) Shop::getContextShopID();
        }
        $id_cms = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($id_cms !== 0){
            $_GET['id_cms'] = $id_cms;
            return true;
        }
        return false;
    }

    private static function isCmsCategoryLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);

        $id_cms_cat = 0;
        if (!empty($kw['id_cms_cat'])) {
            $id_cms_cat = $kw['id_cms_cat'];
        }
        if (empty($kw['cms_category_rewrite'])) {
            if (0 === strpos('/'.$route['rule'], $short_link)) {
                return true;
            }
            return false;
        }
        $sql = 'SELECT l.`id_cms_category`
            FROM `'._DB_PREFIX_.'cms_category_lang` l
            LEFT JOIN `'._DB_PREFIX_.'cms_category_shop` s ON (l.`id_cms_category` = s.`id_cms_category`)
            WHERE l.`link_rewrite` = \''.$kw['cms_category_rewrite'].'\'';

        if ($id_cms_cat !== 0) {
            $sql .= ' AND `l`.`id_cms_category` = ' . $id_cms_cat;
        }

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND s.`id_shop` = '.(int) Shop::getContextShopID();
        }
        $id_cms_cat = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($id_cms_cat !== 0){
            $_GET['id_cms_cat'] = $id_cms_cat;
            return true;
        }
        return false;
    }

    private static function isManufacturerLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);

        $id_manufacturer=0;
        if (!empty($kw['id_manufacturer'])) {
            $id_manufacturer = $kw['id_manufacturer'];
        }
        if (empty($kw['manufacturer_rewrite'])) {
            if (0 === strpos('/'.$route['rule'], $short_link)) {
                return true;
            }
            return false;
        }
        $manufacturer = str_replace('-', '_', $kw['manufacturer_rewrite']);
        $sql = 'SELECT m.`id_manufacturer`
            FROM `'._DB_PREFIX_.'manufacturer` m
            LEFT JOIN `'._DB_PREFIX_.'manufacturer_shop` s ON (m.`id_manufacturer` = s.`id_manufacturer`)
            WHERE LOWER(m.`name`) LIKE \''.pSQL($manufacturer).'\'';

        if ($id_manufacturer !== 0) {
            $sql .= ' AND `m`.`id_manufacturer` = ' . $id_manufacturer;
        }

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND s.`id_shop` = '.(int) Shop::getContextShopID();
        }
        $id_manufacturer = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($id_manufacturer !== 0){
            $_GET['id_manufacturer'] = $id_manufacturer;
            return true;
        }
        return false;
    }

    private static function isSupplierLink($short_link, $route)
    {
        $short_link = preg_replace('#\.html?$#', '', '/'.$short_link);
        $regexp = preg_replace('!\\\.html?\\$#!', '$#', $route['regexp']);
        preg_match($regexp, $short_link, $kw);

        $id_supplier=0;
        if (!empty($kw['id_supplier'])) {
            $id_supplier = $kw['id_supplier'];
        }
        if (empty($kw['supplier_rewrite'])) {
            if (0 === strpos('/'.$route['rule'], $short_link)) {
                return true;
            }
            return false;
        }
        $supplier = str_replace('-', '_', $kw['supplier_rewrite']);
        $sql = 'SELECT sp.`id_supplier`
            FROM `'._DB_PREFIX_.'supplier` sp
            LEFT JOIN `'._DB_PREFIX_.'supplier_shop` s ON (sp.`id_supplier` = s.`id_supplier`)
            WHERE LOWER(sp.`name`) LIKE \''.pSQL($supplier).'\'';

        if ($id_supplier !== 0) {
            $sql .= ' AND `sp`.`id_supplier` = ' . $id_supplier;
        }


        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND s.`id_shop` = '.(int) Shop::getContextShopID();
        }
        $id_supplier = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($id_supplier !== 0){
            $_GET['id_supplier'] = $id_supplier;
            return true;
        }
        return false;
    }

    public function createUrl(
        $route_id,
        $id_lang = null,
        array $params = [],
        $force_routes = false,
        $anchor = '',
        $id_shop = null
    ) {
        if ($id_lang === null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if ($id_shop === null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        if (!isset($this->routes[$id_shop])) {
            $this->loadRoutes($id_shop);
        }

        if (!isset($this->routes[$id_shop][$id_lang][$route_id])) {
            $query = http_build_query($params, '', '&');
            $index_link = $this->use_routes ? '' : 'index.php';

            return ($route_id == 'index') ? $index_link . (($query) ? '?' . $query : '') :
                ((trim($route_id) == '') ? '' : '?controller=' . $route_id) . (($query) ? '&' . $query : '') . $anchor;
        }
        $route = $this->routes[$id_shop][$id_lang][$route_id];
        // Check required fields
        $query_params = isset($route['params']) ? $route['params'] : [];
        foreach ($route['keywords'] as $key => $data) {
            if (!$data['required']) {
                continue;
            }

            if (!array_key_exists($key, $params)) {
                throw new PrestaShopException('Dispatcher::createUrl() miss required parameter "' . $key . '" for route "' . $route_id . '"');
            }
            if (isset($this->default_routes[$route_id])) {
                $query_params[$this->default_routes[$route_id]['keywords'][$key]['param']] = $params[$key];
            }
        }

        // Build an url which match a route
        if ($this->use_routes || $force_routes) {
            $url = $route['rule'];
            $add_param = [];

            foreach ($params as $key => $value) {
                if (!isset($route['keywords'][$key])) {
                    if (!isset($this->default_routes[$route_id]['keywords'][$key])) {
                        $add_param[$key] = $value;
                    }
                } else {
                    if ($params[$key]) {
                        $parameter = $params[$key];
                        if (is_array($parameter)) {
                            if (array_key_exists($id_lang, $parameter)) {
                                $parameter = $parameter[$id_lang];
                            } else {
                                // made the choice to return the first element of the array
                                $parameter = reset($parameter);
                            }
                        }
                        $replace = $route['keywords'][$key]['prepend'] . $parameter . $route['keywords'][$key]['append'];
                    } else {
                        $replace = '';
                    }
                    $url = preg_replace('#\{([^{}]*:)?' . $key . '(:[^{}]*)?\}#', $replace, $url);
                }
            }
            $url = preg_replace('#\{([^{}]*:)?[a-z0-9_]+?(:[^{}]*)?\}#', '', $url);
            if (count($add_param)) {
                $url .= '?' . http_build_query($add_param, '', '&');
            }
        } else {
            // Build a classic url index.php?controller=foo&...
            $add_params = [];
            foreach ($params as $key => $value) {
                if (!isset($route['keywords'][$key]) && !isset($this->default_routes[$route_id]['keywords'][$key])) {
                    $add_params[$key] = $value;
                }
            }

            if (!empty($route['controller'])) {
                $query_params['controller'] = $route['controller'];
            }
            $query = http_build_query(array_merge($add_params, $query_params), '', '&');
            if ($this->multilang_activated) {
                $query .= (!empty($query) ? '&' : '') . 'id_lang=' . (int) $id_lang;
            }
            $url = 'index.php?' . $query;
        }
        return $url . $anchor;
    }
}

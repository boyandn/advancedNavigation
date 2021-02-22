<?php

class AdminMetaControllerCore extends AdminController
{
    public function addFieldRoute($route_id, $title)
    {
        $keywords = array();
        foreach (Dispatcher::getInstance()->default_routes[$route_id]['keywords'] as $keyword => $data) {
            $keywords[] = ((isset($data['require'])) ? '<span class="red">' . $keyword . '*</span>' : $keyword);
        }

        $this->fields_options['routes']['fields']['PS_ROUTE_' . $route_id] = array(
            'title' => $title,
            'desc' => sprintf($this->trans('Keywords: %s', array(), 'Admin.ShopParameters.Feature'), implode(', ', $keywords)),
            'validation' => 'isString',
            'type' => 'text',
            'size' => 70,
            'defaultValue' => Dispatcher::getInstance()->default_routes[$route_id]['rule'],
        );
    }
}
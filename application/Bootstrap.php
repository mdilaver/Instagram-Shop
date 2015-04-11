<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap

{
    protected function _initDb()
    {
        $resource = $this->getPluginResource('db');
        $db = $resource->getDbAdapter();
        Zend_Registry::set('db', $db);
        Zend_Registry::set('config', $this->getOptions());
        $aConfig = $this->getOptions();
        Zend_Registry::set('facebook_client_id', $aConfig['facebook']['client_id']);
        Zend_Registry::set('facebook_client_secret', $aConfig['facebook']['client_secret']);
        Zend_Registry::set('facebook_redirect_uri', $aConfig['facebook']['redirect_uri']);
        Zend_Registry::set('facebook_scope', $aConfig['facebook']['scope']);
        Zend_Registry::set('instagram_client_id', $aConfig['instagram']['client_id']);
        Zend_Registry::set('instagram_client_secret', $aConfig['instagram']['client_secret']);
        Zend_Registry::set('instagram_redirect_uri', $aConfig['instagram']['redirect_uri']);
        Zend_Registry::set('instagram_grant_type', $aConfig['instagram']['grant_type']);
        Zend_Registry::set('instagram_scope', $aConfig['instagram']['scope']);
    }

    protected function _initRoutes()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();


        // username route
        $router->addRoute('magaza', new Route(
            '/:magaza',
            array(
                'controller' => 'index',
                'action' => 'magaza'
            )
        ));
        $router->addRoute('urun', new Zend_Controller_Router_Route(
            'satinal/:magaza_adi/:urunadi',
            array(
                'controller' => 'magaza',
                'action' => 'urungoster'
            )
        ));

    }

}


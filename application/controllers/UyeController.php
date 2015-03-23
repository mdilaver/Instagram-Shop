<?php

class UyeController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->_baseUrl = "/";
        $this->view->baseUrl = "/";
        $this->session = new Zend_Session_Namespace('kullaniciSession');
    }

    public function indexAction()
    {
        $content ="üye iD Uğur UğUR ı ni ğüişö ";
        Zend_Debug::dump($content);
        $string = mbr($content);
        Zend_Debug::dump($string);exit;
        $string = preg_replace("/[-\(\)]/","",$string);
        Zend_Debug::dump($string);
        preg_match_all('/\(\((.*?)\)\)/', $content, $matches);
        foreach ($matches[1] as $a ){
            echo $a." ";
        }
        $config = new Zend_Navigation(array(
            array(
                'label' => 'Page 1',
                'id' => 'home-link',
                'uri' => '/'
            ),
            array(
                'label' => 'Zend',
                'uri' => 'http://www.zend-project.com/'
            )));


        $this->view->navigation($config);
    }
}






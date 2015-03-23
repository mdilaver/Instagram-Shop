<?php 
//kendi projemizdeki tüm ilgili action'lari budan türetebiliriz..

class Admin_Action extends Zend_Controller_Action
{
public function init()
    {
        parent::init();
        $this->_helper->layout()->setLayout("layout");
        $session = new Zend_Session_Namespace('kullaniciSession');
        Zend_Session::rememberMe(600);
        $this->user = $session->user;
        $this->view->session = $session;
  
        if (!$session->user)
        {
            $this->_redirect('/kullanici/giris');
            echo 'Bu SAYFAYA GİRİŞ İZNİN YOK BRO!';
        }
        if (($session->rol !='admin') )
        {
            $this->_redirect('/dersalma');
            echo 'Bu SAYFAYA GİRİŞ İZNİN YOK BRO!';
        }
        
    }
}
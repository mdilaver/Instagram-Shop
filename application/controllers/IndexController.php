<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {   $this->_helper->layout->setLayout('anasayfa_header');
        $tbl = new Tblsatici();
        $data = $tbl->fetchAll()->toArray();
        $this->view->magazalar = $data;
        $session = new Zend_Session_Namespace('kullaniciSession');
        $this->view->kullanici = $session;
    }

    public function magazaAction()
    {
        $param = $this->getRequest()->getParams();
        $tblurun = new Tblurun();
        $tbluser = new Tblsatici();
        $kdata = $tbluser->getir($param['satici_id']);
        unset ($kdata['son_giris'], $kdata['magaza_varmi'], $kdata['sifre'], $kdata['token_key'], $kdata['ip_adres']);
        $this->view->kullanici = $kdata;
        $data = $tblurun->liste(array('urun_sahibi=' => $param['satici_id']), array("urun_etarihi desc"))->rows;
        $this->view->urunler = $data;
    }

    public function twitterAction(){

        echo 1;exit;
    }

}



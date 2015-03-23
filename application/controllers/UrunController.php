<?php

class UrunController extends Site_Action
{

    public function init()
    {
        $this->_baseUrl = "/";
        $this->view->baseUrl = "/";
    }

    public function indexAction()
    {

    }

    public function ekleAction()
    {
        $post = $this->getRequest()->getPost();
        $session = new Zend_Session_Namespace('kullaniciSession');
        $date = Zend_Date::now();
        $data = array(
            'urun_adi' => $post['txtad'],
            'urun_fiyati' => $post['txtfiyat'],
            'urun_adeti' => 999,
            'urun_resim' => $post['txtimg'],
            'urun_uri' => Kutup_Helper::linkcevir($post['txtad']) . '-' . uniqid(),
            'urun_aciklama' => $post['txtaciklama'],
            'urun_tags' => $post['txttags'],
            'urun_etarihi' => $date->toString('yyyy-MM-dd HH:mm:ss'),
            'urun_sahibi' => $session->id,
            'resim_instaid' => $post['txtinstaid']
        );
        $dir = '../public/urunimages/' . $session->user;
        if (!file_exists($dir)):
            mkdir($dir, 0755);
        endif;
        $c = new Zend_Http_Client();
        $c->setUri($data['urun_resim']);
        $result = $c->request('GET');
        $img = imagecreatefromstring($result->getBody());
        imagejpeg($img, '../public/urunimages/' . $session->user . '/' . $data['resim_instaid'] . '.jpg');
        imagedestroy($img);
        $data['urun_resim'] = '/urunimages/' . $session->user . '/' . $data['resim_instaid'] . '.jpg';
        $tbl = new Tblurun();
        $tbl->ekle($data);
        $this->_redirect('/satici');

    }


}




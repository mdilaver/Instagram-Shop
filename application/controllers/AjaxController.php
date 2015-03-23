<?php

class AjaxController extends Site_Action
{
    protected $__response = array(
        'err' => null,
        'msg' => null,
        'html' => null
    );

    public function init()
    {
        parent::init();
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->_baseUrl = "/";
        $this->view->baseUrl = "/";
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->__response['err'] = '1';
            $this->__response['msg'] = 'Bu şekilde çalışmaz';
            return $this->_helper->json->sendJson($this->__response);
        }
        $this->_baseUrl = "/";
        $this->view->baseUrl = "/";
    }

    public function indexAction()
    {
        // action body

    }

    public function resimlerigetirAction()
    {
        $session = new Zend_Session_Namespace('kullaniciSession');
        $token = $session->token;
        $user_id = $session->id;
        $url = "https://api.instagram.com/v1/users/" . $user_id . "/media/recent/?access_token=" . $token . "&count=-1";
        $client = new Zend_Http_Client($url, array(
            'timeout' => 120
        ));
        $response = $client->request('GET');
        $result = json_decode($response->getBody());
        $tbl = new Tblurun();
        $count = 0;
        $row = $tbl->fetchAll()->toArray();
        foreach ($row as $data):
            $dburunler[] = $data['resim_instaid'];
        endforeach;
        foreach ($result->data as $data):
            if (in_array($data->id, $dburunler)):
                unset($result->data[$count]);
            endif;
            $count++;
        endforeach;
        $this->view->indis = array_keys($result->data);
        $this->view->veriler = $result;
        $this->__response['err'] = '0';
        $this->__response['html'] = $this->view->render('ajax/resimlerigetir.phtml');
        return $this->_helper->json->sendJson($this->__response);
    }

    public function urunformugetirAction()
    {
        $session = new Zend_Session_Namespace('kullaniciSession');
        $token = $session->token;
        $user_id = $session->id;
        $url = "https://api.instagram.com/v1/users/" . $user_id . "/media/recent/?access_token=" . $token . "&count=-1";
        $client = new Zend_Http_Client($url, array(
            'timeout' => 120
        ));
        $response = $client->request('GET');
        $result = json_decode($response->getBody());
        $count = $this->getRequest()->getParam('count');
        $data = array(
            'tags' => $result->data[$count]->tags,
            'images' => $result->data[$count]->images,
            'type' => $result->data[$count]->type,
            'id' => $result->data[$count]->id,
        );
        $this->view->veriler = $data;
        $this->__response['err'] = '0';
        $this->__response['html'] = $this->view->render('ajax/urunformugetir.phtml');
        return $this->_helper->json->sendJson($this->__response);
    }
    public function magazaguncelleAction()
    {
        $session = new Zend_Session_Namespace('kullaniciSession');
        $tbl = new Tblsatici();

        $veri = $tbl->getir($session->id);
        $this->view->kveri = $veri;
        $this->__response['err'] = '0';
        $this->__response['html'] = $this->view->render('ajax/magazaguncelle.phtml');
        return $this->_helper->json->sendJson($this->__response);
    }


}


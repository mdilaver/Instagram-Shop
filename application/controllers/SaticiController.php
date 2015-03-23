<?php

class SaticiController extends Site_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        //$this->_helper->layout->setLayout('saticipanel');
        $session = new Zend_Session_Namespace('kullaniciSession');
        Zend_Debug::dump($session->token);exit;
        $token = $session->token;
        $user_id = $session->id;
        $url = "https://api.instagram.com/v1/users/" . '810284113' . "/media/recent/?access_token=" . $token . "&count=-1";
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
        Zend_Debug::dump($result);exit;
        $this->__response['err'] = '0';
        $this->__response['html'] = $this->view->render('ajax/resimlerigetir.phtml');
        return $this->_helper->json->sendJson($this->__response);


    }
    public function magazaolusturAction()
    {
        $this->_helper->layout->setLayout('saticipanel');
    }
    public function magazaduzenleAction()
    {

    }
}


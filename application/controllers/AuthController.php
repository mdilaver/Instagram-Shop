<?php

class AuthController extends Zend_Controller_Action
{
    public function indexAction()
    {
    }

    public function isauthAction()
    { //instagram satıcı girişi
        if ($_GET['code']):
            $code = $_GET['code'];
            $url = "https://api.instagram.com/oauth/access_token";
            $access_token_parameters = array(
                'client_id' => Zend_Registry::get('instagram_client_id'),
                'client_secret' => Zend_Registry::get('instagram_client_secret'),
                'grant_type' => Zend_Registry::get('instagram_grant_type'),
                'redirect_uri' => Zend_Registry::get('instagram_redirect_uri'),
                'code' => $code
            );
            $client = new Zend_Http_Client($url, array(
                'timeout' => 120
            ));
            $client->setParameterPost($access_token_parameters);
            $response = $client->request('POST');

            $sonuc = json_decode($response->getBody());
            $date = Zend_Date::now();
            //Veri yoksa insert edilecek data
            $data = array(
                'satici_id' => $sonuc->user->id,
                'ad_soyad' => $sonuc->user->full_name,
                'kullanici_adi' => $sonuc->user->username,
                'sifre' => 'sifre',
                'e_mail' => 'mail',
                'kullanici_kod' => 'S',
                'token_key' => Kutup_Sifreleme::encode($sonuc->access_token),
                'resim_link' => $sonuc->user->profile_picture,
                'web_site' => $sonuc->user->website,
                'aciklama' => $sonuc->user->bio,
                'kayit_tarih' => $date->toString('yyyy-MM-dd HH:mm:ss')
            );
            $authAdapter = $this->getsaticiAuthAdapter();
            $authAdapter->setIdentity($data['kullanici_adi'])
                ->setCredential($data['token_key']);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);
            $veri = $authAdapter->getResultRowObject();
            if ($result->isValid()):
                $session = new Zend_Session_Namespace('kullaniciSession');
                $session->unlock();
                $session->user = $veri->kullanici_adi; //session a databaseden alınan kullanıcı tanıtıldı.
                $session->id = $veri->satici_id;
                $session->full_name = $veri->magaza_adi;
                $session->picture = $veri->resim_link;
                $session->kullanici_kodu = $veri->kullanici_kod;
                $session->token = Kutup_Sifreleme::decode($veri->token_key);
                //ACL tanımlaması
                $kullanici_kodu = $session->kullanici_kodu ? $session->kullanici_kodu : 'X';
                $acl = new Zend_Acl();
                $role = new Zend_Acl_Role($kullanici_kodu);
                $acl->addRole($role);
                $acl->add(new Zend_Acl_Resource('index'));
                $acl->allow($kullanici_kodu, 'index', 'index');
                $tblacl = new Tblyetki();
                $yetkiler = $tblacl->fetchAll("kullanici_kodu='" . $kullanici_kodu . "'");
                foreach ($yetkiler as $yetki):
                    if (!$acl->has(new Zend_Acl_Resource($yetki->controller))):
                        $acl->add(new Zend_Acl_Resource($yetki->controller));
                    endif;
                    $acl->allow($yetki->kullanici_kodu, $yetki->controller, $yetki->action);
                endforeach;
                $session->acl = $acl;
                $session->lock(); //session kapatıldı.
                $nowdate = array(
                    'son_giris' => $date->toString('yyyy-MM-dd HH:mm:ss'),
                    'ip_adres' => getenv("REMOTE_ADDR")
                );
                $tbl = new Tblsatici();
                $tbl->update($nowdate, 'satici_id=' . $veri->satici_id);
                Kutup_Helper::bilgiMesaji('Başarıyla Giriş Yapıldı.');
                if ($veri->magaza_varmi == 0):
                    $this->_redirect('/satici/magazaolustur');
                endif;
                $this->_redirect('/satici');
            else:
                $tbl = new Tblsatici();
                $tbl->insert($data);
                $this->_redirect('/auth/kayitsonuc');
            endif;
        endif;
    }

    public function fcallbackAction()
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        if (isset($params['code'])) {
            $code = $params['code'];
            $url = 'https://graph.facebook.com/oauth/access_token';
            $arpost = array(
                'client_id' => Zend_Registry::get('facebook_client_id'),
                'redirect_uri' => Zend_Registry::get('facebook_redirect_uri'),
                'client_secret' => Zend_Registry::get('facebook_client_secret'),
                'code' => $code);
            $client = new Zend_Http_Client($url, array(
                'timeout' => 120
            ));
            $client->setParameterPost($arpost);
            $response = $client->request('POST');
            $result = $response->getBody();
            parse_str($result, $atoken);
            $url = 'https://graph.facebook.com/me/friends?access_token=' . $atoken['access_token'];
            $client2 = new Zend_Http_Client($url, array(
                'timeout' => 120
            ));
            unset ($atoken['expires']);
            $response = $client2->request('GET');
            Zend_Debug::dump($response);
            $sonuc = json_decode($response->getBody());
            $date = Zend_Date::now();
            $data = array(
                'alici_id' => $sonuc->id,
                'ad_soyad' => $sonuc->name,
                'kullanici_adi' => 'username',
                'sifre' => 'sifre',
                'e_mail' => $sonuc->email,
                'kullanici_kod' => 'A',
                'web_site' => '',
                'token_key' => $atoken['access_token'],
                'kayit_tarih' => $date->toString('yyyy-MM-dd HH:mm:ss')
            );

            $aliciauthAdapter = $this->getaliciAuthAdapter();
            $aliciauthAdapter->setIdentity($data['e_mail'])
                             ->setCredential($data['alici_id']);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($aliciauthAdapter);
            $veri = $aliciauthAdapter->getResultRowObject();

            if ($result->isValid()):
                $session = new Zend_Session_Namespace('kullaniciSession');
                $session->unlock();
                $session->kullanici = $veri->kullanici_adi; //session a databaseden alınan kullanıcı tanıtıldı.
                $session->id = $veri->alici_id;
                $session->ad_soyad = $veri->ad_soyad;
                $session->kullanici_kodu = $veri->kullanici_kod;
                $session->token = $atoken['access_token'];
                //ACL tanımlaması
                $kullanici_kodu = $session->kullanici_kodu ? $session->kullanici_kodu : 'X';
                $acl = new Zend_Acl();
                $role = new Zend_Acl_Role($kullanici_kodu);
                $acl->addRole($role);
                $acl->add(new Zend_Acl_Resource('index'));
                $acl->allow($kullanici_kodu, 'index', 'index');
                $tblacl = new Tblyetki();
                $yetkiler = $tblacl->fetchAll("kullanici_kodu='" . $kullanici_kodu . "'");
                foreach ($yetkiler as $yetki):
                    if (!$acl->has(new Zend_Acl_Resource($yetki->controller))):
                        $acl->add(new Zend_Acl_Resource($yetki->controller));
                    endif;
                    $acl->allow($yetki->kullanici_kodu, $yetki->controller, $yetki->action);
                endforeach;
                $session->acl = $acl;
                $session->lock(); //session kapatıldı.
                $nowdate = array(
                    'son_giris' => $date->toString('yyyy-MM-dd HH:mm:ss'),
                    'ip_adres' => getenv("REMOTE_ADDR")
                );
                $tbl = new Tblalici();
                $tbl->update($nowdate, 'alici_id=' . $veri->alici_id);
                Kutup_Helper::bilgiMesaji('Başarıyla Giriş Yapıldı.');
                $this->_redirect('/panel');
            else:
                $tbl = new Tblalici();
                $tbl->insert($data);
                $this->_redirect('/auth/kayitsonuc');
            endif;

        }
    }
    public function logoutAction()
    {
        $session = new Zend_Session_Namespace('kullanicisession');
        if ($session) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::destroy();
        } else {
            Zend_Session::destroy();
        }
        $this->_redirect('/index');
    }

    public function facebooklinkAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->__response['err'] = '1';
            $this->__response['msg'] = 'Bu şekilde çalışmaz';
            return $this->_helper->json->sendJson($this->__response);
        }
        $this->getHelper('Layout')->disableLayout();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->_helper->viewRenderer->setNoRender(true);
        $url = 'https://graph.facebook.com/oauth/authorize?client_id=' .
            Zend_Registry::get('facebook_client_id') .
            '&redirect_uri=' .
            Zend_Registry::get('facebook_redirect_uri') .
            '&scope='.
            Zend_Registry::get('facebook_scope');
        $sonuc['link'] = $url;
        return $this->_helper->json->sendJson($sonuc);
    }

    public function instagramlinkAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->__response['err'] = '1';
            $this->__response['msg'] = 'Bu şekilde çalışmaz';
            return $this->_helper->json->sendJson($this->__response);
        }
        $this->getHelper('Layout')->disableLayout();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->_helper->viewRenderer->setNoRender(true);
        $url = 'https://api.instagram.com/oauth/authorize/?client_id=' .
            Zend_Registry::get('instagram_client_id') .
            '&scope=' .
            Zend_Registry::get('instagram_scope') .
            '&redirect_uri=' .
            Zend_Registry::get('instagram_redirect_uri') .
            '&response_type=code';
        $sonuc['link'] = $url;
        return $this->_helper->json->sendJson($sonuc);
    }

    private function getsaticiAuthAdapter()
    { //satici auth adapteri
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_table::getDefaultAdapter());
        $authAdapter->setTableName('tbl_satici')
            ->setIdentityColumn('kullanici_adi')
            ->setCredentialColumn('token_key');
        return $authAdapter;
    }
    private function getaliciAuthAdapter()
    { //alici auth adapteri
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_table::getDefaultAdapter());
        $authAdapter->setTableName('tbl_alici')
            ->setIdentityColumn('e_mail')
            ->setCredentialColumn('alici_id');
        return $authAdapter;

   }

}
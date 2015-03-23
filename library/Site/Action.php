<?php 
//kendi projemizdeki tüm ilgili action'lari budan türetebiliriz..

class Site_Action extends Zend_Controller_Action
{
public function init()
    {
        parent::init();
        $this->_baseUrl="/";
        $this->view->baseUrl="/";
        $this->session= new Zend_Session_Namespace('kullaniciSession');
        $this->view->bilgi_mesaji = $this->session->bilgi_mesaji;
        $this->session->bilgi_mesaji = null;
        $this->view->hata_mesaji = $this->session->hata_mesaji;
        $this->session->hata_mesaji = null;
        if($this->session->kullanici_kodu=='A'):
            $menuyolu='\configs\menu\alicinav.xml';
        elseif($this->session->kullanici_kodu=='S'):
            $menuyolu='\configs\menu\saticinav.xml';
        else:
            $menuyolu='\configs\menu\nav.xml';
        endif;
        $config = new Zend_Config_Xml(APPLICATION_PATH.$menuyolu, 'nav');
        $container = new Zend_Navigation($config);
        $this->view->navigation($container);
    }
    public function preDispatch() {
        $this->session= new Zend_Session_Namespace('kullaniciSession');
        $this->view->kullanici = $this->session;
        if(!$this->session->acl){
            $this->_redirect('index');
        }
        if($this->session->kullanici_kodu!=ADMIN_YETKI_KODU):
            $acl= $this->session->acl;
                if($acl->has($this->getRequest()->getControllerName())):
                    try{
                        if(!$acl->isAllowed($this->session->kullanici_kodu ,$this->_request->getControllerName(),$this->_request->getActionName())):
                            Kutup_Helper::hataMesaji('Bu sayfaya erişim izniniz yoktur.');
                            if($this->session->kullanici_kodu=='S'):
                                $this->redirect('/satici');
                                elseif($this->session->kullanici_kodu=='A'):
                                $this->redirect('/panel');
                            else:
                                $this->redirect('/index');
                            endif;
                            $this->redirect('/error');
                        endif;
                    }
                    catch(Zend_Exception $e){
                        echo $e->getMessage();exit;
                    }
                else:
                    Kutup_Helper::hataMesaji('Bu sayfaya erişme yetkiniz yoktur!');
                    if($this->session->kullanici_kodu=='S'):
                        $this->redirect('/satici');
                    elseif($this->session->kullanici_kodu=='A'):
                        $this->redirect('/panel');
                    else:
                        $this->redirect('/index');
                    endif;
                endif;

        endif;
    }
}
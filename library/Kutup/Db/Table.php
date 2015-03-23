<?php

class Kutup_Db_Table extends Zend_Db_Table_Abstract {

    protected $_select;
    protected $_formId;
    protected $r;
    public $_error;

    public function getPrimary() {
        if (isset($this->_formId))
            return $this->_formId;
        else {
            if (is_array($this->_primary)) {
                $arr = array();
                foreach ($this->_primary as $key) {
                    $arr[] = $key;
                }
                return $arr;
            }
            else
                return array($this->_primary);
        }
    }

    /**
     * tablodan primary key e göre satır getirir
     *
     * @param mixed $id primary değeri
     * @return array
     * @author Sinan Kambur
     */
    public function getir($id = null) {

        $primary = $this->getPrimary();

        if (sizeof($primary) == 1) {
            if ($id != null) {
                $select = $this->select()->where($primary[0] . " = '" . $id . "'");
            }
            else {
                if ($id != null)
                    $select = $this->select();
            }
        }
        else {
            $select = $this->select();
            foreach ($primary as $key) {
                $select = $select->where($key . " = '" . $id[$key] . "'");
            }
        }

        //echo $select->__toString();exit;
        $row = $this->fetchRow($select);
        if ($row)
            return $row->toArray();
        return null;
    }

    /**
     * tabloda geçilen parametreye göre kayıt var mı
     *
     * @param array $where
     * @return bool
     * @author Sinan Kambur
     */
    public function kontrol($where) {

        try {
            $sql = $this->select();

            if (is_array($where)) {
                /* foreach ($where as $key=>$value)
                  {
                  //array_push($where, $this->getAdapter()->quoteInto($key .' = ?', $value));
                  $sql = $sql->where($this->getAdapter()->quoteInto($key .' = ?', $value));


                  } */
                $this->getWhere($where, $sql);
            }
            else
                $sql = $this->select()->where($where);

            $row = $this->fetchRow($sql);

            if ($row) {
                return true;
            }
            else
                return false;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Tabloya yeni kayıt ekler
     *
     * @param array $post tabloya eklenecek değerler
     * @return mixed primary key değeri
     */
    public function ekle($post, $echo = 1) {
        try {
            if ($post) {
               
                $id = $this->insert($post);

                if ($id) {
                    if ($echo)
                        Kutup_Helper::bilgiMesaji("Ürün Eklendi!");
                    return $id;
                }
                else {
                    if ($echo)
                        Kutup_Helper::hataMesaji("Ürün Eklenemedi!");
                    return null;
                }
            }

            if ($echo)
                Kutup_Helper::hataMesaji("Kayıt Eklenemedi!");
            return null;
        } catch (Zend_Exception $e) {

            $frontController = Zend_Controller_Front::getInstance();
            $request = $frontController->getRequest();
            $this->_error = $e;
            return false;
        }
    }

    /**
     * satır güncelleme
     *
     * @param mixed $id primary key değeri veya where kriterleri
     * @param array $post değerler
     * @param bool $noId primary key yerine
     * @return bool
     * @author Sinan Kambur
     */
    public function guncelle($id, $post, $noId=false) {

        try {

            $primary = $this->getPrimary();

            if (sizeof($primary) == 1) {
                $where = array();
                if (!$noId) {
                    $where = array($this->getAdapter()->quoteInto($primary[0] . ' = ?', $id));
                }
                else {
                    foreach ($id as $key => $value) {
                        if (is_array($value))
                            array_push($where, $key . " in ('" . implode("','", $value) . "')");
                        else
                            array_push($where, $this->getAdapter()->quoteInto($key . ' = ?', $value));
                    }
                }


                try {
                    $r = $this->update($post, $where);
                } catch (Zend_Exception $e) {
                    $this->_error = $e;
                    $frontController = Zend_Controller_Front::getInstance();
                    $request = $frontController->getRequest();
                    return $e->getMessage();
                }

                return $r;
            } else {
                $where = array();

                if ($filtre) {
                    foreach ($filtre as $key => $value) {
                        array_push($where, $this->getAdapter()->quoteInto($key . ' = ?', $value));
                    }
                }

                if (!$noId) {
                    foreach ($primary as $key) {
                        array_push($where, $key . " = '" . $id[$key] . "'");
                    }
                } else {
                    foreach ($id as $key => $value) {
                        if (is_array($value))
                            array_push($where, $key . " in ('" . implode("','", $value) . "')");
                        else
                            array_push($where, $this->getAdapter()->quoteInto($key . ' = ?', $value));
                    }
                }

                $where = implode(" and ", $where);

                try {
                    $r = $this->update($post, $where);
                } catch (Zend_Exception $e) {
                    $this->_error = $e;
                    $frontController = Zend_Controller_Front::getInstance();
                    $request = $frontController->getRequest();
                    return $e->getMessage();
                }
                
                return $r;
            }
        } catch (Zend_Exception $e) {
            $this->_error = $e;
            return $e->getMessage();
            exit;
        }
    }

    public function sil($where, $all=0) {

        if (!$where and $all == 0)
            return 0;

        if (is_array($where)) {
            $arr = array();
            foreach ($where as $k => $v) {
                if (stripos($k, '>') > 0 or stripos($k, '<') or stripos($k, '=') > 0) {
                    $arr[] = "$k'$v'";
                } else if (substr($k, strlen($k) - 2, 2) == 'in') {
                    if (is_array($v)) {
                        $arr[] = "$k(" . implode(',', $v) . ")";
                    } else {
                        $arr[] = "$k ($v)";
                    }
                } else {
                    $arr[] = "$k='$v'";
                }
            }
            $where = $arr;
        }
        try {
            return $this->delete($where);
        } catch (Zend_Exception $e) {
            $this->_error = $e;
            $frontController = Zend_Controller_Front::getInstance();
            $request = $frontController->getRequest();

            //echo $e->getMessage();
            //exit;
        }
        return 0;
    }

    /**
     * array den Where kriteri oluşturur
     *
     * @param array $where
     * @param Zend_Db_Select $sql
     * @return Zend_Db_Select
     * @author Sinan Kambur
     */
    function getWhere($where, $sql, $between=null) {
        foreach ($where as $key => $value) {
            if (stripos($key, ' ') > -1) {
                $tmp = explode(' ', $key);
                $key = $tmp[0] . " " . $tmp[1];
            }
            if (is_array($value)) {
                for ($i = 0; $i < sizeof($value); $i++) {
                    if ($value == '') {
                        unset($value[$i]);
                        continue;
                    }
                    $value[$i] = "'" . $value[$i] . "'";
                }

                if (sizeof($value) > 0)
                    $sql = $sql->where($key . " in (" . implode(',', $value) . ")");
            }
            else {
                if (stripos($key, '>') > -1 or stripos($key, '<') > -1 or stripos($key, '=') > -1 or stripos($key, '<>') > -1) {
                    $sql = $sql->where($key . " ?", $value);
                } else if (stripos(strtolower($key), " in") > -1) {
                    $sql = $sql->where($key . $value);
                }
                else if($value instanceof Zend_Db_Expr)
                {
                	$sql=$sql->where($key." ".$value->__toString());
                }
                else
                    $sql = $sql->where($key . " like '$value%'");
            }
        }
		if($between != null)
		{
        	foreach ($between as $key => $value) {
           	 $sql = $sql->where($key . " between ? and ?", $value[0], $value[1]);
        	}
		}

        return $sql;
    }

    /**
     * Tablodan tek bir satırdaki alan veya alanları getirir
     *
     * @param mixed $alan getirilecek alanlar
     * @param array $where
     * @param string $order sıralama
     * @return string
     * @author Sinan Kambur
     */
    public function getirAlan($alan, $where=null, $order=null) {
        $db = $this->getAdapter();
        if (is_array($alan)) {
            $alan = "concat(".implode(",' ',", $alan).")";
        }
        $sql = $db->select()->from($this->_name, array('text' => new Zend_Db_Expr($alan)))->order($order);

        if ($where && is_array($where)) {
            $sql = $this->getWhere($where, $sql);
        }
        //Zend_Debug::dump($sql->__toString());exit;
        try {
            $row = $db->fetchOne($sql);
        } catch (Zend_Exception $e) {
            //echo $e->getMessage();
        }

        if ($row)
            return $row;
        return null;
    }

    /**
     * combo için array oluşturur
     *
     * @param mixed $val value field
     * @param mixed $text display field
     * @param array $where
     * @param string $order
     * @param bool $bos
     * @param string $seciniz
     * @return array
     * @author Sinan Kambur
     */
    public function getirPairs($val, $text, $where=null, $order='text', $bos=true, $seciniz='Seçiniz') {
        try {


            if (!$val || !$text)
                return;

            $db = $this->getAdapter();
            if (is_array($text)) {
                $text = "concat(".implode(",' / ',", $text).")";
            }

            if (is_array($val)) {
                $keys = array_keys($val);

                $sql = $db->select()->from($this->_name, array('text' => new Zend_Db_Expr($text)));
                $sql = $this->getWhere($where, $sql);

                if ($keys)
                    $sql = $sql->where($keys[0] . '=?', $val[$keys[0]]);

                $row = $db->fetchOne($sql);
                if ($row)
                    return $row;
                return null;
            }
            else {
                if (!$order)
                    $order = 'text';

                $orderCol = null;
                $cols = array('val' => new Zend_Db_Expr($val), 'text' => new Zend_Db_Expr($text));
                if ($order != 'text') {
                    $cols = array('val' => $val, 'text' => new Zend_Db_Expr($text), $order);
                }
                $sql = $db->select()->from($this->_name, $cols)
                                ->order($order)->distinct();

                if ($where && is_array($where)) {
                    $sql = $this->getWhere($where, $sql);
                }
                //echo $sql;exit;
                $rows = $db->fetchAll($sql);

                $arr = array();

                if ($bos) {
                    $arr[''] = $seciniz;
                }

                foreach ($rows as $row) {

                    $arr[$row['val']] = $row['text'];
                }

                return $arr;
            }
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }

    /**
     * listeleme fonksiyonu - rows ve rowcount içerikli bir obje döndürür
     *
     * @param array $where
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return object
     */
    public function liste($where=null, $sort=null, $limit=null, $offset=null, $cols=null, $group=null, $count=null, $returnSql=0, $having=null) {
        try {

            if ($cols) {
                $keys = array_keys($cols);
                if (!$cols[0] and !eregi("\(", $cols[$keys[0]])) {
                    $cols = $keys;
                }
            }
            $db = $this->getAdapter();
            if (!$this->_select) {
                if ($cols) {
                    $this->_select = $this->select()->from($this->_name, $cols);
                } else {
                    $this->_select = $this->select()->from($this->_name);
                }
            }

            if ($count and ($group or $cols)) {
                $columns = $this->_select->getPart('columns');
                array_push($columns, array(null, new Zend_Db_Expr('count(*)'), 'adet'));
                $this->_select->setPart('columns', $columns);

                if (!$group) {
                    $this->_select = $this->_select->group($cols);
                }
            } else {
                if ($count != null && $count == true) {
                    $this->_select->setPart('columns', array(array($this->_name, new Zend_Db_Expr('count(*)'), "adet")));
                }
            }

            $this->_select = $this->_select->limit($limit, $offset)
                    ->order($sort);
            if ($group) {
                $this->_select = $this->_select->group($group);
            }

            if ($having) {
                $this->_select = $this->_select->having($having);
            }

            if ($where) {
                $between = array();
                $_where = $where;
                foreach ($where as $k => $v) {

                    if (is_array($v)) {

                        for ($i = 0; $i < sizeof($v); $i++) {
                            if (trim($v[$i]) == '') {
                                unset($v[$i]);
                            }
                        }
                        $where[$k] = $v;
                    }
                    else if(trim($v) == '') {
                        unset($where[$k]);
                    }

                    if ($k{0} == '_' and $v) {
                        $between[substr($k, 3)][] = $v;
                        unset($where[$k]);
                    }
                }

                foreach ($between as $k => $v) {

                    if (sizeof($v) == 1 and $v[0] != '') {
                        $where[$k] = $v[0];
                        unset($between[$k]);
                    }
                }
                $this->_select = $this->getWhere($where, $this->_select, $between);
            }


            if($fc = Zend_Controller_Front::getInstance())
            {
            	if($fc->getRequest() != null){

	            	if (stripos(@$fc->getRequest()->getActionName(), 'ajax') > -1) {
		                $userSession = new Zend_Session_Namespace('userSession');
		                $userSession->unlock();
		                $ind = $fc->getRequest()->getControllerName();
		                $sql = $this->_select;
		                $sql->reset('limitcount');
		                $userSession->yazdir[$ind]['model'] = get_class($this);
		                $where = array_merge($where, $between);
		                $userSession->yazdir[$ind]['where'] = $_where;
		                $userSession->yazdir[$ind]['sort'] = $sort;
		                $userSession->yazdir[$ind]['cols'] = $cols;
		                $userSession->yazdir[$ind]['count'] = $count;
		                $userSession->yazdir[$ind]['group'] = $group;
		                $userSession->lock();
		                $this->_select->limit($limit, $offset);
	            	}
            	}
            }

          /*  if ($returnSql) {
            	            echo "sql: ".$this->_select ."\n\n";
            	exit;
            }*/

            //echo "sql: ".$this->_select ."\n\n<br><br>"; 
            if ($returnSql) {
                $sql = $this->_select;
                $this->_select = null;
                return $sql;
            }
            $r = new stdClass();
            $r->rows = $db->fetchAll($this->_select);
            if ($limit) {
                $this->_select->reset('order');
                $this->_select->reset('limitcount');
                $this->_select->reset('limitoffset');
                //$this->_select->reset('group');
                $this->_select->setPart('columns', array(array($this->_name, new Zend_Db_Expr('count(*) as adet'))));
                $rc = $db->fetchAll($this->_select);
                if (sizeof($rc) > 1) {
                    $r->rowcount = sizeof($rc);
                } else {
                    $r->rowcount = $rc[0]['ADET'];
                }

                if (!$r->rowcount) {
                    $r->rowcount = 0;
                }
            }

            $this->_select = null;
            return $r;
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
            exit;
        }
        return null;
    }

    public function getSelect() {
        return $this->_select;
    }

    public function getForm() {
        $class = get_class($this);
        $cId="form_".$class."_".Ubit_HelperSession::oku()->userData->grup_kodu;
        $cache = Ubit_Helper::getCache();
        $data=$cache->load($cId);

        $data = false;

        if($data===false)
        {

	        $tbl = new TblForm();
	        $where = array('model=' => $class);
	        $yasak = TblSistemRolYasakAlan::getirYasakAlan($class);

	        if (sizeof($yasak) > 0) {
	            $where['alan not'] = $yasak;
	        }

             $data = $tbl->liste($where, 'sira')->rows;

             $cache->save($data, $cId);
        }

        return $data;
    }

    public function getHeader() {
        $class = get_class($this);

        $cId = "header_" . $class . "_" . Ubit_HelperSession::oku()->userData->grup_kodu;
        $cache = Ubit_Helper::getCache();
        $data = $cache->load($cId);
      //  $data = false; // cache kapatmak için
        if ($data === false) {
            $tbl = new TblForm();
            $where = array('model=' => $class, 'liste=' => 1);
            //$yasak = TblGrupYasakAlan::getirYasakAlan($class);
            $yasak = TblSistemRolYasakAlan::getirYasakAlan($class);
            if (sizeof($yasak) > 0) {
                $where['alan not'] = $yasak;
            }
            $data = $tbl->getirPairs('alan', 'etiket', $where, 'liste_sira');
            foreach ($data as $key => $item) {
                $newKey = ereg_replace("[^A-Za-z0-9_]", "", $key);
                $data[$newKey] = $item;
            }
            $cache->save($data, $cId);
        }
        return $data;
    }

    public function getPost($post) {
        $class = get_class($this);

        $cId = "post_" . $class . "_" . Ubit_HelperSession::oku()->userData->grup_kodu;
        $cache = Ubit_Helper::getCache();
        $data = $cache->load($cId);

       // $data = false; //cache kapatmak için

        if ($data === false) {
            $tbl = new TblForm();
            $where = array('model=' => $class, 'arama=' => 1);
            $yasak = TblSistemRolYasakAlan::getirYasakAlan($class);

            if (sizeof($yasak) > 0) {
                $where['alan not'] = $yasak;
            }

            $data = $tbl->liste($where)->rows;
               $cache->save($data, $cId);
        }

        $arr = array();
        $newPost = array();

        foreach ($data as $r) {
            $deger = $post[$r['alan']];

            switch ($r['tip']) {
                case 'hidden':
            	case 'textbox':
                    if ($deger)
                        $newPost[$r['alan']] = $deger;
                    break;

                case 'selectbox':
                case 'date':
                    if ($deger && !is_array($deger))
                        $newPost[$r['alan'] . '='] = $deger;
                    elseif (is_array($deger))
                        $newPost[$r['alan']] = $deger;
                    break;

                case 'multiselect':
                    if ($deger and is_array($deger)) {
                        if (count($deger) > 0)
                            $newPost[$r['alan']] = $deger;
                        else if (count($deger) == 1 and $deger[0])
                            $newPost[$r['alan'] . '='] = $deger[0];
                    }
                    break;

                case 'textbetween':
                case 'datebetween':
                    $deger1 = $post['_1_' . $r['alan']];
                    $deger2 = $post['_2_' . $r['alan']];
                    if ($deger1) {
                        $newPost['_1_' . $r['alan']] = $deger1;
                    }
                    if ($deger2) {
                        $newPost['_2_' . $r['alan']] = $deger2;
                    }
                    break;
            }
        }

        return $newPost;
    }

}

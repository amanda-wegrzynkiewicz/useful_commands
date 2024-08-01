<?php

/**
 * CSV
 **/
class ImportBasicController extends BasicController
{
    //objItems
    //objProducers
    //cat_items
    //objAmount
    //objUsers
    //objOrders
    //objOrdersItems
    public function init()
    {
        parent::init();
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(0);
        ini_set('memory_limit', '2024M');
        $this->db = Zend_Registry::get('dbAdapter');
        $this->lang = Zend_Registry::get('lang');
    }


    public function connect()
    {
        $link = mysqli_connect("localhost", "centr11_test", "3t148FsNo0", "centr11_test") or die("Error " . mysqli_error($link));
        $link->query("SET NAMES 'utf8'");
        return $link;
    }

    public function indexAction()
    {
        echo getcwd();
    }

    public function prodAction()
    {
        ini_set('display_errors', 1);

        $link = $this->connect();

        $sql = "SELECT id_manufacturer,name FROM `ps_manufacturer` WHERE active=1 ORDER BY id_manufacturer ASC";
        $result1 = $link->query($sql);
        $i = 0;
        while ($row1 = mysqli_fetch_array($result1)) {


            if (strlen($row1['name']) > 1) {
                $prod = trim($row1['name']);


                if ($this->objProducers->isExist($prod)) {
                    // istnieje
                } else {
                    // dodaj
                    $data = array();
                    $data['producer_name'] = $prod;
                    $data['producer_desc'] = "";
                    //print_r($data);

                    $this->objProducers->add($data);
                }
            }
        }

        die('import complete without errors');
    }

    public function catAction()
    {

        ini_set('display_errors', 1);
        ini_set('memory_limit', '1024M');
        $lang = 1;
        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();


        $sql = "SELECT c.*, l.name, l.description as description,l.meta_description,l.meta_keywords,l.meta_title
 FROM `ps_category` c INNER JOIN ps_category_lang l ON (l.id_category=c.id_category AND l.id_lang=6) 
            WHERE c.id_parent='1'ORDER BY c.id_category DESC";
        $result1 = $link->query($sql);
        while ($row1 = mysqli_fetch_array($result1)) {

            // level 1
            $arr = array();
            // $arr['id'] = $row1['category_id'];
            $arr['name' . $lang] = $row1['name'];
            $arr['google_title' . $lang] = $row1['meta_title'];
            $arr['google_desc' . $lang] = $row1['meta_description'];
            $arr['google_key_words' . $lang] = $row1['meta_keywords'];
            $arr['campaign_active'] = 0;
            $lastID1 = $this->treeCat->add($arr, 1);
            $this->treeCat->setup();

            try {
                $db->query("INSERT INTO kategorie (stara,nowa) VALUES('" . $row1['id_category'] . "','$lastID1')");
            } catch (Exception $e) {
                print_r($e);
                die();
            }

            if ($row1['description'] != '') {
                $desc = array();
                $desc['cat_id'] = $lastID1;
                $desc['cat_desc'] = $row1['description'];  // print_r($lastID1    );  die('...');
                $this->objCategoriesDesc->add($desc);
            }


            $sql = "SELECT c.*, l.name, l.description as description,l.meta_description,l.meta_keywords,l.meta_title
 FROM `ps_category` c INNER JOIN ps_category_lang l ON (l.id_category=c.id_category AND l.id_lang=6) 
                     WHERE c.id_parent='" . $row1['id_category'] . "' ORDER BY c.id_category DESC";
            $result2 = $link->query($sql);

            if (count($result2) > 0) {
                while ($row2 = mysqli_fetch_array($result2)) {
                    // level 2
                    $arr = array();

                    $arr['name' . $lang] = $row2['name'];
                    $arr['google_title' . $lang] = $row2['meta_title'];
                    $arr['google_desc' . $lang] = $row2['meta_description'];
                    $arr['google_key_words' . $lang] = $row2['meta_keywords'];
                    $arr['campaign_active'] = 0;


                    $lastID2 = $this->treeCat->add($arr, $lastID1);
                    $this->treeCat->setup();
                    try {
                        $db->query("INSERT INTO kategorie (stara,nowa) VALUES('" . $row2['id_category'] . "','$lastID2')");
                    } catch (Exception $e) {
                        print_r($e);
                        die();
                    }

                    //$db->query("INSERT INTO kategorie (stara,nowa) VALUES('".$row2['id_category']."','$lastID2')");


                    if ($row2['description'] != '') {
                        $desc = array();
                        $desc['cat_id'] = $lastID2;
                        $desc['cat_desc'] = $row2['description'];
                        $this->objCategoriesDesc->add($desc);
                    }

                    $sql = "SELECT c.*, l.name, l.description as description,l.meta_description,l.meta_keywords,l.meta_title
 FROM `ps_category` c INNER JOIN ps_category_lang l ON (l.id_category=c.id_category AND l.id_lang=6) 
                     WHERE c.id_parent='" . $row2['id_category'] . "' ORDER BY c.id_category DESC";
                    $result3 = $link->query($sql);
                    while ($row3 = mysqli_fetch_array($result3)) {
                        // level 3
                        $arr = array();
                        // $arr['id'] = $row3['category_id'];
                        $arr['name' . $lang] = $row3['name'];
                        $arr['google_title' . $lang] = $row3['meta_title'];
                        $arr['google_desc' . $lang] = $row3['meta_description'];
                        $arr['google_key_words' . $lang] = $row3['meta_keywords'];
                        $arr['campaign_active'] = 0;

                        $lastID3 = $this->treeCat->add($arr, $lastID2);
                        $this->treeCat->setup();

                        $db->query("INSERT INTO kategorie (stara,nowa) VALUES('" . $row3['id_category'] . "','$lastID3')");
                        if ($row3['description'] != '') {
                            $desc = array();
                            $desc['cat_id'] = $lastID3;
                            $desc['cat_desc'] = $row3['description'];
                            $this->objCategoriesDesc->add($desc);
                        }

                        $sql = "SELECT c.*, l.name, l.description as description,l.meta_description,l.meta_keywords,l.meta_title
 FROM `ps_category` c INNER JOIN ps_category_lang l ON (l.id_category=c.id_category AND l.id_lang=6) 
                     WHERE c.id_parent='" . $row3['id_category'] . "' ORDER BY c.id_category DESC";
                        $result4 = $link->query($sql);
                        while ($row4 = mysqli_fetch_array($result4)) {
                            // level 3
                            $arr = array();

                            $arr['name' . $lang] = $row4['name'];
                            $arr['google_title' . $lang] = $row4['meta_title'];
                            $arr['google_desc' . $lang] = $row4['meta_description'];
                            $arr['google_key_words' . $lang] = $row4['meta_keywords'];
                            $arr['campaign_active'] = 0;

                            $lastID4 = $this->treeCat->add($arr, $lastID3);
                            $this->treeCat->setup();

                            $link->query("INSERT INTO kategorie (stara,nowa) VALUES('" . $row4['id_category'] . "','$lastID4')");

                            if ($row4['description'] != '') {
                                $desc = array();
                                $desc['cat_id'] = $lastID4;
                                $desc['cat_desc'] = $row4['description'];
                                $this->objCategoriesDesc->add($desc);
                            }


                            $sql = "SELECT c.*, l.name, l.description as description,l.meta_description,l.meta_keywords,l.meta_title
 FROM `ps_category` c INNER JOIN ps_category_lang l ON (l.id_category=c.id_category AND l.id_lang=6) 
                     WHERE c.id_parent='" . $row4['id_category'] . "' ORDER BY c.id_category DESC";
                            $result5 = $link->query($sql);
                            while ($row5 = mysqli_fetch_array($result5)) {
                                // level 3
                                $arr = array();

                                $arr['name' . $lang] = $row5['name'];
                                $arr['google_title' . $lang] = $row5['meta_title'];
                                $arr['google_desc' . $lang] = $row5['meta_description'];
                                $arr['google_key_words' . $lang] = $row5['meta_keywords'];
                                $arr['campaign_active'] = 0;

                                $lastID5 = $this->treeCat->add($arr, $lastID4);
                                $this->treeCat->setup();

                                $link->query("INSERT INTO kategorie (stara,nowa) VALUES('" . $row5['id_category'] . "','$lastID5')");

                                if ($row5['description'] != '') {
                                    $desc = array();
                                    $desc['cat_id'] = $lastID5;
                                    $desc['cat_desc'] = $row5['description'];
                                    $this->objCategoriesDesc->add($desc);
                                }
                            }
                        }
                    }
                }
            }
        }


        //pre($cat);

        die('import complete without errors');
    }

    public function propertiesShowAction()
    {
        ini_set('display_errors', 1);
        ini_set('memory_limit', '1024M');
        $lang = 1;

        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();

        $res = array();
        $sql = "SELECT GROUP_CONCAT( CAST( id_attribute AS CHAR ) ) as dupa
FROM ps_product_attribute_combination
GROUP BY id_product_attribute
HAVING count( * ) >1";
        $result = $link->query($sql);
        while ($row = mysqli_fetch_array($result)) {
            $sql = "SELECT id_attribute_group FROM `ps_attribute` WHERE id_attribute IN (" . $row['dupa'] . ")";
            $result2 = $link->query($sql);
            $a = array();
            while ($row2 = mysqli_fetch_array($result2)) {
                $a[] = $row2['id_attribute_group'];
            }
            $res[implode(",", array_unique($a))] += 1;
        }

        pre($res);


        die("OK");
    }

    public function importGroups2Action()
    {

        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();

        $res = array();
        $sql = "SELECT GROUP_CONCAT( CAST( id_attribute AS CHAR ) ) as dupa
FROM ps_product_attribute_combination
GROUP BY id_product_attribute
HAVING count( * ) >1";
        $result = $link->query($sql);
        while ($row = mysqli_fetch_array($result)) {
            $sql = "SELECT id_attribute_group FROM `ps_attribute` WHERE id_attribute IN (" . $row['dupa'] . ")";
            $result2 = $link->query($sql);
            $a = array();
            while ($row2 = mysqli_fetch_array($result2)) {
                $a[] = $row2['id_attribute_group'];
            }
            sort($a);
            $res[implode(",", array_unique($a))] += 1;
        }


        foreach ($res as $r => $r2) {
            $a = explode(",", $r);
            if (count($a) == 1) continue;

            $name1 = $name2 = $name3 = '';

            $sql = "SELECT name FROM `ps_attribute_group_lang` WHERE id_lang=6 AND id_attribute_group=$a[0]";
            $result = $link->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $name1 = $row['name'];
            }
            $sql = "SELECT name FROM `ps_attribute_group_lang` WHERE id_lang=6 AND id_attribute_group=$a[1]";
            $result = $link->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $name2 = $row['name'];
            }
            if (count($a) == 3) {
                $sql = "SELECT name FROM `ps_attribute_group_lang` WHERE id_lang=6 AND id_attribute_group=$a[2]";
                $result = $link->query($sql);
                while ($row = mysqli_fetch_array($result)) {
                    $name3 = $row['name'];
                }
                if ($name3 == '') die("BLAD3");
            }

            if ($name1 == '' or $name2 == '') die("BLAD!");

            $wyn = array();
            $wyn['pg_name'] = $name1 . ' + ' . $name2;
            if ($name3 != '') {
                $wyn['pg_name'] = $name1 . ' + ' . $name2 . ' + ' . $name3;
            }
            $wyn['super'] = 0;
            $wyn['pg_type'] = 1;
            $this->objPropertiesGroups->add($wyn);
            $id = $this->objPropertiesGroups->getLastId();

            $jest = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='$name1'  AND p_id < 30");
            $w = array();
            $w['pg_id'] = $id;
            $w['p_id'] = $jest;
            $w['p_order'] = 0;
            $this->objPropertiesGroupsVal->add($w);

            $jest = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='$name2'  AND p_id < 30");
            $w = array();
            $w['pg_id'] = $id;
            $w['p_id'] = $jest;
            $w['p_order'] = 1;
            $this->objPropertiesGroupsVal->add($w);

            if ($name3 != '') {
                $jest = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='$name3' AND p_id < 30 ");
                $w = array();
                $w['pg_id'] = $id;
                $w['p_id'] = $jest;
                $w['p_order'] = 2;
                $this->objPropertiesGroupsVal->add($w);
            }

            $data = $db->fetchAll("SELECT p_id FROM properties WHERE p_id > 29");
            foreach ($data as $k => $v) {
                $w = array();
                $w['pg_id'] = $id;
                $w['p_id'] = $v['p_id'];
                $w['p_order'] = 3;
                $this->objPropertiesGroupsVal->add($w);
            }
        }

        die("OK!");
    }

    public function importPropertiesValuesAction()
    {
        ini_set('display_errors', 1);
        ini_set('memory_limit', '1024M');
        $lang = 1;

        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();

        try {
            $sql = "SELECT f.id_feature,name FROM `ps_feature` f INNER JOIN ps_feature_lang l ON (f.id_feature=l.id_feature) ORDER BY f.id_feature ASC";
            $result = $link->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $id_feature = $row['id_feature'];

                $abc = $link->query("SELECT COUNT(l.value) as ile FROM ps_feature_value f INNER JOIN ps_feature_value_lang l ON 
                        (f.id_feature_value=l.id_feature_value) WHERE f.id_feature=$id_feature");
                while ($row5 = mysqli_fetch_array($abc)) {
                    $jest = $row5['ile'];
                }
                if ($jest > 50) {
                    $ind = 2;
                } else {
                    $ind = 3;
                }

                $wyn = array();
                $wyn['p_id'] = $id_feature + 29;
                $wyn['p_name'] = $row['name'];
                $wyn['p_iname'] = $row['name'];
                $wyn['p_model'] = 2;
                $wyn['p_type'] = $ind;
                $wyn['p_hide'] = 0;
                $wyn['p_filter'] = 0;
                $this->objProperties->add($wyn);
                $id = $this->objProperties->getLastId();

                if ($ind != 2) {

                    $sql2 = "SELECT DISTINCT value FROM ps_feature_value f INNER JOIN ps_feature_value_lang l ON (f.id_feature_value=l.id_feature_value) "
                        . "WHERE id_feature=$id_feature";
                    $result2 = $link->query($sql2);
                    while ($row2 = mysqli_fetch_array($result2)) {
                        $row2['value'] = stripslashes($row2['value']);

                        $jest = $db->fetchOne("SELECT pv_id FROM `properties_values` WHERE p_id='$id' AND pv_value1='" . addslashes($row2['value']) . "'");
                        if ($jest < 1) {
                            $this->objPropertiesValues->add($id, $row2['value']);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            pre($e->getMessage());
        }

        die("ok");
    }

    public function itemsAction()
    {
        ini_set('display_errors', 1);
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        try {

            $link = $this->connect();

            $db = Zend_Registry::get('dbAdapter');


            $path = '/home/centr11/domains/test.com.pl/public_html/images/items/';
            $type = array('_big', '_top', '_medium', '_small', '_micro', '_prop');


            $sql = "SELECT id_manufacturer,name FROM `ps_manufacturer` WHERE active=1 ORDER BY id_manufacturer ASC";
            $result1 = $link->query($sql);
            $i = 0;
            while ($row1 = mysqli_fetch_array($result1)) {
                if (strlen($row1['name']) > 1) {
                    $prod = trim($row1['name']);
                    $producers[$row1['id_manufacturer']] = $prod;
                }
            }

            $lastID = 0;
            $abc = 0;
            $nowe = 0;
            $result1 = $link->query("SELECT p.*,l.name,l.description,l.description_short,l.meta_description,l.meta_keywords,l.meta_title FROM `ps_product` p INNER JOIN ps_product_lang l 
                    ON (p.id_product=l.id_product AND l.id_lang='6') WHERE p.id_product > 0 ORDER BY p.id_product ASC");
            while ($v = mysqli_fetch_array($result1)) {
                try {

                    $item_id = $v['id_product'];
                    // if ($item_id <= 14730) continue;

                    //$one1 = $this->objItems->getOneById($item_id);
                    //if ($one1['item_id'] < 1) continue;

                    $item = array();
                    $item['item_buy_price'] = $v['wholesale_price'];


                    $item['item_id'] = $item_id;
                    $item['item_name'] = $v['name'];
                    $item['item_desc_short'] = $v['description_short'];
                    $item['item_desc'] = $v['description'];
                    switch ($v['id_tax_rules_group']) {
                        case 1:
                            $tax = 23;
                            break;
                        case 2:
                            $tax = 8;
                            break;
                        case 3:
                            $tax = 5;
                            break;
                        case 4:
                            $tax = 0;
                            break;
                    }
                    $item['item_vat'] = $tax;
                    $item['staff_id'] = 1;
                    $item['item_weight'] = $v['weight'];
                    $item['item_active'] = $v['active'];
                    $item['item_shipping_time'] = 1;

                    $item['item_exp_store'] = 0;
                    $item['pg_id'] = 1;
                    if ($v['id_manufacturer'] > 0) {
                        $producent = $producers[$v['id_manufacturer']];
                    }

                    if ($producent != '') {
                        $p = $this->objProducers->getOneByName($producent);
                        if ($p['producer_id'] > 1) {
                            $item['producer_id'] = $p['producer_id'];
                        } else {
                            $item['producer_id'] = 1;
                        }
                    } else {
                        $item['producer_id'] = 1;
                    }


                    $item['item_code'] = (string)$v['reference'];
                    $item['item_ean'] = (string)$v['ean13'];
                    $item['item_new'] = 0;

                    $priceBrutto = $v['price'] * (1 + $item['item_vat'] / 100);
                    if (isset($v['reduction_price']) && ($v['reduction_price'] > 0)) {

                        $price = $priceBrutto - $v['reduction_price'];

                        if ($v['reduction_from'] == $v['reduction_to']) {
                            $item['item_promo_price_from'] = '2017-10-03';
                            $item['item_promo_price_to'] = '2020-12-31';
                        } else {
                            $item['item_promo_price_from'] = $v['reduction_from'];
                            $item['item_promo_price_to'] = $v['reduction_to'];
                        }

                        $item['item_price'] = $v['price'];
                        $item['item_promo'] = 1;
                        $item['item_promo_price'] = $price;
                        $item['item_real_promo'] = 1;
                        $item['item_actual_price'] = $item['item_promo_price'];
                    } else {
                        $item['item_promo'] = 0;
                        $item['item_price'] = $v['price'];
                        $item['item_actual_price'] = $priceBrutto;
                    }

                    // dodanie produktu
                    try {
                        $this->objItems->add($item);
                    } catch (Exception $e) {
                        print_r($e);
                        die('aa');
                    }

                    /*$sql = "SELECT name FROM `ps_category_lang` WHERE id_category = {$v['id_category_default']}";
                    $cat_connections = $link->query($sql);
                    while ($fetched = mysqli_fetch_array($cat_connections)) {
                        $cat_name = $fetched['name'];
                        $row = $db->fetchRow("SELECT id FROM cat WHERE name1 = '" . $cat_name . "'");
                        $insert_conn = "INSERT INTO cat_items (cat_id, item_id) VALUES ({$row['id']},{$v['id_product']})";
                        try {

                            $db->query($insert_conn);
                        } catch (Exception $e) {
                            print_r($e);
                            die();
                        }
                        //print_r($row['id']); print_r($v['id_product']); die('aa');
                    }*/
                    $lastID = $item_id;

                    $this->_setPropMag($item_id);
                    if ($this->pg_id > 0) {
                        //        $item2 = array();
                        //        $item2['item_exp_store'] = 1;
                        //        $item2['pg_id'] = $this->pg_id;
                        //        $this->objItems->save($item_id,$item2);
                    } else {
                        // magazyn - to dla produktow bez cech mag.
                        $qt = "SELECT quantity FROM `ps_stock_available` WHERE id_product = {$item_id}";
                        $quantity = $link->query($qt);
                        while ($fetchedqt = mysqli_fetch_array($quantity)) {
                            $amount = (int)$fetchedqt['quantity'];
                        }
                        $db->query("INSERT INTO items_amount (item_id,depot_id,ia_amount,ia_nolimit) VALUES('$lastID','1','$amount','0')");
                    }
                } catch (Exception $e) {
                    print_r($e->getMessage());
                }


                //  continue;

                $this->_setPropInf($item_id);


                // foto

                //   @mkdir("{$path}{$lastID}");


                $fetched_images = $link->query("SELECT * FROM `ps_image` where id_product='" . $lastID . "' order by position ASC");
                while ($foto = mysqli_fetch_array($fetched_images)) {

                    //    10779-19481.jpg
                    $img = $lastID . '-' . $foto['id_image'] . '.jpg';


                    if ($img != '') {

                        // file_put_contents($path . $lastID . '/' . $foto['id_product'] . '-' . $foto['id_image'] . '_big.jpg', file_get_contents('http://www.centrumnawadniania.com/img/p/' . $img));

                        $base = $foto['id_product'] . '-' . $foto['id_image'] . '.jpg';
                        foreach ($type as $ftype) {
                            $fname = $foto['id_product'] . '-' . $foto['id_image'] . $ftype . '.jpg';
                            if ($foto['cover'] == 1) {
                                $if_root = 1;
                            } else {
                                $if_root = 0;
                            }

                            $db->query("INSERT INTO items_foto (item_id,if_root,if_base_name,if_name,if_type,prop) 
                                                    VALUES('$lastID','$if_root','$base','$fname','$ftype','0');");
                        }
                    }
                }
                // die('photos');
                // end foto
                // continue;

                // kategorie
                $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('1','$lastID')";
                $db->query($sql);

                $i_id = $lastID;
                $old_cats = 'SELECT id_category FROM ps_category_product WHERE id_product = "' . $item_id . '"';
                $res_old_cats = $link->query($old_cats);
                //$sql6 = "SELECT k.nowa FROM `cat` c INNER JOIN kategorie k ON (k.stara=c.id_category) WHERE id_product='$item_id'";
                //$res6 = $link->query($sql6);
                while ($row6 = mysqli_fetch_array($res_old_cats)) {
                    $new_cats = 'SELECT nowa FROM kategorie WHERE stara="' . $row6['id_category'] . '"';
                    $res_new_cats = $db->fetchAll($new_cats);
                    print_r($res_new_cats);
                    foreach ($res_new_cats as $proper_cat) {
                        $c_id = $cat = $proper_cat['nowa'];
                        $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$cat','$lastID')";

                        $db->query($sql);

                        $level = $this->treeCat->getLevel($cat);
                        if ($level == 4) {
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                        }
                        if ($level == 3) {
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                        } elseif ($level == 2) {
                            $parent = $this->treeCat->getParent($c_id);
                            $c_id = $parent['id'];
                            $sql = "INSERT INTO cat_items (cat_id,item_id) VALUES ('$c_id','$i_id')";
                            try {
                                $db->query($sql);
                            } catch (Exception $e) {
                                echo $sql . ' ';
                                print_r($e->getMessage());
                            }
                        }
                    }
                }
                //die('jeden');
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        die('import complete without errors');
    }

    private function _setPropInf($id)
    {
        $ind = array(0);
        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();


        $sql = "SELECT f.id_feature, f.id_feature_value, name, l2.value
FROM `ps_feature_product` f
INNER JOIN ps_feature_lang l ON ( f.id_feature = l.id_feature )
INNER JOIN ps_feature_value v ON ( v.id_feature_value = f.id_feature_value )
INNER JOIN ps_feature_value_lang l2 ON ( l2.id_feature_value = v.id_feature_value )
WHERE `id_product` =$id";
        $result = $link->query($sql);
        while ($row = mysqli_fetch_array($result)) {
            // id_feature name value

            $aa = $db->fetchRow("SELECT p_id,p_type FROM properties WHERE p_iname='" . $row['name'] . "' AND p_id>'29'");
            $pid = $aa['p_id'];
            $type = $aa['p_type'];
            if ($pid > 0) {
                if ($type == 2) {
                    $this->addPropInd($pid, $id, $row['value']);
                } else {
                    $pv_id = $db->fetchOne("SELECT pv_id FROM `properties_values` WHERE p_id='$pid' AND pv_value1='" . addslashes($row['value']) . "'");
                    if ($pv_id > 0) {
                        $this->addProp($pid, $id, $pv_id);
                    }
                }
            }
        }
    }

    private function addProp($id, $item_id, $val)
    {
        try {
            $d = array();
            $d['p_id'] = $id;
            $d['item_id'] = $item_id;
            $d['pv_id'] = $val;
            $this->objPropertiesValMulti->add($d);
        } catch (Exception $e) {
        }
    }

    private function addPropInd($id, $item_id, $val)
    {
        try {
            $d = array();
            $d['p_id'] = $id;
            $d['item_id'] = $item_id;
            $d['pvi_value'] = $val;
            $this->objPropertiesValIndividual->add($d);
        } catch (Exception $e) {
        }
    }

    private function _setPropMag($id)
    {
        $this->pg_id = 0;
        $db = Zend_Registry::get('dbAdapter');
        $link = $this->connect();


        $sql = "SELECT a.*,c.id_attribute,l.name,p2.id_attribute_group FROM `ps_product_attribute` a INNER JOIN ps_product_attribute_combination c 
ON (c.id_product_attribute=a.id_product_attribute) INNER JOIN ps_attribute_lang l ON (l.id_attribute=c.id_attribute AND l.id_lang=3) 
INNER JOIN ps_attribute p2 ON (p2.id_attribute = c.id_attribute) WHERE a.id_product=$id ORDER BY a.id_product_attribute ASC, p2.id_attribute_group ASC ";
        $result = $link->query($sql);
        $i = 0;
        $opt = array();
        while ($row = mysqli_fetch_array($result)) {
            $opt[] = $row;
        }
        if (count($opt) < 1) {
            return 1; // brak cech mag.
        }
        if ($opt[0]['id_product_attribute'] == $opt[1]['id_product_attribute'] && $opt[1]['id_product_attribute'] == $opt[2]['id_product_attribute']) {
            //2 cechy

            $name = [];
            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE   l.id_lang = 3 AND p.id_attribute =" . $opt[0]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $name[$row2['name']] = $row2['id_attribute_group'];
            }

            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE   l.id_lang = 3 AND p.id_attribute =" . $opt[1]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $name[$row2['name']] = $row2['id_attribute_group'];
            }

            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE   l.id_lang = 3 AND p.id_attribute =" . $opt[2]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $name[$row2['name']] = $row2['id_attribute_group'];
            }

            asort($name);
            $names = array_keys($name);
            $group = substr(implode(" + ", $names), 0, 32);

            $pg_id = $db->fetchOne("SELECT pg_id FROM `properties_groups` WHERE pg_name='" . $group . "'");

            if ($pg_id > 0) {
                $this->pg_id = $pg_id;

                $pid = [];
                $pid[] = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $names[0] . "'  AND p_id < 30");
                $pid[] = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $names[1] . "'  AND p_id < 30");
                $pid[] = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $names[2] . "'  AND p_id < 30");


                if (count($pid) == 2 && $pid[0] != '' && $pid[1] != '' && $pid[2] != '') {
                    $i = 0;
                    $grupuj = [];
                    foreach ($opt as $k => $v) {
                        $grupuj[$v['id_product_attribute']][] = $v;
                    }

                    foreach ($grupuj as $id_product_attribute => $z) {
                        $c = 0;
                        $pv_id = [];
                        foreach ($z as $k2 => $v) {
                            $pv_id[] = $db->fetchOne("SELECT pv_id FROM `properties_values` WHERE p_id='$pid[$c]' 
                                                    AND pv_value1='" . addslashes($v['name']) . "'");
                            $c++;
                        }

                        $es = array();
                        $es['item_id'] = $id;
                        $es['pv_id_1'] = $pv_id[0];
                        $es['pv_id_2'] = $pv_id[1];
                        $es['pv_id_3'] = $pv_id[2];
                        $es['item_code'] = (string)$v['reference'];
                        $es['item_price'] = 0;
                        $es['item_weight'] = $v['weight'];
                        //  $this->objItemsES->add($es);
                        $aa = $this->objItemsES->getOneByAllId($id, $pv_id[0], $pv_id[1], $pv_id[2]);
                        if ($aa['es_id'] > 0) {
                            $amount = array();
                            $amount['item_id'] = $id;
                            $amount['depot_id'] = 1;
                            $amount['mag_id'] = $aa['es_id'];
                            $amount['ia_amount'] = (int)$v['quantity'];
                            $amount['ia_nolimit'] = 0;
                            $this->objAmount->add($amount);
                        }
                    }
                }
            }
        } elseif ($opt[0]['id_product_attribute'] == $opt[1]['id_product_attribute']) {
            //2 cechy

            $name = [];
            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE   l.id_lang = 3 AND p.id_attribute =" . $opt[0]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $name[$row2['name']] = $row2['id_attribute_group'];
            }

            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE   l.id_lang = 3 AND p.id_attribute =" . $opt[1]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $name[$row2['name']] = $row2['id_attribute_group'];
            }

            asort($name);
            $names = array_keys($name);
            $group = substr(implode(" + ", $names), 0, 32);

            $pg_id = $db->fetchOne("SELECT pg_id FROM `properties_groups` WHERE pg_name='" . $group . "'");

            if ($pg_id > 0) {
                $this->pg_id = $pg_id;

                $pid = [];
                $pid[] = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $names[0] . "'  AND p_id < 30");
                $pid[] = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $names[1] . "'  AND p_id < 30");


                if (count($pid) == 2 && $pid[0] != '' && $pid[1] != '') {
                    $i = 0;
                    $grupuj = [];
                    foreach ($opt as $k => $v) {
                        $grupuj[$v['id_product_attribute']][] = $v;
                    }


                    foreach ($grupuj as $id_product_attribute => $z) {
                        $c = 0;
                        $pv_id = [];
                        foreach ($z as $k2 => $v) {
                            $pv_id[] = $db->fetchOne("SELECT pv_id FROM `properties_values` WHERE p_id='$pid[$c]' 
                                                    AND pv_value1='" . addslashes($v['name']) . "'");
                            $c++;
                        }

                        $es = array();
                        $es['item_id'] = $id;
                        $es['pv_id_1'] = $pv_id[0];
                        $es['pv_id_2'] = $pv_id[1];
                        $es['pv_id_3'] = 0;
                        $es['item_code'] = (string)$v['reference'];
                        $es['item_price'] = 0;
                        $es['item_weight'] = $v['weight'];
                        //     $this->objItemsES->add($es);
                        $aa = $this->objItemsES->getOneByAllId($id, $pv_id[0], $pv_id[1], 0);
                        if ($aa['es_id'] > 0) {
                            $amount = array();
                            $amount['item_id'] = $id;
                            $amount['depot_id'] = 1;
                            $amount['mag_id'] = $aa['es_id'];
                            $amount['ia_amount'] = (int)$v['quantity'];
                            $amount['ia_nolimit'] = 0;
                            $this->objAmount->add($amount);
                        }
                    }
                }
            }
        } else {
            // 1 cecha
            $sql = "SELECT name, p.id_attribute_group
                FROM `ps_attribute_group` a
                INNER JOIN ps_attribute_group_lang l ON ( l.id_attribute_group = a.id_attribute_group )
                INNER JOIN ps_attribute p ON ( p.id_attribute_group = a.id_attribute_group )
                WHERE  l.id_lang = 3 AND p.id_attribute =" . $opt[0]['id_attribute'];
            $result2 = $link->query($sql);
            while ($row2 = mysqli_fetch_array($result2)) {
                $this->pg_id = $row2['id_attribute_group'] + 1;
                $name = $row2['name'];
            }

            $pid = $db->fetchOne("SELECT p_id FROM properties WHERE p_iname='" . $name . "' AND p_id < 30");
            if ($pid > 0) {
                foreach ($opt as $k => $v) {

                    $pv_id = $db->fetchOne("SELECT pv_id FROM `properties_values` WHERE p_id='$pid' AND pv_value1='" . addslashes($v['name']) . "'");

                    $es = array();
                    $es['item_id'] = $id;
                    $es['pv_id_1'] = $pv_id;
                    $es['pv_id_2'] = 0;
                    $es['pv_id_3'] = 0;
                    $es['item_code'] = (string)$v['reference'];
                    $es['item_price'] = 0;
                    $es['item_weight'] = $v['weight'];
                    //    $this->objItemsES->add($es);
                    $aa = $this->objItemsES->getOneByAllId($id, $pv_id[0], 0, 0);

                    //    $es_id =  $db->fetchOne("SELECT es_id FROM `itemses` WHERE item_id='$id' AND pv_id_1='".$pv_id."'");
                    //    if($es_id<1) continue;
                    $atr = $v['id_product_attribute'];
                    if ($aa['es_id'] > 0) {
                        $amount = array();
                        $amount['item_id'] = $id;
                        $amount['depot_id'] = 1;
                        $amount['mag_id'] = $aa['es_id'];
                        $amount['ia_amount'] = (int)$v['quantity'];
                        $amount['ia_nolimit'] = 0;
                        $this->objAmount->add($amount);
                    }
                }
            }
        }
    }

    public function usersAction()
    {
        $i = 0;
        ini_set('display_errors', 1);
        // sprawdz czy klient o takim mailu istnieje
        // jesli nie to dodaj
        // potem dodaj zamowienie
        // potem produkty z zamowienia

        $link = $this->connect();

        try {
            $result1 = $link->query("SELECT DISTINCT email,c.id_customer,c.firstname,c.lastname,a.address1,a.city,a.postcode,c.newsletter,
                    a.phone,a.company,a.phone_mobile
                    FROM `ps_customer` c 
                    LEFT JOIN ps_address a ON (a.id_customer=c.id_customer)
                    GROUP BY email  ORDER BY c.id_customer ASC  ");

            while ($v = mysqli_fetch_array($result1)) {
                $email = $v['email'];


                if ($this->objUsers->isExist($email)) {
                    $user = $this->objUsers->getOneByEmail($email);
                    $u_id = $user['user_id'];
                } else {

                    $user = array();
                    $user['user_id'] = $v['id_customer'];
                    $user['user_type'] = strlen($v['company']) > 3 ? 2 : 1;
                    $user['user_password'] = rand(1000, 10000);
                    if ($user['user_type'] == 2) {
                        $user['user_firm'] = $v['company'];
                    } else {
                        $user['user_firm'] = '';
                    }
                    $user['user_name'] = $v['firstname'];
                    $user['user_surname'] = $v['lastname'];
                    $phone = $v['phone'];
                    if ($phone == '') $phone = $v['phone_mobile'];
                    $user['user_phone'] = str_replace(' ', '', str_replace('-', '', trim($phone)));
                    $user['user_email'] = $email;
                    $user['user_street'] = $v['address1'];
                    $user['user_postcode'] = $v['postcode'];
                    $user['user_city'] = $v['city'];
                    $user['user_country'] = 'pl';
                    $user['user_nip'] = '';
                    $user['user_vat'] = $user['user_type'] == 2 ? 1 : 0;
                    $user['user_newsletter'] = $v['newsletter'];
                    $user['user_activate'] = 1;
                    $user['user_active'] = 1;
                    try {
                        $this->objUsers->add($user);
                    } catch (Exception $e) {
                        print_r($e->getMessage());
                    }
                    $u_id = $this->objUsers->getLastId();
                }
            }

            die('import complete without errors');
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function ordersAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $lang = 1;
        ini_set('display_errors', 1);
        $link = $this->connect();


        $db = Zend_Registry::get('dbAdapter');

        $statusy = array();
        $statusy[10] = 100;
        $statusy[26] = 1;
        $statusy[17] = 1;
        $statusy[5] = 1;
        $statusy[24] = 1;
        $statusy[11] = 2;
        $statusy[21] = 2;
        $statusy[32] = 2;
        $statusy[28] = 2;
        $statusy[14] = 1;
        $statusy[4] = 1;
        $statusy[7] = 1;
        $statusy[30] = 1;
        $statusy[1] = 1;
        $statusy[2] = 1;
        $statusy[15] = 90;
        $statusy[29] = 1;
        $statusy[33] = 1;
        $statusy[13] = 1;
        $statusy[18] = 9;
        $statusy[8] = 1;
        $statusy[25] = 1;
        $statusy[19] = 1;
        $statusy[20] = 1;
        $statusy[31] = 1;
        $statusy[6] = 1;
        $statusy[3] = 9;
        $statusy[22] = 5;
        $statusy[16] = 9;
        $statusy[9] = 9;
        $statusy[27] = 9;
        $statusy[23] = 2;
        $statusy[12] = 2;

        try {

            $sql = "SELECT * FROM ps_orders ORDER  BY id_order ASC";
            $result1 = $link->query($sql);
            $i = 0;
            $elo = 0;
            while ($row = mysqli_fetch_array($result1)) {
                $u_id = $row['id_customer'];

                $sql1 = "SELECT id_order_state  FROM `ps_order_history` WHERE `id_order` = '" . $row['id_order'] . "' order by id_order_history DESC LIMIT 1";
                $res2 = $link->query($sql1);
                while ($row2 = mysqli_fetch_array($res2)) {
                    $state = $row2['id_order_state'];
                }

                $ss = $statusy[$state];
                if ($ss < 1) $ss = 100;

                // zamowienie
                $order = array();
                $order['order_id'] = $row['id_order'];
                $order['order_status'] = $ss;
                try {
                    $user = $this->objUsers->getOneById($u_id);
                } catch (Exception $e) {
                    print_r($e);
                }
                if ($user['user_id'] > 0) {
                    $order['user_id'] = $u_id;
                } else {

                    $sql4 = "SELECT * FROM ps_customer WHERE id_customer='$u_id'";
                    $result4 = $link->query($sql4);
                    while ($row4 = mysqli_fetch_array($result4)) {

                        $email = $row4['email'];
                    }

                    $user = $this->objUsers->getOneByEmail($email);
                    if (!$user) {
                        $user = array();
                        $user['user_type'] = 1;
                        $user['user_email'] = $email;
                        $user['user_name'] = $row4['firstname'];
                        $user['user_surname'] = $row4['lastname'];
                    }
                    if ($user['user_email'] == '') {
                        $user['user_email'] = ' ';
                    }
                }

                $order['order_date'] = $row['date_add'];
                $order['order_email'] = $user['user_email'];
                $order['order_payer'] = $user['user_type'];
                $order['order_new'] = 0;

                if ($user['user_type'] == 2) {
                    $who = $user['user_firm'];
                } else {
                    $who = $user['user_name'] . ' ' . $user['user_surname'];
                }
                $order['order_pay_to'] = $who . "\n" . $user['user_street'] . '||' . $user['user_home'] . '||' . $user['user_flat'] . '||' . $user['user_postcode'] . '||' . $user['user_city'] . '||Polska';
                if ($user['user_nip'] != '')
                    $order['order_pay_to'] .= "\n" . $user['user_nip'];
                else
                    $order['order_pay_to'] .= "\n";

                if ($user['user_phone'] != '') {
                    $order['order_pay_to'] .= "\n" . $user['user_phone'];
                } else {
                    $order['order_pay_to'] .= "\n";
                }

                $who = $user['user_name'] . ' ' . $user['user_surname'];
                $order['order_send_to'] = $who . "\n" . $user['user_street'] . '||' . $user['user_home'] . '||' . $user['user_flat'] . '||' . $user['user_postcode'] . '||' . $user['user_city'] . '||Polska';
                if ($user['user_phone'] != '') {
                    $order['order_send_to'] .= "\n\n" . $user['user_phone'];
                }

                $order['order_fvat'] = $user['user_type'] == 2 ? 1 : 0;


                $amount = 0;
                $suma = 0;
                $positions = $link->query("SELECT * FROM ps_order_detail WHERE id_order='$row[id_order]'");

                while ($b = mysqli_fetch_array($positions)) {
                    $amount += $b['product_quantity'];

                    $cena = $b['unit_price_tax_incl'];

                    $suma += ($cena * $b['product_quantity']);
                }
                $order['order_amount'] = $amount;
                $order['order_price'] = $suma;
                $order['order_delivery_country_code'] = 'pl';
                $order['order_supplier_name'] = 'kurier';
                $order['order_payment_name'] = $row['payment'];
                $order['order_payment_type'] = $row['module'] == 'cashondelivery' ? 'cod' : 'transfer';
                $order['order_delivery_price'] = $row['total_shipping'];
                $order['order_topay'] = $row['total_paid'];

                $order['order_currency'] = 'PLN';
                $order['order_exchange'] = 1;
                $order['order_lang'] = 'pl';
                $order['order_source'] = 'prestashop';
                $this->objOrders->add($order);
                $order_id = $this->objOrders->getLastId();

                $positions = $link->query("SELECT * FROM ps_order_detail WHERE id_order='$row[id_order]'");
                while ($b = mysqli_fetch_array($positions)) {

                    //  $prod = $link->query("SELECT nowe FROM prod WHERE stare='$b[product_id]'");
                    //   $iii = mysql_fetch_row($prod);

                    $vat = 0.23;
                    $cena = $b['unit_price_tax_incl'];
                    $cena2 = $b['product_quantity_discount'];


                    $oi = array();
                    $item = $this->objItems->getOneById($b[product_id]);
                    if ($b['product_reference'] == '') $b['product_reference'] = ' ';
                    if ($item['item_id'] > 0) {
                        $oi['oi_item_id'] = $b[product_id];
                        $oi['oi_item_code'] = $b['product_reference'];
                        $oi['oi_item_name'] = $b['product_name'];
                    } else {
                        $oi['oi_item_id'] = NULL;
                        $oi['oi_item_code'] = $b['product_reference'];
                        $oi['oi_item_name'] = $b['product_name'];
                    }
                    $oi['order_id'] = $order_id;
                    $oi['oi_type'] = 1;
                    $oi['oi_mag'] = 0;
                    $oi['oi_mag_id'] = NULL;
                    $oi['oi_item_desc'] = '';

                    $oi['oi_item_price'] = $cena;
                    $oi['oi_item_vat'] = 23;

                    if ($cena2 > 0) {
                        $oi['oi_promo'] = 1;
                        $oi['oi_promo_price'] = $cena2;
                    }

                    $oi['oi_amount'] = $b['product_quantity'];
                    $this->objOrdersItems->add($oi);
                }

                // Zapisujemy informacje dot. transportu jako pozycje w orders_items
                // start : saveTG
                $d = '';
                $d['order_id'] = $order_id;
                $d['oi_type'] = 2; // - transport
                $d['oi_item_id'] = null;
                $d['oi_mag'] = null;
                $d['oi_mag_id'] = null;
                $d['oi_item_name'] = "Transport";
                $d['oi_item_desc'] = null;
                $d['oi_item_code'] = "";
                $d['oi_item_price'] = $order['order_delivery_price'];
                $d['oi_item_vat'] = 23;
                $d['oi_amount'] = 1;

                $this->objOrdersItems->saveTG($d);
                // end : saveTG

            }

            die('import complete without errors');
        } catch (Exception $e) {
            print_r($e->getMessage());
        }

        die('import WITH errors');
    }

    public function blogcatAction()
    {
        ini_set('display_errors', 1);
        $link = $this->connect();
        $db = Zend_Registry::get('dbAdapter');
        try {
            $result1 = $link->query("SELECT b.id_smart_blog_category as id, bl.meta_title as title, bl.link_rewrite as url,
                                      b.id_parent as parent FROM `ps_smart_blog_category` b 
                    LEFT JOIN ps_smart_blog_category_lang bl ON (b.id_smart_blog_category=bl.id_smart_blog_category)");

            while ($blog = mysqli_fetch_array($result1)) {
                $id = ++$blog['id'];
                $title = $blog['title'];
                $url = $blog['url'];
                $parent = ++$blog['parent'];

                try {
                    $db->query('INSERT INTO news_cat (id, name1, name2, name3, name4, name5, cat_url1,parent,root) VALUES (' . $id . ',"' . $title . '","","","","","' . $url . '",' . $parent . ',0)');
                } catch (Exception $e) {
                    print_r($e);
                }
            }
            die('import complete without errors');
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function blogpostsAction()
    {
        ini_set('display_errors', 1);
        $link = $this->connect();
        $db = Zend_Registry::get('dbAdapter');
        try {
            $result1 = $link->query("SELECT b.id_smart_blog_post as id, b.id_category as cat,
                                            bl.meta_title as title, bl.link_rewrite as url,
                                            bl.meta_description as des, bl.meta_keyword as keyw,
                                          bl.content as post FROM `ps_smart_blog_post` b 
                    LEFT JOIN ps_smart_blog_post_lang bl ON (b.id_smart_blog_post=bl.id_smart_blog_post) WHERE b.active = 1");

            while ($blog = mysqli_fetch_array($result1)) {
                $id = $blog['id'];
                $cat = ++$blog['cat'];
                $title = $blog['title'];
                $url = strtolower(removePolishChars($blog['url']));
                $post = addslashes($blog['post']);
                $desc = $blog['des'];
                $keyw = $blog['keyw'];
                //print_r($id); die('dd')

                if ($id == 58) {

                    try {


                        $db->query('INSERT INTO news (news_id, news_author,
                  news_title1, news_title2, news_title3, news_title4, news_title5, news_url1,
                  news_content5, news_content4, news_content3, news_content2, news_content1,
                  google_title1, google_title2, google_title3, google_title4, google_title5,
                  google_desc1, google_desc2, google_desc3, google_desc4, google_desc5,
                  google_key_words1, google_key_words2, google_key_words3, google_key_words4, google_key_words5)
          VALUES (' . $id . ',"admin",
                  "' . $title . '","","","","","' . $url . '",
                  "", "", "", "", "' . $post . '",
                  "' . $title . '", "", "", "", "",
                  "' . $desc . '", "", "", "", "",
                  "' . $keyw . '", "", "", "", "")');

                        //die('asd');
                    } catch (Exception $e) {
                        print_r($e);
                        die('aa');
                    }
                    try {
                        $db->query('INSERT INTO news_items (cat_id, news_id, hi_order) VALUES (' . $cat . ',' . $id . ', 0)');
                    } catch (Exception $e) {
                        print_r($e);
                    }

                    try {
                        $path = '/home/centr11/domains/test.com.pl/public_html/images/news/';
                        $type = ['_big', '_top', '_medium', '_small', '_micro', '_prop'];
                        @mkdir("{$path}{$id}");

                        $img = $id . '.jpg';

                        if ($img != '') {

                            file_put_contents($path . $id . '/' . $id . '_big.jpg', file_get_contents('https://www.centrumnawadniania.com/modules//smartblog/images/' . $id . '-single-default.jpg'));
                            foreach ($type as $ftype) {
                                $fname = $id . $ftype . '.jpg';

                                $db->query("INSERT INTO news_foto (news_id,nf_root,nf_base_name,nf_name,nf_type, nf_order) 
                                                    VALUES('$id','1','$img','$fname','$ftype', 0)");
                            }
                        }
                    } catch (Exception $e) {
                        print_r($e);
                        die();
                    }
                }
            }
            die('import complete without errors');
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }
}

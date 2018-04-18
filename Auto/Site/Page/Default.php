<?php
class Auto_Site_Page_Default extends ZOL_Abstract_Page {
    /*
    * php index.php --c=Default --a=Default --id=123
     */
    public function doDefault(ZOL_Request $input) {
        $id = $input->get('id');
        echo "auto run {$id} \n";
        exit;
    }
}

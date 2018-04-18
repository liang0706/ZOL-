<?php
class Site_Page_Default extends ZOL_Abstract_Page {
    public function doDefault(ZOL_Request $input, ZOL_Response $output) {
        $res = Libs_Site::getData();
        $url = Helper_Func::clearPageUrl($input->get());
        $output->setTemplate('Default');
    }
}

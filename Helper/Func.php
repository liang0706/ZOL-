<?php
class Helper_Func {
    /*
    * $params = $input->get()
     */
    public static function clearPageUrl($params = array()) {
        $str = '?';
        $outStr = $_SERVER['SCRIPT_NAME'];
        if (is_array($params) && !empty($params)) {
            foreach ($params as $paramsKey => $paramsVal) {
                if ($paramsVal && $paramsKey != 'page') {
                    $outStr .= $str . $paramsKey . '=' . htmlspecialchars($paramsVal);
                    $str = '&';
                }
            }
        }
        return $outStr;
    }
}
